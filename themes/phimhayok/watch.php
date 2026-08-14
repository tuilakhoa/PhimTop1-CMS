<?php include __DIR__ . '/header.php'; 

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://phimimg.com/';
if (!empty($movie['category']) && is_array($movie['category'])) {
    $firstCatObj = reset($movie['category']);
    $firstCat = is_array($firstCatObj) ? ($firstCatObj['slug'] ?? '') : (is_string($firstCatObj) ? $firstCatObj : '');
    if ($firstCat) {
        $sugRes = @file_get_contents("https://phimapi.com/v1/api/the-loai/" . urlencode($firstCat) . "?limit=6");
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

<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1400px] py-6">
    <!-- Video Player Area -->
    <div class="bg-black rounded-lg md:rounded-xl overflow-hidden shadow-2xl border border-gray-900 mb-6">
        <div class="aspect-video w-full relative flex items-center justify-center bg-black group" id="player-container">
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
                            if (startTime > 0) video.currentTime = startTime;
                            video.play().catch(function(e) {
                                console.log("Auto-play blocked by browser.");
                            });
                        });
                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                        // For Safari
                        video.src = videoSrc;
                        video.addEventListener('loadedmetadata', function() {
                            if (startTime > 0) video.currentTime = startTime;
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
                <div class="text-gray-400 text-center p-8 flex flex-col items-center">
                    <i data-lucide="alert-triangle" class="w-16 h-16 text-yellow-600 mb-4"></i>
                    <p class="text-lg">Không tìm thấy nguồn phát phù hợp cho tập phim này.</p>
                    <p class="text-sm mt-2 text-gray-500">Vui lòng thử lại sau hoặc chọn server khác (nếu có).</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Player Controls/Info Bar -->
        <div class="p-4 md:p-6 bg-[#0f0f0f] border-t border-gray-900 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="hover:text-red-500 transition-colors"><?= htmlspecialchars($movie['name']) ?></a>
                </h1>
                <div class="flex items-center text-gray-400 text-sm">
                    <span class="mr-4 flex items-center">
                        <i data-lucide="film" class="w-4 h-4 mr-1"></i>
                        Đang xem: <strong class="text-white ml-1 font-medium">Tập <?= htmlspecialchars($currentEp['name']) ?></strong>
                    </span>
                    <span class="flex items-center">
                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                        <?= number_format(rand(1000, 99999)) ?> lượt xem
                    </span>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <button onclick="toggleWatchPartyDialog()" class="flex items-center px-4 py-2 bg-phim-yellow hover:bg-yellow-400 text-black text-sm font-bold rounded transition-colors shadow-[0_0_10px_rgba(234,179,8,0.3)]">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i> Xem Chung
                </button>
                <button class="flex items-center px-4 py-2 bg-[#1a1a1a] hover:bg-[#252525] text-gray-300 hover:text-white text-sm font-medium rounded transition-colors border border-gray-800">
                    <i data-lucide="flag" class="w-4 h-4 mr-2 text-red-500"></i> Báo lỗi
                </button>
                <div class="px-4 py-2 bg-red-600/10 border border-red-600/30 text-red-500 rounded text-sm font-medium flex items-center">
                    <i data-lucide="server" class="w-4 h-4 mr-2"></i>
                    Server: <?= htmlspecialchars($episodes[0]['server_name'] ?? 'HLS/Embed') ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Episodes List -->
        <div class="lg:col-span-2 space-y-6">
            <?php if (!empty($episodes[0]['server_data'])): ?>
                <div class="bg-[#141414] rounded-xl p-5 md:p-6 border border-gray-900">
                    <div class="flex items-center justify-between mb-5 border-b border-gray-800 pb-3">
                        <h3 class="text-lg font-bold text-white flex items-center uppercase tracking-wider">
                            <i data-lucide="list-video" class="w-5 h-5 mr-2 text-red-600"></i> Danh sách tập
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-5 xl:grid-cols-7 gap-2.5 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                        <?php foreach ($episodes[0]['server_data'] as $e): 
                            $isActive = $currentEp['slug'] === $e['slug'];
                            $classes = $isActive 
                                ? "bg-red-600 text-white font-bold pointer-events-none ring-2 ring-red-600 ring-offset-2 ring-offset-[#141414]" 
                                : "bg-[#1a1a1a] text-gray-400 hover:bg-[#2a2a2a] hover:text-white border border-gray-800";
                        ?>
                            <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($e['slug']) ?>" 
                               class="flex items-center justify-center py-2.5 rounded-md transition-all text-sm <?= $classes ?>">
                                <?= htmlspecialchars($e['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar (Related or Next Episodes placeholder) -->
        <div class="lg:col-span-1">
            <div class="bg-[#141414] rounded-xl p-5 md:p-6 border border-gray-900">
                <h3 class="text-lg font-bold text-white flex items-center uppercase tracking-wider mb-5 border-b border-gray-800 pb-3">
                    <i data-lucide="info" class="w-5 h-5 mr-2 text-red-600"></i> Thông tin phim
                </h3>
                
                <div class="flex space-x-4 mb-4">
                    <img src="<?= htmlspecialchars($movie['thumb_url']) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-24 h-auto rounded border border-gray-800 object-cover aspect-[3/4]">
                    <div>
                        <h4 class="text-white font-bold text-base leading-snug mb-1"><?= htmlspecialchars($movie['name']) ?></h4>
                        <p class="text-gray-500 text-xs mb-2"><?= htmlspecialchars($movie['origin_name']) ?></p>
                        <div class="text-red-500 text-sm font-semibold mb-1"><?= htmlspecialchars($movie['episode_current'] ?? '') ?></div>
                        <div class="text-gray-400 text-xs"><?= htmlspecialchars($movie['year'] ?? '') ?></div>
                    </div>
                </div>
                
                <p class="text-gray-400 text-sm line-clamp-4 leading-relaxed">
                    <?= htmlspecialchars(strip_tags($movie['content'] ?? '')) ?>
                </p>
                
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="block w-full mt-4 text-center py-2 bg-[#1a1a1a] hover:bg-[#252525] text-white text-sm font-medium rounded transition-colors">
                    Xem chi tiết
                </a>
            </div>

            <!-- Phim Đề Xuất -->
            <?php if (!empty($suggestions)): ?>
            <div class="bg-[#141414] rounded-xl p-5 md:p-6 border border-gray-900 mt-6">
                <h3 class="text-lg font-bold text-white flex items-center uppercase tracking-wider mb-5 border-b border-gray-800 pb-3">
                    <i data-lucide="sparkles" class="w-5 h-5 mr-2 text-red-600"></i> Phim Đề Xuất
                </h3>
                <div class="space-y-4">
                    <?php foreach ($suggestions as $item): ?>
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="flex gap-4 group">
                            <div class="w-20 shrink-0 relative rounded overflow-hidden aspect-[3/4]">
                                <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                <div class="absolute inset-0 bg-black/40 group-hover:bg-transparent transition-colors"></div>
                            </div>
                            <div class="flex-1 py-1">
                                <h4 class="text-white text-sm font-medium leading-snug group-hover:text-red-500 transition-colors line-clamp-2 mb-1"><?= htmlspecialchars($item['name']) ?></h4>
                                <p class="text-gray-500 text-xs mb-1"><?= htmlspecialchars($item['year'] ?? '') ?></p>
                                <span class="inline-block px-1.5 py-0.5 bg-red-600/20 text-red-500 text-[10px] rounded border border-red-600/30">
                                    <?= htmlspecialchars($item['quality'] ?? 'HD') ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- History Logging Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const appApiKey = '<?= addslashes($settings['appApiKey'] ?? '') ?>';
    const movieSlug = '<?= addslashes($movie['slug']) ?>';
    const movieName = '<?= addslashes($movie['name']) ?>';
    const episodeName = '<?= addslashes($currentEp['name']) ?>';
    // Function to log history
    function logHistory() {
        let currentTime = 0;
        let duration = 0;
        const video = document.getElementById('video-player');
        if (video) {
            currentTime = Math.floor(video.currentTime || 0);
            duration = Math.floor(video.duration || 0);
        }

        // Lưu lịch sử xem vào LocalStorage (cho khách và thành viên)
        try {
            let history = JSON.parse(localStorage.getItem('phimhayok_watch_history')) || [];
            // Loại bỏ phim này nếu đã có trong lịch sử để đưa lên đầu
            history = history.filter(h => h.slug !== movieSlug);
            history.unshift({
                slug: movieSlug,
                name: movieName,
                episode: episodeName,
                thumb: thumbUrl,
                url: window.location.pathname,
                time: new Date().getTime(),
                currentTime: currentTime
            });
            // Giữ tối đa 20 phim
            if (history.length > 20) {
                history = history.slice(0, 20);
            }
            localStorage.setItem('phimhayok_watch_history', JSON.stringify(history));
        } catch(e) {
            console.error('Error saving local history:', e);
        }

        <?php if (isset($_SESSION['user'])): ?>
        // Lưu lịch sử xem vào Database qua API cho thành viên đã đăng nhập
        fetch(`/api/v1/history.php?action=add&key=${appApiKey}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                movie_slug: movieSlug,
                movie_name: movieName,
                episode_name: episodeName,
                episode_slug: '<?= addslashes($currentEp['slug']) ?>',
                thumb_url: thumbUrl,
                current_time: currentTime,
                duration: duration
            })
        }).catch(err => console.error('Error logging history:', err));
        <?php endif; ?>
    }

    // Log immediately
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
<div id="watch-party-dialog" class="fixed inset-0 bg-black/90 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-[#111] border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl relative">
        <button onclick="toggleWatchPartyDialog()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="users" class="w-6 h-6 mr-2 text-phim-yellow"></i> Phòng Xem Chung
        </h3>
        
        <div id="wp-setup-view">
            <div class="space-y-4">
                <div>
                    <button onclick="createWatchParty()" class="w-full bg-phim-yellow hover:bg-yellow-400 text-black py-3 rounded-xl font-bold transition-colors flex items-center justify-center shadow-[0_0_15px_rgba(234,179,8,0.2)]">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> Tạo phòng mới
                    </button>
                    <label class="flex items-center justify-center text-sm text-gray-400 mt-3 cursor-pointer group">
                        <input type="checkbox" id="wp-is-public" class="mr-2 rounded border-gray-700 bg-gray-800 text-phim-yellow focus:ring-phim-yellow focus:ring-offset-[#111] transition-colors w-4 h-4"> 
                        <span class="group-hover:text-gray-300 transition-colors">Công khai phòng này để mọi người cùng xem</span>
                    </label>
                </div>
                
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-white/10"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-500 text-sm">Hoặc</span>
                    <div class="flex-grow border-t border-white/10"></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Nhập mã phòng</label>
                    <div class="flex space-x-2">
                        <input type="text" id="wp-room-input" placeholder="Ví dụ: A1B2C3" class="flex-1 bg-[#1a1a1a] border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-phim-yellow uppercase transition-colors">
                        <button onclick="joinWatchPartyBtn()" class="bg-[#222] hover:bg-[#333] text-white px-4 py-2 rounded-xl font-medium transition-colors border border-white/5">
                            Vào
                        </button>
                    </div>
                </div>
                
                <div id="wp-public-rooms-container" class="hidden mt-4 pt-4 border-t border-white/10">
                    <h4 class="text-sm font-bold text-gray-300 mb-3 flex items-center">
                        <i data-lucide="globe" class="w-4 h-4 mr-1.5 text-phim-yellow"></i> Các phòng đang mở
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
                <div class="text-4xl font-mono font-bold text-phim-yellow tracking-wider mb-2" id="wp-room-code-display"></div>
                <p class="text-xs text-gray-500">Gửi mã này hoặc link trang web cho bạn bè để cùng xem.</p>
            </div>
            
            <div class="bg-[#1a1a1a] rounded-xl p-3 mb-4 text-left border border-white/5">
                <div class="text-sm text-gray-300 flex items-center justify-between mb-1">
                    <span>Trạng thái:</span>
                    <span id="wp-status-text" class="text-green-400 font-bold flex items-center"><span class="w-2 h-2 rounded-full bg-green-400 mr-1.5 animate-pulse"></span> Đã kết nối</span>
                </div>
                <div class="text-sm text-gray-300 flex items-center justify-between">
                    <span>Vai trò:</span>
                    <span id="wp-role-text" class="text-white font-bold"></span>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button onclick="copyWatchPartyLink()" class="flex-1 bg-[#222] hover:bg-[#333] text-white py-2 rounded-xl font-medium transition-colors flex items-center justify-center text-sm border border-white/5">
                    <i data-lucide="copy" class="w-4 h-4 mr-1.5"></i> Copy Link
                </button>
                <button onclick="leaveWatchParty()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-xl font-bold transition-colors flex items-center justify-center text-sm shadow-[0_0_10px_rgba(220,38,38,0.3)]">
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
                <div class="flex items-center justify-between bg-[#1a1a1a] rounded-lg p-3 border border-white/5">
                    <div>
                        <div class="text-phim-yellow font-mono font-bold text-sm">${room.room_code}</div>
                        <div class="text-xs text-gray-400">Host: ${room.creator_name} - Tập ${room.episode_name}</div>
                    </div>
                    <button onclick="joinWatchParty('${room.room_code}')" class="px-3 py-1.5 bg-[#222] hover:bg-[#333] text-white text-xs font-bold rounded-lg transition-colors border border-white/5">
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
        badge.className = 'absolute top-4 left-4 z-20 bg-black/60 backdrop-blur border border-[#eab308]/30 text-white px-3 py-1.5 rounded text-xs font-bold flex items-center cursor-pointer hover:bg-black/80 transition-colors shadow-lg shadow-[#eab308]/10';
        badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></span> Watch Party: <span class="text-[#eab308] ml-1">' + code + '</span>';
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
