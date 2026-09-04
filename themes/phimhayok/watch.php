<?php include __DIR__ . '/header.php'; 

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://phimimg.com/';
if (!empty($movie['category']) && is_array($movie['category'])) {
    $firstCatObj = reset($movie['category']);
    $firstCat = is_array($firstCatObj) ? ($firstCatObj['slug'] ?? '') : (is_string($firstCatObj) ? $firstCatObj : '');
    if ($firstCat) {
        $sugData = fetchApiFilms('the-loai', $firstCat, 1, '', '', '', '', '');
        if ($sugData && isset($sugData['items'])) {
            $suggestions = array_slice($sugData['items'], 0, 6);
            $sugDomain = $sugData['domain'] ?? 'https://phimimg.com/';
        }
    }
}

// Fetch Keywords
$movieKeywords = [];
if (!empty($movie['seo_keywords'])) {
    $movieKeywords = array_map('trim', explode(',', $movie['seo_keywords']));
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
                <link rel="stylesheet" href="https://cdn.plyr.io/3.8.4/plyr.css" />
                <style>
                    :root { --plyr-color-main: #eab308; } /* Phim-yellow */
                    .plyr { border-radius: 0.5rem; overflow: hidden; height: 100%; width: 100%; }
                </style>
                <video id="video-player" class="w-full h-full outline-none" playsinline></video>
                <!-- Thư viện Hls.js và Plyr -->
                <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
                <script src="https://cdn.plyr.io/3.8.4/plyr.polyfilled.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        var video = document.getElementById('video-player');
                        var source = "<?= addslashes($videoUrl) ?>";
                        var startTime = <?= $startTime ?>;
                        
                        if (Hls.isSupported()) {
                            var hls = new Hls();
                            hls.loadSource(source);
                            hls.on(Hls.Events.MANIFEST_PARSED, function (event, data) {
                                // Lấy các mức chất lượng
                                var availableQualities = hls.levels.map((l) => l.height);
                                // Chỉ thêm tính năng chọn độ phân giải nếu có nhiều hơn 1 luồng
                                var plyrOptions = {
                                    i18n: { quality: 'Chất lượng', speed: 'Tốc độ', normal: 'Bình thường' }
                                };
                                
                                if (availableQualities.length > 1) {
                                    availableQualities.unshift(0); // 0 = Auto
                                    plyrOptions.quality = {
                                        default: 0,
                                        options: availableQualities,
                                        forced: true,
                                        onChange: (newQuality) => {
                                            if (newQuality === 0) {
                                                window.hls.currentLevel = -1; // Auto
                                            } else {
                                                window.hls.levels.forEach((level, levelIndex) => {
                                                    if (level.height === newQuality) {
                                                        window.hls.currentLevel = levelIndex;
                                                    }
                                                });
                                            }
                                        }
                                    };
                                    plyrOptions.i18n.qualityLabel = { 0: 'Tự động' };
                                }
                                
                                var player = new Plyr(video, plyrOptions);
                                window.hls = hls;
                                
                                if (startTime > 0) {
                                    player.once('canplay', () => { player.currentTime = startTime; });
                                }
                            });
                            hls.attachMedia(video);
                        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                            // Safari
                            video.src = source;
                            var player = new Plyr(video);
                            if (startTime > 0) {
                                player.once('canplay', () => { player.currentTime = startTime; });
                            }
                        }
                    });
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
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="hover:text-red-500 "><?= htmlspecialchars($movie['name']) ?></a>
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
                <button onclick="toggleWatchPartyDialog()" class="flex items-center px-4 py-2 bg-phim-yellow hover:bg-yellow-400 text-black text-sm font-bold rounded  shadow-[0_0_10px_rgba(234,179,8,0.3)]">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i> Xem Chung
                </button>
                <button onclick="reportMovieError()" class="flex items-center px-4 py-2 bg-[#1a1a1a] hover:bg-[#252525] text-gray-300 hover:text-white text-sm font-medium rounded  border border-gray-800">
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
        <!-- Main Column (Left) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Episodes List -->
            <?php if (!empty($episodes[0]['server_data'])): ?>
                <div class="bg-[#141414] rounded-xl p-5 md:p-6 border border-gray-900">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-5 border-b border-gray-800 pb-3 gap-3">
                        <h3 class="text-lg font-bold text-white flex items-center uppercase tracking-wider">
                            <i data-lucide="list-video" class="w-5 h-5 mr-2 text-red-600"></i> Danh sách tập
                        </h3>
                        <div class="relative">
                            <input type="text" id="search-episode" placeholder="Tìm tập phim..." class="bg-[#202020] text-sm text-white px-3 py-1.5 rounded-lg border border-gray-700 outline-none focus:border-red-600 w-full md:w-48 transition-colors">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2"></i>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-5 xl:grid-cols-7 gap-2.5 max-h-[400px] overflow-y-auto custom-scrollbar pr-2" id="episode-list">
                        <?php foreach ($episodes[0]['server_data'] as $e): 
                            $isActive = $currentEp['slug'] === $e['slug'];
                            $classes = $isActive 
                                ? "bg-red-600 text-white font-bold pointer-events-none ring-2 ring-red-600 ring-offset-2 ring-offset-[#141414]" 
                                : "bg-[#1a1a1a] text-gray-400 hover:bg-[#2a2a2a] hover:text-white border border-gray-800 transition-colors";
                        ?>
                            <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($e['slug']) ?>" 
                               class="flex items-center justify-center py-2.5 rounded-md text-sm <?= $classes ?>">
                                <?= htmlspecialchars($e['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var searchEp = document.getElementById('search-episode');
                            if (searchEp) {
                                searchEp.addEventListener('input', function(e) {
                                    var keyword = e.target.value.toLowerCase().trim();
                                    var eps = document.querySelectorAll('#episode-list a');
                                    eps.forEach(ep => {
                                        var text = ep.textContent.toLowerCase().trim();
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

            <!-- Movie Info (Moved to Main Column) -->
            <div class="bg-[#141414] rounded-xl p-5 md:p-6 border border-gray-900">
                <h3 class="text-lg font-bold text-white flex items-center uppercase tracking-wider mb-5 border-b border-gray-800 pb-3">
                    <i data-lucide="info" class="w-5 h-5 mr-2 text-red-600"></i> Thông tin phim
                </h3>
                
                <div class="flex flex-col sm:flex-row gap-5">
                    <div class="w-32 shrink-0 mx-auto sm:mx-0">
                        <img src="<?= htmlspecialchars($movie['thumb_url']) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-auto rounded border border-gray-800 object-cover aspect-[3/4] shadow-lg">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <h4 class="text-white font-bold text-xl leading-snug mb-1 text-center sm:text-left"><?= htmlspecialchars($movie['name']) ?></h4>
                        <p class="text-gray-400 text-sm mb-3 text-center sm:text-left"><?= htmlspecialchars($movie['origin_name']) ?></p>
                        
                        <div class="flex flex-wrap items-center gap-3 mb-4 justify-center sm:justify-start">
                            <span class="inline-block px-2.5 py-1 bg-red-600/20 text-red-500 text-xs font-semibold rounded border border-red-600/30">
                                <?= htmlspecialchars($movie['episode_current'] ?? 'Full') ?>
                            </span>
                            <span class="inline-block px-2.5 py-1 bg-[#252525] text-gray-300 text-xs font-medium rounded border border-gray-700">
                                <?= htmlspecialchars($movie['year'] ?? '') ?>
                            </span>
                        </div>
                        
                        <p class="text-gray-400 text-sm leading-relaxed mb-4 text-justify sm:text-left">
                            <?= htmlspecialchars(strip_tags($movie['content'] ?? '')) ?>
                        </p>
                        
                        <div class="mt-auto">
                            <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="inline-flex items-center justify-center px-6 py-2 bg-[#1a1a1a] hover:bg-[#252525] text-white text-sm font-medium rounded border border-gray-800 transition-colors w-full sm:w-auto">
                                <i data-lucide="external-link" class="w-4 h-4 mr-2 text-gray-400"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Column (Right) -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Phim Đề Xuất -->
            <?php if (!empty($suggestions)): ?>
            <div class="bg-[#141414] rounded-xl p-5 md:p-6 border border-gray-900">
                <h3 class="text-lg font-bold text-white flex items-center uppercase tracking-wider mb-5 border-b border-gray-800 pb-3">
                    <i data-lucide="sparkles" class="w-5 h-5 mr-2 text-red-600"></i> Phim Đề Xuất
                </h3>
                <div class="space-y-4">
                    <?php foreach ($suggestions as $item): ?>
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="flex gap-4 group">
                            <div class="w-20 shrink-0 relative rounded overflow-hidden aspect-[3/4]">
                                <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                <div class="absolute inset-0 bg-black/40 group-hover:bg-transparent transition-colors"></div>
                            </div>
                            <div class="flex-1 py-1">
                                <h4 class="text-white text-sm font-medium leading-snug group-hover:text-red-500 transition-colors line-clamp-2 mb-1"><?= htmlspecialchars($item['name']) ?></h4>
                                <p class="text-gray-500 text-xs mb-1.5"><?= htmlspecialchars($item['year'] ?? '') ?></p>
                                <span class="inline-block px-1.5 py-0.5 bg-[#1a1a1a] text-gray-400 text-[10px] font-medium rounded border border-gray-800">
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
    var appApiKey = <?= json_encode($settings['appApiKey'] ?? '') ?>;
    var movieSlug = <?= json_encode($movie['slug']) ?>;
    var movieName = <?= json_encode($movie['name']) ?>;
    var episodeName = <?= json_encode($currentEp['name']) ?>;
    var thumbUrl = <?= json_encode($movie['thumb_url'] ?? '') ?>;
    // Function to log history
    function logHistory() {
        var currentTime = 0;
        var duration = 0;
        var video = document.getElementById('video-player');
        if (video) {
            currentTime = Math.floor(video.currentTime || 0);
            duration = Math.floor(video.duration || 0);
        }

        // Lưu lịch sử xem vào LocalStorage (cho khách và thành viên)
        try {
            var history = JSON.parse(localStorage.getItem('phimhayok_watch_history')) || [];
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
        var video = document.getElementById('video-player');
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
    var deviceId = localStorage.getItem('phimtop1_device_id');
    if (!deviceId) {
        deviceId = 'web-' + Math.random().toString(36).substr(2, 9) + '-' + Date.now();
        localStorage.setItem('phimtop1_device_id', deviceId);
    }
    
    var isLogged = <?= isset($_SESSION['user']) ? 1 : 0 ?>;
    var userName = <?= json_encode($_SESSION['user']['name'] ?? 'Guest') ?>;
    var movieSlug = <?= json_encode($movie['slug']) ?>;
    var movieName = <?= json_encode($movie['name']) ?>;
    var episodeName = <?= json_encode($currentEp['name']) ?>;

    function sendHeartbeat() {
        var progress = 0;
        var video = document.getElementById('video-player');
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
        var video = document.getElementById('video-player');
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
        <button onclick="toggleWatchPartyDialog()" class="absolute top-4 right-4 text-gray-400 hover:text-white ">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="users" class="w-6 h-6 mr-2 text-phim-yellow"></i> Phòng Xem Chung
        </h3>
        
        <div id="wp-setup-view">
            <div class="space-y-4">
                <div>
                    <button onclick="createWatchParty()" class="w-full bg-phim-yellow hover:bg-yellow-400 text-black py-3 rounded-xl font-bold  flex items-center justify-center shadow-[0_0_15px_rgba(234,179,8,0.2)]">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> Tạo phòng mới
                    </button>
                    <label class="flex items-center justify-center text-sm text-gray-400 mt-3 cursor-pointer group">
                        <input type="checkbox" id="wp-is-public" class="mr-2 rounded border-gray-700 bg-gray-800 text-phim-yellow focus:ring-phim-yellow focus:ring-offset-[#111]  w-4 h-4"> 
                        <span class="group-hover:text-gray-300 ">Công khai phòng này để mọi người cùng xem</span>
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
                        <input type="text" id="wp-room-input" placeholder="Ví dụ: A1B2C3" class="flex-1 bg-[#1a1a1a] border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-phim-yellow uppercase ">
                        <button onclick="joinWatchPartyBtn()" class="bg-[#222] hover:bg-[#333] text-white px-4 py-2 rounded-xl font-medium  border border-white/5">
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
                    <span id="wp-status-text" class="text-green-400 font-bold flex items-center"><span class="w-2 h-2 rounded-full bg-green-400 mr-1.5 "></span> Đã kết nối</span>
                </div>
                <div class="text-sm text-gray-300 flex items-center justify-between">
                    <span>Vai trò:</span>
                    <span id="wp-role-text" class="text-white font-bold"></span>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button onclick="copyWatchPartyLink()" class="flex-1 bg-[#222] hover:bg-[#333] text-white py-2 rounded-xl font-medium  flex items-center justify-center text-sm border border-white/5">
                    <i data-lucide="copy" class="w-4 h-4 mr-1.5"></i> Copy Link
                </button>
                <button onclick="leaveWatchParty()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-xl font-bold  flex items-center justify-center text-sm shadow-[0_0_10px_rgba(220,38,38,0.3)]">
                    <i data-lucide="log-out" class="w-4 h-4 mr-1.5"></i> Rời Phòng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var wpRoomCode = null;
var wpIsHost = false;
var wpSyncInterval = null;
var wpVideo = document.getElementById('video-player');

// Check URL for party code
document.addEventListener('DOMContentLoaded', function() {
    var urlParams = new URLSearchParams(window.location.search);
    var partyCode = urlParams.get('party');
    if (partyCode) {
        document.getElementById('wp-room-input').value = partyCode;
        joinWatchParty(partyCode);
    }
});

function reportMovieError() {
    <?php if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])): ?>
        Swal.fire({
            title: 'Yêu cầu đăng nhập',
            text: 'Bạn cần đăng nhập để gửi báo lỗi!',
            icon: 'warning',
            background: '#111',
            color: '#fff',
            confirmButtonColor: '#eab308'
        });
        return;
    <?php endif; ?>

    Swal.fire({
        title: 'Báo lỗi phim',
        html: `
            <style>
                .report-option input:checked + div {
                    border-color: #eab308;
                    background-color: rgba(234, 179, 8, 0.1);
                }
                .report-option input:checked + div span {
                    color: #eab308;
                    font-weight: 600;
                }
                .report-option input:checked + div .radio-circle {
                    border-color: #eab308;
                    background-color: #eab308;
                    box-shadow: inset 0 0 0 3px rgba(234, 179, 8, 0.2);
                }
            </style>
            <div class="text-left space-y-2.5 mt-2" onchange="
                var sel = document.querySelector('input[name=report_error]:checked');
                var ta = document.getElementById('other-detail-container');
                if (sel && sel.value === 'Khác') {
                    ta.classList.remove('hidden');
                    document.getElementById('report-detail-text').focus();
                } else {
                    ta.classList.add('hidden');
                }
            ">
                <label class="report-option block cursor-pointer">
                    <input type="radio" name="report_error" value="Phim không phát được / Đứng hình" class="hidden">
                    <div class="px-4 py-3 rounded-xl border border-gray-800 bg-[#141414] hover:bg-[#1a1a1a] transition-colors flex items-center">
                        <div class="radio-circle w-4 h-4 rounded-full border border-gray-600 mr-3 flex-shrink-0 transition-colors"></div>
                        <span class="text-gray-300 text-sm flex-1">Phim không phát được / Đứng hình</span>
                    </div>
                </label>
                <label class="report-option block cursor-pointer">
                    <input type="radio" name="report_error" value="Lỗi phụ đề / Thuyết minh" class="hidden">
                    <div class="px-4 py-3 rounded-xl border border-gray-800 bg-[#141414] hover:bg-[#1a1a1a] transition-colors flex items-center">
                        <div class="radio-circle w-4 h-4 rounded-full border border-gray-600 mr-3 flex-shrink-0 transition-colors"></div>
                        <span class="text-gray-300 text-sm flex-1">Lỗi phụ đề / Thuyết minh</span>
                    </div>
                </label>
                <label class="report-option block cursor-pointer">
                    <input type="radio" name="report_error" value="Âm thanh bị lệch / Không có tiếng" class="hidden">
                    <div class="px-4 py-3 rounded-xl border border-gray-800 bg-[#141414] hover:bg-[#1a1a1a] transition-colors flex items-center">
                        <div class="radio-circle w-4 h-4 rounded-full border border-gray-600 mr-3 flex-shrink-0 transition-colors"></div>
                        <span class="text-gray-300 text-sm flex-1">Âm thanh bị lệch / Không có tiếng</span>
                    </div>
                </label>
                <label class="report-option block cursor-pointer">
                    <input type="radio" name="report_error" value="Chất lượng hình ảnh kém" class="hidden">
                    <div class="px-4 py-3 rounded-xl border border-gray-800 bg-[#141414] hover:bg-[#1a1a1a] transition-colors flex items-center">
                        <div class="radio-circle w-4 h-4 rounded-full border border-gray-600 mr-3 flex-shrink-0 transition-colors"></div>
                        <span class="text-gray-300 text-sm flex-1">Chất lượng hình ảnh kém</span>
                    </div>
                </label>
                <label class="report-option block cursor-pointer">
                    <input type="radio" name="report_error" value="Tập phim bị trùng / Thiếu tập" class="hidden">
                    <div class="px-4 py-3 rounded-xl border border-gray-800 bg-[#141414] hover:bg-[#1a1a1a] transition-colors flex items-center">
                        <div class="radio-circle w-4 h-4 rounded-full border border-gray-600 mr-3 flex-shrink-0 transition-colors"></div>
                        <span class="text-gray-300 text-sm flex-1">Tập phim bị trùng / Thiếu tập</span>
                    </div>
                </label>
                <label class="report-option block cursor-pointer">
                    <input type="radio" name="report_error" value="Khác" class="hidden">
                    <div class="px-4 py-3 rounded-xl border border-gray-800 bg-[#141414] hover:bg-[#1a1a1a] transition-colors flex items-center">
                        <div class="radio-circle w-4 h-4 rounded-full border border-gray-600 mr-3 flex-shrink-0 transition-colors"></div>
                        <span class="text-gray-300 text-sm flex-1">Lỗi khác (Nhập chi tiết)</span>
                    </div>
                </label>
                <div id="other-detail-container" class="hidden mt-2">
                    <textarea id="report-detail-text" class="w-full bg-[#141414] border border-gray-700 rounded-xl p-3 text-white text-sm focus:outline-none focus:border-[#eab308] h-24 resize-none transition-colors" placeholder="Nhập nội dung báo lỗi chi tiết..."></textarea>
                </div>
            </div>
        `,
        preConfirm: () => {
            const selected = document.querySelector('input[name="report_error"]:checked');
            if (!selected) {
                Swal.showValidationMessage('Vui lòng chọn một loại lỗi!');
                return false;
            }
            let detail = '';
            if (selected.value === 'Khác') {
                detail = document.getElementById('report-detail-text').value.trim();
                if (!detail) {
                    Swal.showValidationMessage('Vui lòng nhập chi tiết lỗi!');
                    return false;
                }
            }
            return { type: selected.value, detail: detail };
        },
        background: '#111',
        color: '#fff',
        showCancelButton: true,
        confirmButtonText: 'Gửi báo cáo',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#eab308',
        cancelButtonColor: '#333',
        customClass: {
            confirmButton: 'text-black font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let errorType = result.value.type;
            let detail = result.value.detail;
            var msg = "Phim: <?= addslashes(htmlspecialchars($movie['name'])) ?> (<?= addslashes(htmlspecialchars($movie['slug'])) ?>) - Tập: <?= addslashes(htmlspecialchars($currentEp['name'])) ?> - Lỗi: " + errorType + (detail ? " - Chi tiết: " + detail : "");
            fetch('/api/v1/feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Thành công',
                        text: 'Cảm ơn bạn đã báo lỗi. Admin sẽ kiểm tra và khắc phục sớm nhất!',
                        icon: 'success',
                        background: '#111',
                        color: '#fff',
                        confirmButtonColor: '#eab308',
                        customClass: { confirmButton: 'text-black font-bold' }
                    });
                } else {
                    Swal.fire('Lỗi!', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Lỗi!', 'Có sự cố xảy ra, vui lòng thử lại sau.', 'error');
            });
        }
    });
}

function toggleWatchPartyDialog() {
    var dialog = document.getElementById('watch-party-dialog');
    dialog.classList.toggle('hidden');
    if (!dialog.classList.contains('hidden')) {
        lucide.createIcons();
        if (!wpRoomCode) {
            fetchPublicRooms();
        }
    }
}

function fetchPublicRooms() {
    var movieSlug = '<?= addslashes($movie['slug']) ?>';
    fetch('/api/v1/watch_party.php?action=list_public&movie_slug=' + movieSlug)
    .then(res => res.json())
    .then(data => {
        var container = document.getElementById('wp-public-rooms-container');
        var list = document.getElementById('wp-public-rooms-list');
        if (data.status === 'success' && data.data.length > 0) {
            var html = '';
            data.data.forEach(room => {
                html += `
                <div class="flex items-center justify-between bg-[#1a1a1a] rounded-lg p-3 border border-white/5">
                    <div>
                        <div class="text-phim-yellow font-mono font-bold text-sm">${room.room_code}</div>
                        <div class="text-xs text-gray-400">Host: ${room.creator_name} - Tập ${room.episode_name}</div>
                    </div>
                    <button onclick="joinWatchParty('${room.room_code}')" class="px-3 py-1.5 bg-[#222] hover:bg-[#333] text-white text-xs font-bold rounded-lg  border border-white/5">
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
    var badge = document.getElementById('wp-player-badge');
    if (!badge) {
        badge = document.createElement('div');
        badge.id = 'wp-player-badge';
        badge.className = 'absolute top-4 left-4 z-20 bg-black/60 backdrop-blur border border-[#eab308]/30 text-white px-3 py-1.5 rounded text-xs font-bold flex items-center cursor-pointer hover:bg-black/80  shadow-lg shadow-[#eab308]/10';
        badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500 mr-2 "></span> Watch Party: <span class="text-[#eab308] ml-1">' + code + '</span>';
        badge.onclick = toggleWatchPartyDialog;
        document.getElementById('player-container').appendChild(badge);
    }
    
    startWpSync();
}

function showWpSetupView() {
    document.getElementById('wp-setup-view').classList.remove('hidden');
    document.getElementById('wp-active-view').classList.add('hidden');
    var badge = document.getElementById('wp-player-badge');
    if (badge) badge.remove();
}

function createWatchParty() {
    var movieSlug = '<?= addslashes($movie['slug']) ?>';
    var episodeName = '<?= addslashes($currentEp['name']) ?>';
    var userName = '<?= addslashes($_SESSION['user']['name'] ?? 'Guest') ?>';
    var isPublic = document.getElementById('wp-is-public').checked ? 1 : 0;
    
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
    var code = document.getElementById('wp-room-input').value.trim().toUpperCase();
    if (code) joinWatchParty(code);
}

function joinWatchParty(code) {
    var movieSlug = '<?= addslashes($movie['slug']) ?>';
    
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
    var url = new URL(window.location.href);
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

var isSyncing = false;
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
                    var r = data.data;
                    if (r.status !== 'active') {
                        alert('Phòng xem chung đã bị khóa hoặc kết thúc.');
                        leaveWatchParty();
                        return;
                    }
                    
                    var timeDiff = Math.abs(wpVideo.currentTime - r.current_time);
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
