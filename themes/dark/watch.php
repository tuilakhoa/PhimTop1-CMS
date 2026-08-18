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
<div class="bg-[#000000] min-h-screen text-gray-200 font-sans pb-20">
    <div class="max-w-[1400px] mx-auto px-6 md:px-12 pt-8 lg:pt-12">
    
    <!-- Video Player -->
    <div class="mb-8 bg-[#0a0a0a] rounded-2xl overflow-hidden border border-gray-900">
        <div class="aspect-video w-full bg-black relative flex items-center justify-center group" id="player-container">
<?php
$startTime = 0;
if (isset($_SESSION['user'])) {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT current_time FROM watch_history WHERE user_email = ? AND movie_slug = ? AND episode_slug = ? LIMIT 1");
        $stmt->execute([$_SESSION['user']['email'], $movie['slug'], $currentEp['slug']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['current_time'] > 0) {
            $startTime = (int)$row['current_time'];
        }
    }
}
?>
            <?php if ($isM3U8): ?>
                <video id="video-player" class="w-full h-full outline-none" controls playsinline>
                    <source src="<?= htmlspecialchars($videoUrl) ?>" type="application/x-mpegURL">
                </video>
                <!-- Thư viện Hls.js để phát m3u8 -->
                <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
                <script>
                    var video = document.getElementById('video-player');
                    var videoSrc = "<?= addslashes($videoUrl) ?>";
                    var startTime = <?= $startTime ?>;
                    
                    if (Hls.isSupported()) {
                        var hls = new Hls();
                        hls.loadSource(videoSrc);
                        hls.attachMedia(video);
                        hls.on(Hls.Events.MANIFEST_PARSED, function() {
                            if (startTime > 0) {
                                video.currentTime = startTime;
                            }
                            video.play().catch(function(e) {
                                console.log("Auto-play blocked by browser.");
                            });
                        });
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        // For Safari
                        video.src = videoSrc;
                        video.addEventListener('loadedmetadata', function() {
                            if (startTime > 0) {
                                video.currentTime = startTime;
                            }
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
        
        <div class="p-5 md:p-8 bg-[#111] border-t border-gray-900 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 tracking-tight"><a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="hover:text-gray-300 transition-colors"><?= htmlspecialchars($movie['name']) ?></a></h1>
                <h2 class="text-base text-gray-500 font-medium">Đang xem: Tập <?= htmlspecialchars($currentEp['name']) ?></h2>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="toggleWatchPartyDialog()" class="px-5 py-2.5 bg-[#1a1a1a] hover:bg-white text-gray-300 hover:text-black rounded-lg text-sm font-medium border border-gray-800 hover:border-white flex items-center transition-colors">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i> Xem Chung
                </button>
                <span class="px-4 py-2.5 bg-[#1a1a1a] text-gray-400 rounded-lg text-sm font-medium border border-gray-800 hidden sm:inline-flex items-center">
                    <i data-lucide="server" class="w-4 h-4 mr-2"></i> Server: <?= htmlspecialchars($episodes[0]['server_name'] ?? 'HLS/Embed') ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Episode List -->
    <?php if (!empty($episodes[0]['server_data'])): ?>
        <div class="bg-[#111] rounded-2xl p-6 md:p-8 border border-gray-900 mb-12">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-3">
                <h3 class="text-lg font-bold text-white flex items-center tracking-tight">
                    <i data-lucide="list-video" class="w-5 h-5 mr-3 text-white"></i> Danh sách tập
                </h3>
                <div class="relative">
                    <input type="text" id="search-episode" placeholder="Tìm tập phim..." class="bg-[#1a1a1a] text-sm text-white px-3 py-1.5 rounded-lg border border-gray-800 outline-none focus:border-white w-full md:w-48">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-3 max-h-[400px] overflow-y-auto custom-scrollbar" id="episode-list">
                <?php foreach ($episodes[0]['server_data'] as $e): 
                    $isActive = $currentEp['slug'] === $e['slug'];
                    $classes = $isActive 
                        ? "bg-white text-black font-medium border border-white" 
                        : "bg-[#1a1a1a] text-gray-400 hover:text-white border border-gray-800 hover:border-gray-500";
                ?>
                    <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($e['slug']) ?>" 
                       class="px-5 py-2.5 rounded-lg transition-colors text-sm <?= $classes ?>">
                        <?= htmlspecialchars($e['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchEp = document.getElementById('search-episode');
                    if (searchEp) {
                        searchEp.addEventListener('input', function(e) {
                            const keyword = e.target.value.toLowerCase().trim();
                            const eps = document.querySelectorAll('#episode-list a');
                            eps.forEach(ep => {
                                const text = ep.textContent.toLowerCase().trim();
                                if (text.includes(keyword)) {
                                    ep.style.display = '';
                                } else {
                                    ep.style.display = 'none';
                                }
                            });
                        });
                    }
                });
            </script>
        </div>
    <?php endif; ?>

    <!-- Movie Suggestions -->
    <?php if (!empty($suggestions)): ?>
    <div class="mt-12 mb-12">
        <h3 class="text-2xl font-bold text-white mb-8 tracking-tight">Có Thể Bạn Sẽ Thích</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-5 gap-y-10">
            <?php foreach ($suggestions as $item): ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group flex flex-col">
                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                        <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        
                        <div class="absolute top-2 left-2">
                            <span class="bg-black/70 backdrop-blur-md text-white text-[10px] font-medium px-2 py-0.5 rounded"><?= htmlspecialchars($item['quality'] ?? 'HD') ?></span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-sm font-medium text-gray-100 line-clamp-1 group-hover:text-white transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    </div>
</div>

<!-- History Logging Script -->
<?php if (isset($_SESSION['user'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const appApiKey = <?= json_encode($settings['appApiKey'] ?? '') ?>;
    const movieSlug = <?= json_encode($movie['slug']) ?>;
    const movieName = <?= json_encode($movie['name']) ?>;
    const episodeName = <?= json_encode($currentEp['name']) ?>;
    const episodeSlug = <?= json_encode($currentEp['slug']) ?>;
    const thumbUrl = <?= json_encode($movie['thumb_url'] ?? '') ?>;

    function logHistory() {
        let currentTime = 0;
        let duration = 0;
        const video = document.getElementById('video-player');
        if (video) {
            currentTime = Math.floor(video.currentTime || 0);
            duration = Math.floor(video.duration || 0);
        }

        fetch(`/api/v1/history.php?action=add&key=${appApiKey}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                movie_slug: movieSlug,
                movie_name: movieName,
                episode_name: episodeName,
                episode_slug: episodeSlug,
                thumb_url: thumbUrl,
                current_time: currentTime,
                duration: duration
            })
        }).catch(err => console.error('Error logging history:', err));
    }

    // Log immediately on load
    logHistory();

    // Log every 15 seconds if playing
    setInterval(() => {
        const video = document.getElementById('video-player');
        if (video && !video.paused) {
            logHistory();
        }
    }, 15000);

    // Log when leaving the page to capture the exact second
    window.addEventListener('beforeunload', function() {
        logHistory();
    });
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
    const userName = <?= json_encode($_SESSION['user']['name'] ?? 'Guest') ?>;
    const movieSlug = <?= json_encode($movie['slug']) ?>;
    const movieName = <?= json_encode($movie['name']) ?>;
    const episodeName = <?= json_encode($currentEp['name']) ?>;

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
