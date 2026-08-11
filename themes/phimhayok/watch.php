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
    const thumbUrl = '<?= addslashes($movie['thumb_url'] ?? '') ?>';
    
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
            time: new Date().getTime()
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
            thumb_url: thumbUrl
        })
    }).then(res => res.json())
      .then(data => console.log('History logged to DB:', data))
      .catch(err => console.error('Error logging history:', err));
    <?php endif; ?>
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

<?php include __DIR__ . '/footer.php'; ?>
