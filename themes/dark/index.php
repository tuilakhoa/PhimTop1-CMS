<?php
include __DIR__ . '/header.php';
?>

<div class="bg-[#000000] min-h-screen text-gray-200 font-sans pb-20">
    <!-- Hero Section / Featured (Edge-to-Edge Minimalist) -->
<?php
if (!function_exists('getPhimImgUrl')) {
    function getPhimImgUrl($url) {
        global $data, $settings;
        if (empty($url)) return '';
        if (preg_match('/^http/', $url)) return $url;
        if (preg_match('/^\/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/i', $url)) {
            return 'https://image.tmdb.org/t/p/w500' . $url;
        }
        $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
        return rtrim($domain, '/') . '/' . ltrim($url, '/');
    }
}

$featuredMovies = [];
$featuredType = $settings['featuredType'] ?? 'latest';
$featuredStyle = $settings['featuredStyle'] ?? 'single';
$featuredCount = max(1, (int)($settings['featuredCount'] ?? 5));

if ($featuredType === 'admin') {
    $slugs = explode(',', $settings['featuredMovieSlug'] ?? '');
    foreach ($slugs as $s) {
        $s = trim($s);
        if (!$s) continue;
        if (($settings['displayMode'] ?? 'api') === 'crawl') {
            $m = getMovieRepository()->getMovieBySlug($s);
            if ($m) $featuredMovies[] = $m;
        } else {
            $res = fetchApiMovieDetail($s);
            if ($res && $res['movie']) $featuredMovies[] = $res['movie'];
        }
    }
} elseif ($featuredType === 'view') {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM movies ORDER BY view DESC LIMIT " . $featuredCount);
        $stmt->execute();
        $featuredMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $featuredMovies = array_slice($movies, 0, $featuredCount);
}

if ($featuredStyle === 'single' && count($featuredMovies) > 0) {
    $featuredMovies = [$featuredMovies[0]];
}
if (empty($featuredMovies) && !empty($movies)) {
    $featuredMovies = [$movies[0]];
}
?>

