<?php 
include __DIR__ . '/header.php'; 

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://phimimg.com/';
if (!empty($movie['category']) && is_array($movie['category'])) {
    $firstCat = is_array($movie['category'][0]) ? ($movie['category'][0]['slug'] ?? '') : '';
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
                <span class="px-3 py-1.5 bg-gray-800 text-gray-300 rounded-lg text-sm font-medium border border-gray-700">
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

<?php include __DIR__ . '/footer.php'; ?>
