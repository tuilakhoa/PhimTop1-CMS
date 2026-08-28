<?php 
// Meta for movie
$year = $movie['year'] ?? '';
$country = (!empty($movie['country']) && is_array($movie['country'])) ? $movie['country'][0]['name'] : '';
$episodes_total = $movie['episode_total'] ?? '';
$quality = $movie['quality'] ?? '';
$categories = [];
if (!empty($movie['category']) && is_array($movie['category'])) {
    foreach ($movie['category'] as $c) {
        $categories[] = is_array($c) ? ($c['name'] ?? '') : $c;
    }
}
$categories_str = implode(' - ', array_filter($categories));
$meta_tags = array_filter([$quality, $year, $country, $episodes_total, $categories_str]);

$preloadImage = !empty($movie['poster_url']) ? $movie['poster_url'] : (!empty($movie['thumb_url']) ? $movie['thumb_url'] : '');

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


// Get the first episode link for the Play button
$first_ep_link = '#';
if (!empty($episodes[0]['server_data'])) {
    $first_ep_link = '/' . ($settings["slugWatch"] ?? "xem-phim") . '/' . urlencode($slug) . '/' . urlencode($episodes[0]['server_data'][0]['slug']);
}

// Get starting time
$startTime = 0;
if (isset($currentEp) && $currentEp && isset($_SESSION['user'])) {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT current_time, duration FROM watch_history WHERE user_email = ? AND movie_slug = ? AND episode_slug = ? AND profile_id = ? LIMIT 1");
        $profileId = isset($_SESSION['current_profile']) ? (int)$_SESSION['current_profile']['id'] : 0;
        $stmt->execute([$_SESSION['user']['email'], $movie['slug'], $currentEp['slug'], $profileId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $duration = 0;
        if ($row) {
            if ($row['current_time'] > 0) $startTime = (int)$row['current_time'];
            if ($row['duration'] > 0) $duration = (int)$row['duration'];
        }
    }
}
?>

<?php if (isset($currentEp) && $currentEp): ?>
<div class="bg-black pt-[60px] md:pt-[70px]">
    <!-- THEATER MODE WATCH PAGE -->
    <div class="w-full px-0 md:px-8 lg:px-12 2xl:px-20 mx-auto pt-0 md:pt-6">
        <!-- Video Player -->
        <div class="w-full bg-black md:rounded-xl overflow-hidden shadow-2xl border-0 md:border border-gray-800">
                    <div class="aspect-video w-full relative flex items-center justify-center group" id="player-container">
                        <?php if ($isM3U8): ?>
                            <link rel="stylesheet" href="https://cdn.plyr.io/3.8.4/plyr.css" />
                            <style>
                                :root { --plyr-color-main: #dc2626; } /* Red theme */
                                .plyr { overflow: hidden; height: 100%; width: 100%; }
                            </style>
                            <video id="video-player" class="w-full h-full outline-none bg-black" playsinline poster="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : ($movie['thumb_url'] ?? '')) ?>"></video>
                            <script defer src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
                            <script defer src="https://cdn.plyr.io/3.8.4/plyr.polyfilled.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const video = document.getElementById('video-player');
                                    const source = "<?= addslashes($videoUrl) ?>";
                                    let startTime = <?= $startTime ?>;
                                    
                                    if (Hls.isSupported()) {
                                        const hls = new Hls();
                                        hls.loadSource(source);
                                        hls.on(Hls.Events.MANIFEST_PARSED, function () {
                                            const player = new Plyr(video, {
                                                i18n: { quality: 'Chất lượng', speed: 'Tốc độ', normal: 'Bình thường' }
                                            });
                                            if (startTime > 0) player.once('canplay', () => { player.currentTime = startTime; });
                                        });
                                        hls.attachMedia(video);
                                    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                        video.src = source;
                                        video.addEventListener('loadedmetadata', () => {
                                            if (startTime > 0) video.currentTime = startTime;
                                        });
                                        new Plyr(video);
                                    }
                                });
                            </script>
                        <?php elseif ($isMp4): ?>
                            <video controls class="w-full h-full bg-black" poster="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : ($movie['thumb_url'] ?? '')) ?>">
                                <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
                            </video>
                        <?php elseif ($videoUrl): ?>
                            <iframe src="<?= htmlspecialchars($currentEp['link_embed']) ?>" class="absolute inset-0 w-full h-full bg-black" allowfullscreen frameborder="0"></iframe>
                        <?php else: ?>
                            <div class="text-white text-center p-8">
                                <i data-lucide="alert-triangle" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
                                <p>Không tìm thấy nguồn phát phù hợp cho tập phim này.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Player Control Bar -->
                    <div class="p-4 bg-[#111] flex flex-wrap justify-between items-center gap-3">
                        <div class="flex items-center text-gray-400 text-sm">
                            <i data-lucide="server" class="w-4 h-4 mr-1.5 text-red-500"></i> 
                            Server: <?= htmlspecialchars($episodes[0]['server_name'] ?? 'HLS/Embed') ?>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="toggleTheatreMode()" class="px-4 py-2 bg-gray-800 hover:bg-red-600 text-white rounded-md text-sm font-medium transition-colors flex items-center shadow-lg">
                                <i data-lucide="monitor" class="w-4 h-4 mr-1.5"></i> Rạp Hát
                            </button>
                        </div>
                    </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- CINEMATIC MOVIE DETAIL BACKDROP -->
