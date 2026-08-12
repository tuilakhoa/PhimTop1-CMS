<?php 
include __DIR__ . '/header.php'; 

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://phimimg.com/';
if (!empty($movie['category']) && is_array($movie['category'])) {
    $firstCatObj = reset($movie['category']);
    $firstCat = is_array($firstCatObj) ? ($firstCatObj['slug'] ?? '') : (is_string($firstCatObj) ? $firstCatObj : '');
    if ($firstCat) {
        $sugRes = @file_get_contents("https://phimapi.com/v1/api/the-loai/" . urlencode($firstCat) . "?limit=12");
        if ($sugRes) {
            $sugData = json_decode($sugRes, true);
            if (isset($sugData['data']['items'])) {
                $suggestions = $sugData['data']['items'];
                $sugDomain = $sugData['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
            }
        }
    }
}
?>
<div class="container mx-auto px-4 py-8 max-w-6xl">
    
    <!-- Video Player -->
    <div class="mb-6 bg-black rounded-2xl overflow-hidden shadow-2xl border border-gray-800">
        <div class="aspect-video w-full bg-black relative flex items-center justify-center group" id="player-container">
            <?php if ($isM3U8): ?>
                <video id="video-player" class="w-full h-full outline-none" controls playsinline>
                    <source src="<?= htmlspecialchars($videoUrl) ?>" type="application/x-mpegURL">
                </video>
                <!-- Thư viện Hls.js để phát m3u8 -->
                <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
                <script>
                    var video = document.getElementById('video-player');
                    var videoSrc = "<?= addslashes($videoUrl) ?>";
                    
                    if (Hls.isSupported()) {
                        var hls = new Hls();
                        hls.loadSource(videoSrc);
                        hls.attachMedia(video);
                        hls.on(Hls.Events.MANIFEST_PARSED, function() {
                            video.play().catch(function(e) {
                                console.log("Auto-play blocked by browser.");
                            });
                        });
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        // For Safari
                        video.src = videoSrc;
                        video.addEventListener('loadedmetadata', function() {
                            video.play().catch(function(e) {
                                console.log("Auto-play blocked by browser.");
                            });
                        });
                    }
                </script>
            <?php elseif (!empty($currentEp['link_embed'])): ?>
                <!-- Phát qua Iframe (Embed) -->
                <iframe src="<?= htmlspecialchars($currentEp['link_embed']) ?>" 
                        class="absolute inset-0 w-full h-full" 
                        allowfullscreen 
                        frameborder="0">
                </iframe>
            <?php else: ?>
                <div class="text-white text-center p-8">
                    <i data-lucide="alert-triangle" class="w-12 h-12 text-yellow-500 mx-auto mb-4"></i>
                    <p>Không tìm thấy nguồn phát phù hợp cho tập phim này.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="p-5 bg-gray-900 border-t border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white mb-1"><a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="hover:text-red-500 transition-colors"><?= htmlspecialchars($movie['name']) ?></a></h1>
                <h2 class="text-lg text-gray-300">Đang xem: Tập <?= htmlspecialchars($currentEp['name']) ?></h2>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="toggleWatchPartyDialog()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium border border-indigo-500 flex items-center transition-colors">
                    <i data-lucide="users" class="w-4 h-4 mr-1"></i> Xem Chung
                </button>
                <span class="px-3 py-1.5 bg-gray-800 text-gray-300 rounded-lg text-sm font-medium border border-gray-700 hidden sm:inline-block">
                    <i data-lucide="server" class="w-4 h-4 inline mr-1 text-blue-400"></i> Server: <?= htmlspecialchars($episodes[0]['server_name'] ?? 'HLS/Embed') ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Episode List -->
    <?php if (!empty($episodes[0]['server_data'])): ?>
        <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i data-lucide="list-video" class="w-5 h-5 mr-2 text-red-500"></i> Danh sách tập
                </h3>
            </div>
            
            <div class="flex flex-wrap gap-2 max-h-[300px] overflow-y-auto custom-scrollbar p-2">
                <?php foreach ($episodes[0]['server_data'] as $e): 
                    $isActive = $currentEp['slug'] === $e['slug'];
                    $classes = $isActive 
                        ? "bg-red-600 text-white shadow-lg shadow-red-600/30 font-bold transform scale-105 ring-2 ring-red-400 ring-offset-2 ring-offset-gray-900" 
                        : "bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white border border-gray-700";
                ?>
                    <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($e['slug']) ?>" 
                       class="px-5 py-2.5 rounded-lg transition-all <?= $classes ?>">
                        <?= htmlspecialchars($e['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Movie Suggestions -->
    <?php if (!empty($suggestions)): ?>
    <div class="mt-12 mb-12">
        <h3 class="text-xl font-bold text-white mb-6 border-l-4 border-red-500 pl-3">Có Thể Bạn Sẽ Thích</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($suggestions as $item): ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-800 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-500/20">
                    <div class="relative aspect-[2/3] w-full overflow-hidden">
                        <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute top-2 left-2">
                            <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($item['quality'] ?? 'HD') ?></span>
                        </div>
                    </div>
                    <div class="p-3 relative z-10 flex flex-col flex-grow">
                        <h3 class="text-sm font-semibold text-white line-clamp-1 mb-1 group-hover:text-red-400 transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- History Logging Script -->
<?php if (isset($_SESSION['user'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const appApiKey = '<?= addslashes($settings['appApiKey'] ?? '') ?>';
    const movieSlug = '<?= addslashes($movie['slug']) ?>';
    const movieName = '<?= addslashes($movie['name']) ?>';
    const episodeName = '<?= addslashes($currentEp['name']) ?>';
    const thumbUrl = '<?= addslashes($movie['thumb_url'] ?? '') ?>';

    fetch(`/api/v1/history.php?action=add&key=${appApiKey}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            movie_slug: movieSlug,
            movie_name: movieName,
            episode_name: episodeName,
            thumb_url: thumbUrl
        })
    }).then(res => res.json())
      .then(data => console.log('History logged:', data))
      .catch(err => console.error('Error logging history:', err));
});
</script>
<?php endif; ?>

<!-- Realtime Watching Session Heartbeat -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let deviceId = localStorage.getItem('phimtop1_device_id');
    if (!deviceId) {
        deviceId = 'web-' + Math.random().toString(36).substr(2, 9) + '-' + Date.now();
        localStorage.setItem('phimtop1_device_id', deviceId);
    }
    
    const isLogged = <?= isset($_SESSION['user']) ? 1 : 0 ?>;
    const userName = '<?= addslashes($_SESSION['user']['name'] ?? 'Guest') ?>';
    const movieSlug = '<?= addslashes($movie['slug']) ?>';
    const movieName = '<?= addslashes($movie['name']) ?>';
    const episodeName = '<?= addslashes($currentEp['name']) ?>';

    function sendHeartbeat() {
        let progress = 0;
        const video = document.getElementById('video-player');
        if (video) progress = Math.floor(video.currentTime);

        fetch('/api/v1/watching_session.php?action=heartbeat', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                device_id: deviceId,
                device_name: navigator.userAgent.substring(0, 40) + '...',
                platform: 'web',
                movie_slug: movieSlug,
                movie_name: movieName,
                episode_name: episodeName,
                user_name: userName,
                is_logged_in: isLogged,
                progress: progress
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.command) {
                executeCommand(data.command);
            }
        })
        .catch(err => console.error(err));
    }

    function executeCommand(cmd) {
        const video = document.getElementById('video-player');
        if (!video) return; // Cannot control iframe easily
        if (cmd === 'play') video.play();
        else if (cmd === 'pause' || cmd === 'stop') video.pause();
    }

    sendHeartbeat();
    setInterval(sendHeartbeat, 10000);
});
</script>

<!-- Watch Party Dialog -->
<div id="watch-party-dialog" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
        <button onclick="toggleWatchPartyDialog()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="users" class="w-6 h-6 mr-2 text-indigo-400"></i> Phòng Xem Chung
        </h3>
        
        <div id="wp-setup-view">
            <div class="space-y-4">
                <div>
                    <button onclick="createWatchParty()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-medium transition-colors flex items-center justify-center">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> Tạo phòng mới
                    </button>
                    <label class="flex items-center justify-center text-sm text-gray-400 mt-3 cursor-pointer group">
                        <input type="checkbox" id="wp-is-public" class="mr-2 rounded border-gray-700 bg-gray-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-gray-900 transition-colors w-4 h-4"> 
                        <span class="group-hover:text-gray-300 transition-colors">Công khai phòng này để mọi người cùng xem</span>
                    </label>
                </div>
                
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-gray-700"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-500 text-sm">Hoặc</span>
                    <div class="flex-grow border-t border-gray-700"></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Nhập mã phòng</label>
                    <div class="flex space-x-2">
                        <input type="text" id="wp-room-input" placeholder="Ví dụ: A1B2C3" class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-indigo-500 uppercase">
                        <button onclick="joinWatchPartyBtn()" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl font-medium transition-colors">
                            Vào
                        </button>
                    </div>
                </div>
                
                <div id="wp-public-rooms-container" class="hidden mt-4 pt-4 border-t border-gray-800">
                    <h4 class="text-sm font-medium text-gray-300 mb-3 flex items-center">
                        <i data-lucide="globe" class="w-4 h-4 mr-1.5 text-blue-400"></i> Các phòng đang mở
                    </h4>
                    <div id="wp-public-rooms-list" class="space-y-2 max-h-[150px] overflow-y-auto custom-scrollbar">
                        <!-- Public rooms rendered here -->
                    </div>
                </div>
            </div>
        </div>
        
        <div id="wp-active-view" class="hidden text-center">
            <div class="mb-4">
                <span class="text-gray-400 text-sm block mb-1">Mã phòng của bạn:</span>
                <div class="text-3xl font-mono font-bold text-indigo-400 tracking-wider mb-2" id="wp-room-code-display"></div>
                <p class="text-xs text-gray-500">Gửi mã này hoặc link trang web cho bạn bè để cùng xem.</p>
            </div>
            
            <div class="bg-gray-800/50 rounded-xl p-3 mb-4 text-left border border-gray-700">
                <div class="text-sm text-gray-300 flex items-center justify-between mb-1">
                    <span>Trạng thái:</span>
                    <span id="wp-status-text" class="text-green-400 font-medium flex items-center"><span class="w-2 h-2 rounded-full bg-green-400 mr-1.5 animate-pulse"></span> Đã kết nối</span>
                </div>
                <div class="text-sm text-gray-300 flex items-center justify-between">
                    <span>Vai trò:</span>
                    <span id="wp-role-text" class="text-white font-medium"></span>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button onclick="copyWatchPartyLink()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-xl font-medium transition-colors flex items-center justify-center text-sm">
                    <i data-lucide="copy" class="w-4 h-4 mr-1.5"></i> Copy Link
                </button>
                <button onclick="leaveWatchParty()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-xl font-medium transition-colors flex items-center justify-center text-sm">
                    <i data-lucide="log-out" class="w-4 h-4 mr-1.5"></i> Rời Phòng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let wpRoomCode = null;
let wpIsHost = false;
let wpSyncInterval = null;
let wpVideo = document.getElementById('video-player');

// Check URL for party code
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const partyCode = urlParams.get('party');
    if (partyCode) {
        document.getElementById('wp-room-input').value = partyCode;
        joinWatchParty(partyCode);
    }
});

function toggleWatchPartyDialog() {
    const dialog = document.getElementById('watch-party-dialog');
    dialog.classList.toggle('hidden');
    if (!dialog.classList.contains('hidden')) {
        lucide.createIcons();
        if (!wpRoomCode) {
            fetchPublicRooms();
        }
    }
}

function fetchPublicRooms() {
    const movieSlug = '<?= addslashes($movie['slug']) ?>';
    fetch('/api/v1/watch_party.php?action=list_public&movie_slug=' + movieSlug)
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('wp-public-rooms-container');
        const list = document.getElementById('wp-public-rooms-list');
        if (data.status === 'success' && data.data.length > 0) {
            let html = '';
            data.data.forEach(room => {
                html += `
                <div class="flex items-center justify-between bg-gray-800 rounded-lg p-3 border border-gray-700">
                    <div>
                        <div class="text-indigo-400 font-mono font-bold text-sm">${room.room_code}</div>
                        <div class="text-xs text-gray-400">Host: ${room.creator_name} - Tập ${room.episode_name}</div>
                    </div>
                    <button onclick="joinWatchParty('${room.room_code}')" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-medium rounded-lg transition-colors">
                        Tham gia
                    </button>
                </div>`;
            });
            list.innerHTML = html;
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            list.innerHTML = '';
        }
    })
    .catch(err => console.error('Error fetching public rooms:', err));
}

function showWpActiveView(code, isHost) {
    document.getElementById('wp-setup-view').classList.add('hidden');
    document.getElementById('wp-active-view').classList.remove('hidden');
    document.getElementById('wp-room-code-display').innerText = code;
    document.getElementById('wp-role-text').innerText = isHost ? 'Chủ phòng' : 'Người xem';
    wpRoomCode = code;
    wpIsHost = isHost;
    
    // Create subtle badge on player
    let badge = document.getElementById('wp-player-badge');
    if (!badge) {
        badge = document.createElement('div');
        badge.id = 'wp-player-badge';
        badge.className = 'absolute top-4 left-4 z-20 bg-black/60 backdrop-blur border border-indigo-500/30 text-indigo-100 px-3 py-1.5 rounded-full text-xs font-medium flex items-center cursor-pointer hover:bg-black/80 transition-colors';
        badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></span> Watch Party: ' + code;
        badge.onclick = toggleWatchPartyDialog;
        document.getElementById('player-container').appendChild(badge);
    }
    
    startWpSync();
}

function showWpSetupView() {
    document.getElementById('wp-setup-view').classList.remove('hidden');
    document.getElementById('wp-active-view').classList.add('hidden');
    let badge = document.getElementById('wp-player-badge');
    if (badge) badge.remove();
}

function createWatchParty() {
    const movieSlug = '<?= addslashes($movie['slug']) ?>';
    const episodeName = '<?= addslashes($currentEp['name']) ?>';
    const userName = '<?= addslashes($_SESSION['user']['name'] ?? 'Guest') ?>';
    const isPublic = document.getElementById('wp-is-public').checked ? 1 : 0;
    
    fetch('/api/v1/watch_party.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ movie_slug: movieSlug, episode_name: episodeName, creator_name: userName, is_public: isPublic })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showWpActiveView(data.room_code, true);
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
}

function joinWatchPartyBtn() {
    const code = document.getElementById('wp-room-input').value.trim().toUpperCase();
    if (code) joinWatchParty(code);
}

function joinWatchParty(code) {
    const movieSlug = '<?= addslashes($movie['slug']) ?>';
    
    fetch('/api/v1/watch_party.php?action=join&room_code=' + code)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.data.movie_slug !== movieSlug) {
                alert('Phòng này đang xem phim khác!');
                return;
            }
            showWpActiveView(code, false);
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
}

function copyWatchPartyLink() {
    const url = new URL(window.location.href);
    url.searchParams.set('party', wpRoomCode);
    navigator.clipboard.writeText(url.toString()).then(() => {
        alert('Đã copy link phòng xem chung!');
    });
}

function leaveWatchParty() {
    if (wpSyncInterval) clearInterval(wpSyncInterval);
    wpRoomCode = null;
    wpIsHost = false;
    showWpSetupView();
    toggleWatchPartyDialog();
}

let isSyncing = false;
function startWpSync() {
    if (wpSyncInterval) clearInterval(wpSyncInterval);
    if (!wpVideo) return; // Cannot sync if iframe
    
    wpSyncInterval = setInterval(() => {
        if (isSyncing) return;
        isSyncing = true;
        
        if (wpIsHost) {
            // Push state
            fetch('/api/v1/watch_party.php?action=sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    room_code: wpRoomCode,
                    is_playing: !wpVideo.paused ? 1 : 0,
                    current_time: Math.floor(wpVideo.currentTime)
                })
            }).finally(() => { isSyncing = false; });
        } else {
            // Pull state
            fetch('/api/v1/watch_party.php?action=state&room_code=' + wpRoomCode)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const r = data.data;
                    if (r.status !== 'active') {
                        alert('Phòng xem chung đã bị khóa hoặc kết thúc.');
                        leaveWatchParty();
                        return;
                    }
                    
                    const timeDiff = Math.abs(wpVideo.currentTime - r.current_time);
                    if (timeDiff > 2) {
                        wpVideo.currentTime = r.current_time;
                    }
                    
                    if (r.is_playing == 1 && wpVideo.paused) {
                        wpVideo.play().catch(e => console.log('Autoplay blocked'));
                    } else if (r.is_playing == 0 && !wpVideo.paused) {
                        wpVideo.pause();
                    }
                }
            })
            .finally(() => { isSyncing = false; });
        }
    }, 2000); // 2s polling
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