<?php if (!empty($featuredMovies)): ?>
    <div class="relative w-full h-[60vh] md:h-[75vh] mb-12 lg:mb-20">
        <?php if ($featuredStyle === 'slider' && count($featuredMovies) > 1): ?>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
            <div class="swiper swiper-hero w-full h-full">
                <div class="swiper-wrapper">
                    <?php foreach($featuredMovies as $featured): ?>
                        <div class="swiper-slide relative w-full h-full">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10"></div>
                            <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover opacity-90">
                            
                            <div class="absolute inset-0 z-20 flex flex-col justify-center px-6 md:px-16 lg:px-24 max-w-[1400px] mx-auto w-full">
                                <div class="max-w-2xl">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <span class="text-white text-xs font-medium tracking-widest uppercase opacity-70">Nổi Bật</span>
                                        <div class="h-[1px] w-12 bg-white/30"></div>
                                    </div>
                                    <h1 class="text-4xl md:text-5xl lg:text-7xl font-bold text-white mb-6 leading-[1.1] tracking-tight">
                                        <?= htmlspecialchars($featured['name'] ?? '') ?>
                                    </h1>
                                    <?php if (!empty(trim(strip_tags($featured['content'] ?? '')))): ?>
                                    <p class="text-gray-400 text-sm md:text-lg mb-8 max-w-xl line-clamp-3 leading-relaxed font-light">
                                        <?= htmlspecialchars(strip_tags($featured['content'])) ?>
                                    </p>
                                    <?php endif; ?>
                                    <div class="flex flex-wrap items-center gap-4">
                                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="flex items-center justify-center bg-primary text-dark-900 hover:bg-primary-dark px-8 py-3.5 rounded-xl font-bold transition-all shadow-[0_0_20px_rgba(250,204,21,0.3)] active:scale-95">
                                            <i data-lucide="play" class="w-5 h-5 mr-2 fill-current"></i>
                                            Phát ngay
                                        </a>
                                        <button class="flex items-center justify-center bg-white/10 hover:bg-white/20 text-white px-6 py-3.5 rounded-xl font-medium transition-colors backdrop-blur-md border border-white/10 active:scale-95">
                                            <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                                            Chi tiết
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Minimalist Pagination -->
                <div class="swiper-pagination !bottom-8 opacity-70"></div>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swiper !== 'undefined') {
                        new Swiper('.swiper-hero', {
                            slidesPerView: 1,
                            loop: true,
                            autoplay: { delay: 6000, disableOnInteraction: false },
                            effect: 'fade',
                            fadeEffect: { crossFade: true },
                            pagination: { el: '.swiper-hero .swiper-pagination', clickable: true }
                        });
                    }
                });
            </script>
        <?php else: $featured = $featuredMovies[0]; ?>
            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10"></div>
            <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover opacity-90">
            
            <div class="absolute inset-0 z-20 flex flex-col justify-center px-6 md:px-16 lg:px-24 max-w-[1400px] mx-auto w-full">
                <div class="max-w-2xl">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="text-white text-xs font-medium tracking-widest uppercase opacity-70">Nổi Bật</span>
                        <div class="h-[1px] w-12 bg-white/30"></div>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-7xl font-bold text-white mb-6 leading-[1.1] tracking-tight">
                        <?= htmlspecialchars($featured['name'] ?? '') ?>
                    </h1>
                    <?php if (!empty(trim(strip_tags($featured['content'] ?? '')))): ?>
                    <p class="text-gray-400 text-sm md:text-lg mb-8 max-w-xl line-clamp-3 leading-relaxed font-light">
                        <?= htmlspecialchars(strip_tags($featured['content'])) ?>
                    </p>
                    <?php endif; ?>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="flex items-center justify-center bg-primary text-dark-900 hover:bg-primary-dark px-8 py-3.5 rounded-xl font-bold transition-all shadow-[0_0_20px_rgba(250,204,21,0.3)] active:scale-95">
                            <i data-lucide="play" class="w-5 h-5 mr-2 fill-current"></i>
                            Phát ngay
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

    <!-- Main Container -->
    <div class="max-w-[1400px] mx-auto px-6 md:px-12 grid grid-cols-1 lg:grid-cols-4 gap-12 lg:gap-16">
        
        <!-- Left Content: Movies Grid -->
        <div class="lg:col-span-3">
            <?php
            $historyItems = [];
            $enableContinueWatching = isset($settings['enableContinueWatching']) ? (int)$settings['enableContinueWatching'] : 1;
            if ($enableContinueWatching && isset($_SESSION['user'])) {
                $pdo = getPDO();
                if ($pdo) {
                    $stmt = $pdo->prepare("SELECT * FROM watch_history WHERE user_email = ? ORDER BY updated_at DESC LIMIT 5");
                    $stmt->execute([$_SESSION['user']['email']]);
                    $historyItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            ?>
            <?php if (!empty($historyItems)): ?>
            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white tracking-tight flex items-center">
                        <i data-lucide="clock" class="w-6 h-6 mr-2 text-primary"></i> Tiếp tục xem
                    </h2>
                </div>
                <div class="flex overflow-x-auto md:grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 custom-scrollbar pb-4 snap-x snap-mandatory">
                    <?php foreach ($historyItems as $item): 
                        $progress = $item['duration'] > 0 ? min(1, max(0, $item['current_time'] / $item['duration'])) * 100 : 0;
                        // Build direct watch link using episode_slug if available, fallback to movie page
                        $historyLink = !empty($item['episode_slug']) 
                            ? '/' . ($settings["slugWatch"] ?? "xem-phim") . '/' . urlencode($item['movie_slug']) . '/' . urlencode($item['episode_slug'])
                            : '/' . ($settings["slugMovie"] ?? "phim") . '/' . urlencode($item['movie_slug']);
                    ?>
                        <a href="<?= $historyLink ?>" class="group shrink-0 w-64 md:w-auto block snap-start">
                            <div class="relative aspect-video w-full overflow-hidden rounded-xl bg-dark-800 mb-3 border border-white/5 shadow-lg group-hover:border-primary/50 transition-all duration-300">
                                <img src="<?= htmlspecialchars(getPhimImgUrl($item['thumb_url'])) ?>" alt="<?= htmlspecialchars($item['movie_name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-10">
                                    <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(250,204,21,0.5)] transform scale-75 group-hover:scale-100 transition-transform duration-300">
                                        <i data-lucide="play" class="w-6 h-6 text-black fill-current ml-1"></i>
                                    </div>
                                </div>
                                <?php if ($item['duration'] > 0): ?>
                                    <div class="absolute bottom-0 left-0 w-full h-1 bg-white/20 z-20">
                                        <div class="h-full bg-primary shadow-[0_0_10px_rgba(250,204,21,0.8)]" style="width: <?= $progress ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="px-1">
                                <h3 class="text-sm font-medium text-gray-100 line-clamp-1 mb-1 group-hover:text-primary transition-colors"><?= htmlspecialchars($item['movie_name']) ?></h3>
                                <p class="text-xs text-primary/80"><?= htmlspecialchars($item['episode_name']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- AI Recommend Section -->
            <div id="ai-recommend-container" class="mb-12 hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white tracking-tight flex items-center">
                        <i data-lucide="sparkles" class="w-6 h-6 mr-2 text-cyan-400"></i> Dành Riêng Cho Bạn
                    </h2>
                </div>
                <div class="flex overflow-x-auto md:grid md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-5 pb-6 custom-scrollbar snap-x snap-mandatory" id="ai-recommend-list">
                    <!-- JS will populate -->
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                fetch('/api/v1/recommend.php?action=personal&limit=10')
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data && res.data.length > 0) {
                        const list = document.getElementById('ai-recommend-list');
                        const container = document.getElementById('ai-recommend-container');
                        let html = '';
                        res.data.forEach(item => {
                            let thumb = item.thumb_url || item.poster_url;
                            if (thumb && !thumb.startsWith('http')) {
                                thumb = 'https://phimimg.com/' + thumb;
                            }
                            html += `
                                <a href="/phim/${item.slug}" class="group flex flex-col shrink-0 w-36 md:w-auto snap-start">
                                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-dark-800 mb-3 border border-white/5 shadow-lg group-hover:border-primary/50 transition-all duration-300">
                                        <img src="${thumb}" alt="${item.name}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-gradient-to-t from-dark-900 via-transparent to-transparent opacity-80"></div>
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 z-20">
                                            <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(250,204,21,0.5)] transform scale-75 group-hover:scale-100 transition-transform duration-300">
                                                <i data-lucide="play" class="w-6 h-6 text-black fill-current ml-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col px-1">
                                        <h3 class="text-sm font-medium text-gray-200 line-clamp-1 mb-1 group-hover:text-primary transition-colors">${item.name}</h3>
                                        <p class="text-[11px] text-gray-500 line-clamp-1">${item.origin_name || ''}</p>
                                    </div>
                                </a>
                            `;
                        });
                        list.innerHTML = html;
                        container.classList.remove('hidden');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                })
                .catch(err => console.error(err));
            });
            </script>

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white tracking-tight">Mới Cập Nhật</h2>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="text-sm font-medium text-primary hover:text-primary-dark transition-colors flex items-center">
                    Xem tất cả <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex overflow-x-auto md:grid md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-5 pb-6 custom-scrollbar snap-x snap-mandatory">
                <?php foreach ($movies as $movie): 
                    $thumb = !empty($movie['poster_url']) ? $movie['poster_url'] : (!empty($movie['thumb_url']) ? $movie['thumb_url'] : '');
                    if (!preg_match('/^http/', $thumb) && $thumb) {
                        if (preg_match('/^\/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/i', $thumb)) {
                            $thumb = 'https://image.tmdb.org/t/p/w500' . $thumb;
                        } else {
                            $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
                            $thumb = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
                        }
                    }
                ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col shrink-0 w-36 md:w-auto snap-start">
                        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-xl bg-dark-800 mb-3 border border-white/5 shadow-lg group-hover:border-primary/50 transition-all duration-300">
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-dark-900 via-transparent to-transparent opacity-80"></div>
                            
                            <!-- Minimal Overlays -->
                            <div class="absolute top-2 left-2 flex gap-1.5 z-10">
                                <?php if (!empty($movie['quality'])): ?>
                                    <span class="bg-black/70 backdrop-blur-md text-primary text-[10px] font-bold px-2 py-0.5 rounded border border-primary/20"><?= htmlspecialchars($movie['quality']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($movie['episode_current'])): ?>
                                <div class="absolute bottom-2 right-2 bg-primary text-black text-[10px] font-bold px-2 py-1 rounded shadow-lg z-10">
                                    <?= htmlspecialchars($movie['episode_current']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Play button overlay on hover -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 z-20">
                                <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(250,204,21,0.5)] transform scale-75 group-hover:scale-100 transition-transform duration-300">
                                    <i data-lucide="play" class="w-6 h-6 text-black fill-current ml-1"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col px-1">
                            <h3 class="text-sm font-medium text-gray-200 line-clamp-1 mb-1 group-hover:text-primary transition-colors" title="<?= htmlspecialchars($movie['name']) ?>">
                                <?= htmlspecialchars($movie['name']) ?>
                            </h3>
                            <p class="text-[11px] text-gray-500 line-clamp-1">
                                <?= !empty($movie['year']) ? $movie['year'] . ' • ' : '' ?><?= htmlspecialchars($movie['origin_name'] ?? '') ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($movies)): ?>
                <div class="text-center py-20 text-gray-500 border border-gray-900 rounded-2xl">
                    Chưa có dữ liệu phim.
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Content: Minimal Leaderboard -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <h2 class="text-xl font-bold text-white mb-6 tracking-tight">Xếp Hạng</h2>
                
                <!-- Minimal Tabs -->
                <div class="flex border-b border-gray-800 mb-6">
                    <button onclick="switchRankTab('day')" id="tab-day" class="pb-3 px-2 text-sm font-medium text-white border-b-2 border-white mr-4 transition-colors">Ngày</button>
                    <button onclick="switchRankTab('week')" id="tab-week" class="pb-3 px-2 text-sm font-medium text-gray-500 hover:text-gray-300 border-b-2 border-transparent transition-colors mr-4">Tuần</button>
                    <button onclick="switchRankTab('month')" id="tab-month" class="pb-3 px-2 text-sm font-medium text-gray-500 hover:text-gray-300 border-b-2 border-transparent transition-colors">Tháng</button>
                </div>
                
                <?php 
                $rankData = [
                    'day' => array_slice($movies, 0, 10),
                    'week' => array_slice($movies, 5, 10) ?: array_slice($movies, 0, 10),
                    'month' => array_slice($movies, 10, 10) ?: array_slice($movies, 0, 10)
                ];
                foreach ($rankData as $type => $list): 
                ?>
                <div id="rank-<?= $type ?>" class="space-y-5 <?= $type === 'day' ? 'block' : 'hidden' ?>">
                    <?php 
                    $rank = 1;
                    foreach ($list as $item): 
                        $thumb = !empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? '');
                        $rankColor = $rank <= 3 ? 'text-white' : 'text-gray-600';
                        $views = !empty($item['view']) ? $item['view'] : rand(1000, 50000);
                    ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="flex items-center group">
                        <div class="w-6 text-left shrink-0">
                            <span class="text-lg font-bold <?= $rankColor ?>"><?= $rank ?></span>
                        </div>
                        <div class="w-12 h-16 shrink-0 mx-3 rounded bg-[#111] overflow-hidden">
                            <img src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-200 truncate group-hover:text-white transition-colors mb-0.5"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-xs text-gray-500 flex items-center">
                                <?= number_format($views) ?> views
                            </p>
                        </div>
                    </a>
                    <?php $rank++; endforeach; ?>
                </div>
                <?php endforeach; ?>
                
                <script>
                function switchRankTab(type) {
                    ['day', 'week', 'month'].forEach(t => {
                        document.getElementById('rank-' + t).classList.add('hidden');
                        document.getElementById('rank-' + t).classList.remove('block');
                        
                        const tab = document.getElementById('tab-' + t);
                        tab.classList.remove('text-white', 'border-white');
                        tab.classList.add('text-gray-500', 'border-transparent');
                    });
                    
                    document.getElementById('rank-' + type).classList.remove('hidden');
                    document.getElementById('rank-' + type).classList.add('block');
                    
                    const activeTab = document.getElementById('tab-' + type);
                    activeTab.classList.remove('text-gray-500', 'border-transparent');
                    activeTab.classList.add('text-white', 'border-white');
                }
                </script>
                
                <button class="w-full mt-8 py-3 rounded-lg bg-[#111] hover:bg-[#1a1a1a] text-sm font-medium text-gray-300 transition-colors">
                    Xem toàn bộ
                </button>
            </div>
        </div>
        
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