<div class="relative w-full min-h-[70vh] lg:min-h-[85vh] bg-black flex items-end pb-12 pt-[80px]">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($movie['poster_url']) ? $movie['poster_url'] : ($movie['thumb_url'] ?? ''))) ?>" alt="Backdrop" class="w-full h-full object-cover opacity-50 blur-[2px] md:blur-none">
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/60 to-transparent hidden md:block"></div>
    </div>
    
    <div class="relative z-10 w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto">
        <div class="flex flex-col md:flex-row gap-8 items-end md:items-stretch">
            <!-- Poster Desktop -->
            <div class="hidden md:block w-48 lg:w-64 shrink-0 shadow-2xl rounded-xl overflow-hidden border border-white/10 self-end">
                <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($movie['thumb_url']) ? $movie['thumb_url'] : ($movie['poster_url'] ?? ''))) ?>" class="w-full object-cover aspect-[2/3]">
            </div>
            
            <!-- Info -->
            <div class="flex-1 pb-4">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <?php if(!empty($movie['quality'])): ?>
                    <span class="bg-white/20 backdrop-blur text-white px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider"><?= htmlspecialchars($movie['quality']) ?></span>
                    <?php endif; ?>
                    <span class="bg-red-600 text-white px-2 py-0.5 rounded text-xs font-bold"><?= htmlspecialchars($movie['year'] ?? date('Y')) ?></span>
                    <span class="text-gray-300 text-sm flex items-center gap-1"><i data-lucide="clock" class="w-4 h-4 text-gray-400"></i> <?= htmlspecialchars($movie['time'] ?? 'N/A') ?></span>
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-black text-white leading-tight tracking-tight drop-shadow-2xl mb-2"><?= htmlspecialchars($movie['name']) ?></h1>
                <?php if(!empty($movie['origin_name'])): ?>
                <h2 class="text-lg md:text-xl text-gray-400 font-medium mb-6 italic"><?= htmlspecialchars($movie['origin_name']) ?></h2>
                <?php endif; ?>
                
                <div class="flex flex-wrap gap-4 items-center">
                    <?php if ($first_ep_link !== '#'): ?>
                    <a href="<?= $first_ep_link ?>" class="flex items-center bg-white text-black hover:bg-gray-200 px-8 py-3.5 rounded-lg font-bold transition-all hover:scale-105 shadow-xl shadow-white/10">
                        <i data-lucide="play" class="w-6 h-6 mr-2 fill-current"></i> Xem Phim
                    </a>
                    <?php else: ?>
                    <button disabled class="flex items-center bg-gray-700 text-gray-400 px-8 py-3.5 rounded-lg font-bold cursor-not-allowed">
                        Đang Cập Nhật
                    </button>
                    <?php endif; ?>
                    <button onclick="document.getElementById('download-app-modal').classList.remove('hidden'); document.getElementById('download-app-modal').classList.add('flex');" class="flex items-center bg-white/10 hover:bg-white/20 backdrop-blur-md text-white px-6 py-3.5 rounded-lg font-medium transition-all hover:scale-105 border border-white/10">
                        <i data-lucide="download" class="w-5 h-5 mr-2"></i> Tải App
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="bg-black min-h-screen text-gray-300 font-sans pb-20 pt-8">
    <div class="w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto">
        <div class="flex flex-col xl:flex-row gap-8">
            
            <!-- Left Column: Main Content -->
            <div class="w-full xl:w-[72%]">
                <!-- Movie Info -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-2">
                        <h1 class="text-2xl md:text-3xl font-bold text-white"><a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($slug) ?>" class="hover:text-red-500 transition-colors"><?= htmlspecialchars($movie['name']) ?></a></h1>
                        <div class="flex items-center gap-1 sm:gap-2">
                            <button onclick="document.getElementById('download-app-modal').classList.remove('hidden'); document.getElementById('download-app-modal').classList.add('flex');" class="flex items-center text-gray-400 hover:text-white transition-colors p-2 rounded-md hover:bg-gray-800">
                                <i data-lucide="download" class="w-4 h-4 mr-1.5"></i> <span class="text-sm hidden sm:inline">Tải phim</span>
                            </button>
                            <button onclick="shareMovie('<?= htmlspecialchars(addslashes($movie['name'])) ?>')" class="flex items-center text-gray-400 hover:text-white transition-colors p-2 rounded-md hover:bg-gray-800">
                                <i data-lucide="share-2" class="w-4 h-4 mr-1.5"></i> <span class="text-sm hidden sm:inline">Chia sẻ</span>
                            </button>
                        </div>
                    </div>
                    <div class="text-lg text-red-500 font-medium mb-3">Tập <?= htmlspecialchars($currentEp['name']) ?></div>
                    
                    <div class="flex flex-wrap items-center gap-2 text-[13px] text-gray-400 mb-5">
                        <?php foreach ($meta_tags as $idx => $tag): ?>
                            <?php if ($idx === 0): ?>
                                <span class="border border-gray-600 px-1.5 py-0.5 rounded text-gray-300"><?= htmlspecialchars($tag) ?></span>
                            <?php else: ?>
                                <span>|</span>
                                <span><?= htmlspecialchars($tag) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Description -->
                    <div class="text-[14px] text-gray-400 leading-relaxed relative bg-gray-900/50 p-4 rounded-lg border border-gray-800">
                        <div id="movie-desc" class="line-clamp-2">
                            <?= !empty($movie['content']) ? strip_tags($movie['content'], '<p><br><b><i>') : 'Chưa có tóm tắt.' ?>
                        </div>
                        <button id="btn-read-more" class="text-red-500 hover:text-red-400 text-sm mt-1 focus:outline-none flex items-center font-medium">Đọc thêm <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i></button>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const desc = document.getElementById('movie-desc');
                        const btn = document.getElementById('btn-read-more');
                        if (desc && btn) {
                            btn.addEventListener('click', function() {
                                if (desc.classList.contains('line-clamp-2')) {
                                    desc.classList.remove('line-clamp-2');
                                    btn.innerHTML = 'Thu gọn <i data-lucide="chevron-up" class="w-4 h-4 ml-1"></i>';
                                    lucide.createIcons();
                                } else {
                                    desc.classList.add('line-clamp-2');
                                    btn.innerHTML = 'Đọc thêm <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>';
                                    lucide.createIcons();
                                }
                            });
                        }
                    });
                </script>
                
                <!-- Suggestions -->
                <?php if (!empty($suggestions)): ?>
                <div class="mb-10">
                    <h3 class="text-xl font-bold text-white mb-4">Đề xuất cho bạn</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach ($suggestions as $item): ?>
                            <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group flex flex-col">
                                <div class="relative w-full aspect-[16/9] overflow-hidden rounded-md bg-gray-800 mb-2 border border-gray-800">
                                    <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute top-1 right-1">
                                        <span class="bg-[#ff4d4f] text-white text-[10px] font-bold px-1.5 py-0.5 rounded"><?= htmlspecialchars($item['quality'] ?? 'HD') ?></span>
                                    </div>
                                    <div class="absolute bottom-1 right-1">
                                        <span class="bg-black/80 text-white text-[11px] px-1.5 py-0.5 rounded"><?= htmlspecialchars($item['episode_current'] ?? 'N/A') ?></span>
                                    </div>
                                </div>
                                <h3 class="text-sm font-medium text-gray-200 line-clamp-1 group-hover:text-red-500 transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Sidebar -->
            <div class="w-full xl:w-[28%] space-y-6">
                
                <?php if (!empty($episodes[0]['server_data'])): ?>
                <!-- Playlist Sidebar -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-800">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-white leading-tight"><?= htmlspecialchars($movie['name']) ?></h3>
                        <p class="text-sm text-gray-500 mt-1"><?= count($episodes[0]['server_data']) ?> Tập</p>
                    </div>
                    
                    <div class="flex items-center gap-4 mb-4 border-b border-gray-800 pb-2">
                        <button class="text-red-500 font-medium text-sm border-b-2 border-[#ff8f00] pb-1">Danh sách phát</button>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-1 max-h-[500px] overflow-y-auto custom-scrollbar pr-1" id="episode-list">
                        <?php foreach ($episodes[0]['server_data'] as $e): 
                            $isActive = $currentEp['slug'] === $e['slug'];
                            $containerClass = $isActive ? "bg-[#2d2f36]" : "hover:bg-[#2d2f36]";
                            $textClass = $isActive ? "text-red-500" : "text-gray-300 group-hover:text-red-500";
                            $imgClass = $isActive ? "opacity-30" : "opacity-30 group-hover:opacity-10";
                            $iconClass = $isActive ? "block text-red-500" : "hidden group-hover:block text-white group-hover:text-red-500";
                        ?>
                            <a href="/<?= $settings["slugWatch"] ?? "xem-phim" ?>/<?= urlencode($slug) ?>/<?= urlencode($e['slug']) ?>" 
                               class="flex items-center justify-between px-3 py-3 rounded transition-colors group <?= $containerClass ?>">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 md:w-16 md:h-10 rounded bg-gray-800 flex items-center justify-center mr-3 transition-colors text-xs font-medium text-gray-500 overflow-hidden relative border border-gray-800">
                                        <img src="<?= htmlspecialchars(!empty($movie['poster_url']) ? $movie['poster_url'] : (!empty($movie['thumb_url']) ? $movie['thumb_url'] : '')) ?>" alt="" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover transition-opacity <?= $imgClass ?>">
                                        <?php if ($isActive): ?>
                                            <div class="absolute inset-0 bg-red-600/30"></div>
                                        <?php endif; ?>
                                        <i data-lucide="play" class="w-4 h-4 relative z-10 <?= $iconClass ?>"></i>
                                    </div>
                                    <?php if ($isActive && isset($startTime) && isset($duration) && $duration > 0 && $startTime > 0): ?>
                                    <div class="absolute bottom-0 left-0 h-1 bg-red-600" style="width: <?= min(100, max(0, ($startTime / $duration) * 100)) ?>%;"></div>
                                    <?php endif; ?>
                                    <?php 
                                        $epName = trim($e['name']);
                                        $displayEpName = (stripos($epName, 'tập') === 0 || stripos($epName, 'tap') === 0 || stripos($epName, 'ep') === 0) ? $epName : 'Tập ' . $epName;
                                    ?>
                                    <span class="text-sm transition-colors font-medium <?= $textClass ?>"><?= htmlspecialchars($displayEpName) ?></span>
                                </div>
                                <span class="bg-black/50 text-[10px] text-gray-400 px-1.5 py-0.5 rounded group-hover:bg-red-600 group-hover:text-black transition-colors">VIP</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Comments Widget -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-800">
                    <h3 class="text-base font-bold text-white mb-3">Bình luận (<span id="comment-count">0</span>)</h3>
                    <div class="bg-gray-800 rounded-md p-3 mb-4">
                        <textarea id="comment-content" rows="2" class="w-full bg-transparent text-white text-sm outline-none resize-none placeholder-gray-500" placeholder="Viết bình luận..."></textarea>
                        <div class="flex items-center justify-end mt-2">
                            <button id="btn-submit-comment" class="bg-red-600 text-black font-bold px-3 py-1.5 rounded text-xs hover:bg-[#e68000] transition-colors">
                                Gửi
                            </button>
                        </div>
                    </div>
                    <div id="comments-list" class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                        <div class="text-center text-gray-500 text-xs py-4">Đang tải bình luận...</div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<script>
// Theatre Mode
let theatreOverlay = document.createElement('div');
theatreOverlay.id = 'theatre-overlay';
theatreOverlay.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:40; display:none; pointer-events:none;';
document.body.appendChild(theatreOverlay);

function toggleTheatreMode() {
    const overlay = document.getElementById('theatre-overlay');
    const playerContainer = document.getElementById('player-container').parentElement;
    if (overlay.style.display === 'none') {
        overlay.style.display = 'block';
        playerContainer.style.position = 'relative';
        playerContainer.style.zIndex = '50';
    } else {
        overlay.style.display = 'none';
        playerContainer.style.position = '';
        playerContainer.style.zIndex = '';
    }
}

// Auto-Play Next
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('video-player');
    if (!video) return;

    let nextEpisodeUrl = null;
    const episodeLinks = document.querySelectorAll('#episode-list a');
    for(let i=0; i<episodeLinks.length - 1; i++) {
        if(episodeLinks[i].classList.contains('bg-[#2d2f36]')) { // current active
            nextEpisodeUrl = episodeLinks[i+1].href;
            break;
        }
    }

    let autoPlayFired = false;
    video.addEventListener('timeupdate', function() {
        if (!autoPlayFired && video.duration > 0 && nextEpisodeUrl) {
            if (video.currentTime / video.duration >= 0.95) {
                autoPlayFired = true;
                let countdownDiv = document.createElement('div');
                countdownDiv.className = 'absolute top-4 right-4 bg-black/80 text-white p-4 rounded-lg z-50 border border-[#ff8f00] shadow-xl';
                countdownDiv.innerHTML = 'Chuyển tập tiếp theo trong <span id="auto-play-counter" class="text-red-500 font-bold text-xl">5</span>s... <button onclick="cancelAutoPlay()" class="ml-3 text-sm text-gray-400 hover:text-white underline">Hủy</button>';
                document.getElementById('player-container').appendChild(countdownDiv);
                
                let count = 5;
                window.autoPlayTimer = setInterval(function() {
                    count--;
                    const counterEl = document.getElementById('auto-play-counter');
                    if(counterEl) counterEl.innerText = count;
                    if (count <= 0) {
                        clearInterval(window.autoPlayTimer);
                        window.location.href = nextEpisodeUrl;
                    }
                }, 1000);
            }
        }
    });

    window.cancelAutoPlay = function() {
        clearInterval(window.autoPlayTimer);
        autoPlayFired = true; // prevent re-triggering
        const counterEl = document.getElementById('auto-play-counter');
        if(counterEl && counterEl.parentNode) counterEl.parentNode.remove();
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Comments logic
    const contentInput = document.getElementById('comment-content');
    const submitBtn = document.getElementById('btn-submit-comment');
    const commentsList = document.getElementById('comments-list');
    const countSpan = document.getElementById('comment-count');
    const movieSlug = '<?= htmlspecialchars($slug) ?>';
    
    function fetchComments() {
        fetch('/api/comments.php?slug=' + movieSlug)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    countSpan.textContent = res.data.length;
                    if (res.data.length === 0) {
                        commentsList.innerHTML = '<div class="text-center text-gray-500 text-xs py-4">Chưa có bình luận.</div>';
                        return;
                    }
                    
                    let html = '';
                    res.data.forEach(c => {
                        html += `
                            <div class="flex gap-3 bg-gray-800 p-3 rounded-lg border border-gray-800">
                                <div class="w-8 h-8 rounded-full bg-gray-900/50 flex items-center justify-center shrink-0 border border-gray-800">
                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-baseline gap-2 mb-1">
                                        <span class="font-bold text-gray-200 text-xs">${c.user_name}</span>
                                        <span class="text-[10px] text-gray-500">${c.time_ago}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 leading-relaxed">${c.content}</p>
                                </div>
                            </div>
                        `;
                    });
                    commentsList.innerHTML = html;
                    lucide.createIcons();
                }
            });
    }
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            const content = contentInput.value.trim();
            if (!content) return alert('Vui lòng nhập nội dung bình luận!');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '...';
            
            fetch('/api/comments.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({slug: movieSlug, name: '', content: content, anonymous: true})
            })
            .then(res => res.json())
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Gửi';
                if (res.success) {
                    contentInput.value = '';
                    fetchComments();
                } else {
                    alert(res.message);
                }
            });
        });
    }
    
    fetchComments();
});
</script>

<!-- History & Watch Party Scripts Include Here -->
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
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                movie_slug: movieSlug,
                movie_name: movieName,
                episode_name: episodeName,
                episode_slug: episodeSlug,
                thumb_url: thumbUrl,
                current_time: currentTime,
                duration: duration
            })
        }).catch(err => console.error(err));
    }

    logHistory();
    setInterval(() => {
        if (document.hidden) return;
        const video = document.getElementById('video-player');
        if (video && !video.paused) logHistory();
    }, 15000);
    window.addEventListener('beforeunload', logHistory);
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) logHistory();
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
        if (!video) return;
        if (cmd === 'play') video.play();
        else if (cmd === 'pause' || cmd === 'stop') video.pause();
    }

    sendHeartbeat();
    setInterval(() => {
        if (document.hidden) return;
        sendHeartbeat();
    }, 10000);
});
</script>

<!-- Watch Party Dialog -->
<div id="watch-party-dialog" class="fixed inset-0 bg-black/95 z-[100] hidden flex items-center justify-center">
    <div class="bg-gray-900/50 border border-gray-800 rounded-xl w-full max-w-md p-6 shadow-2xl relative">
        <button onclick="toggleWatchPartyDialog()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <i data-lucide="users" class="w-6 h-6 mr-2 text-red-500"></i> Phòng Xem Chung
        </h3>
        
        <div id="wp-setup-view">
            <div class="space-y-4">
                <div>
                    <button onclick="createWatchParty()" class="w-full bg-red-600 hover:bg-[#e68000] text-black py-3 rounded-lg font-bold transition-colors flex items-center justify-center">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i> Tạo phòng mới
                    </button>
                    <label class="flex items-center justify-center text-sm text-gray-400 mt-3 cursor-pointer group">
                        <input type="checkbox" id="wp-is-public" class="mr-2 rounded border-gray-700 bg-gray-800 text-red-500 focus:ring-[#ff8f00] focus:ring-offset-gray-900 transition-colors w-4 h-4"> 
                        <span class="group-hover:text-gray-300 transition-colors">Công khai phòng này để mọi người cùng xem</span>
                    </label>
                </div>
                
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-gray-800"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-500 text-sm">Hoặc</span>
                    <div class="flex-grow border-t border-gray-800"></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Nhập mã phòng</label>
                    <div class="flex space-x-2">
                        <input type="text" id="wp-room-input" placeholder="Ví dụ: A1B2C3" class="flex-1 bg-gray-800 border border-gray-800 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-[#ff8f00] uppercase">
                        <button onclick="joinWatchPartyBtn()" class="bg-[#2d2f36] hover:bg-[#3d3f46] text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Vào
                        </button>
                    </div>
                </div>
                
                <div id="wp-public-rooms-container" class="hidden mt-4 pt-4 border-t border-gray-800">
                    <h4 class="text-sm font-medium text-gray-300 mb-3 flex items-center">
                        <i data-lucide="globe" class="w-4 h-4 mr-1.5 text-red-500"></i> Các phòng đang mở
                    </h4>
                    <div id="wp-public-rooms-list" class="space-y-2 max-h-[150px] overflow-y-auto custom-scrollbar">
                    </div>
                </div>
            </div>
        </div>
        
        <div id="wp-active-view" class="hidden text-center">
            <div class="mb-4">
                <span class="text-gray-400 text-sm block mb-1">Mã phòng của bạn:</span>
                <div class="text-3xl font-mono font-bold text-red-500 tracking-wider mb-2" id="wp-room-code-display"></div>
                <p class="text-xs text-gray-500">Gửi mã này hoặc link trang web cho bạn bè để cùng xem.</p>
            </div>
            
            <div class="bg-gray-800 rounded-lg p-3 mb-4 text-left border border-gray-800">
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
                <button onclick="copyWatchPartyLink()" class="flex-1 bg-[#2d2f36] hover:bg-[#3d3f46] text-white py-2 rounded-lg font-medium transition-colors flex items-center justify-center text-sm">
                    <i data-lucide="copy" class="w-4 h-4 mr-1.5"></i> Copy Link
                </button>
                <button onclick="leaveWatchParty()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition-colors flex items-center justify-center text-sm">
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
        if (!wpRoomCode) fetchPublicRooms();
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
                <div class="flex items-center justify-between bg-[#2d2f36] rounded-lg p-3 border border-gray-700">
                    <div>
                        <div class="text-red-500 font-mono font-bold text-sm">${room.room_code}</div>
                        <div class="text-xs text-gray-400">Host: ${room.creator_name} - Tập ${room.episode_name}</div>
                    </div>
                    <button onclick="joinWatchParty('${room.room_code}')" class="px-3 py-1.5 bg-gray-900/50 hover:bg-black text-white text-xs font-medium rounded-md transition-colors border border-gray-800">
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
    .catch(err => console.error(err));
}

function showWpActiveView(code, isHost) {
    document.getElementById('wp-setup-view').classList.add('hidden');
    document.getElementById('wp-active-view').classList.remove('hidden');
    document.getElementById('wp-room-code-display').innerText = code;
    document.getElementById('wp-role-text').innerText = isHost ? 'Chủ phòng' : 'Người xem';
    wpRoomCode = code;
    wpIsHost = isHost;
    
    let badge = document.getElementById('wp-player-badge');
    if (!badge) {
        badge = document.createElement('div');
        badge.id = 'wp-player-badge';
        badge.className = 'absolute top-4 left-4 z-20 bg-black/80 border border-[#ff8f00]/50 text-red-500 px-3 py-1.5 rounded text-xs font-medium flex items-center cursor-pointer hover:bg-black transition-colors';
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
        } else alert('Lỗi: ' + data.message);
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
        } else alert('Lỗi: ' + data.message);
    });
}

function copyWatchPartyLink() {
    const url = new URL(window.location.href);
    url.searchParams.set('party', wpRoomCode);
    navigator.clipboard.writeText(url.toString()).then(() => alert('Đã copy link phòng xem chung!'));
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
    if (!wpVideo) return;
    
    wpSyncInterval = setInterval(() => {
        if (isSyncing) return;
        isSyncing = true;
        
        if (wpIsHost) {
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
                    if (timeDiff > 2) wpVideo.currentTime = r.current_time;
                    
                    if (r.is_playing == 1 && wpVideo.paused) wpVideo.play().catch(e => console.log('Autoplay blocked'));
                    else if (r.is_playing == 0 && !wpVideo.paused) wpVideo.pause();
                }
            })
            .finally(() => { isSyncing = false; });
        }
    }, 2000);
}
</script>



<!-- Download App Modal -->
<div id="download-app-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex-col items-center justify-center p-4 transition-opacity">
    <div class="bg-gray-900/50/90 backdrop-blur-xl border border-white/10 rounded-3xl w-full max-w-sm p-6 shadow-2xl relative text-center mx-4">
        <button onclick="document.getElementById('download-app-modal').classList.add('hidden'); document.getElementById('download-app-modal').classList.remove('flex');" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors bg-white/5 hover:bg-white/10 rounded-full p-2 z-10">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
        <div class="w-16 h-16 bg-red-600/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-[#ff8f00]/20 shadow-[0_0_20px_rgba(255,143,0,0.15)]">
            <i data-lucide="download-cloud" class="w-8 h-8 text-red-500"></i>
        </div>
        <h3 class="text-xl font-bold text-white mb-2 tracking-tight">Tải phim ngoại tuyến</h3>
        <p class="text-sm text-gray-400 mb-6 px-2">Tính năng tải phim siêu tốc độ cao và xem offline không quảng cáo chỉ có trên ứng dụng PhimTop1. Trải nghiệm ngay!</p>
        
        <?php
        $intentUrlModal = "intent://movie/" . urlencode($slug) . "#Intent;scheme=phimtop1;package=com.phimtop1.app;S.browser_fallback_url=" . urlencode($settings['appDownloadUrl'] ?? '') . ";end;";
        ?>
        <div class="flex flex-col gap-3">
            <a href="<?= $intentUrlModal ?>" class="w-full bg-red-600 hover:bg-[#e68000] text-black py-3.5 rounded-xl font-bold transition-all flex items-center justify-center shadow-[0_4px_20px_rgba(255,143,0,0.3)] hover:shadow-[0_4px_25px_rgba(255,143,0,0.4)] hover:-translate-y-0.5">
                <i data-lucide="smartphone" class="w-5 h-5 mr-2"></i> Mở trong Ứng dụng
            </a>
            <a href="<?= htmlspecialchars($settings['appDownloadUrl'] ?? '#') ?>" target="_blank" class="w-full bg-white/5 hover:bg-white/10 text-white py-3.5 rounded-xl font-medium transition-all flex items-center justify-center border border-white/10 hover:border-white/20">
                Tải bản APK
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
