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
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
            <div class="swiper swiper-hero w-full h-full">
                <div class="swiper-wrapper">
                    <?php foreach($featuredMovies as $featured): ?>
                        <div class="swiper-slide relative w-full h-full">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10"></div>
                            <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-90">
                            
                            <div class="absolute inset-0 z-20 flex flex-col justify-center px-6 md:px-16 lg:px-24 w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto w-full">
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
                                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="flex items-center justify-center bg-white text-black hover:bg-gray-200 px-8 py-3.5 rounded-lg font-medium transition-colors">
                                            <i data-lucide="play" class="w-5 h-5 mr-2 fill-current"></i>
                                            Phát ngay
                                        </a>
                                        <button class="flex items-center justify-center bg-white/10 hover:bg-white/20 text-white px-6 py-3.5 rounded-lg font-medium transition-colors">
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
                <div class="absolute bottom-0 left-0 w-full h-1 bg-white/10 z-50">
                    <div class="hero-progress-line h-full bg-white w-0 transition-all duration-75 ease-linear"></div>
                </div>
                <div class="swiper-pagination !bottom-8 opacity-70"></div>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swiper !== 'undefined') {
                        new Swiper('.swiper-hero', {
                            slidesPerView: 1,
                            loop: true,
                            autoplay: { delay: 6000, disableOnInteraction: false },
                            effect: 'creative',
                            creativeEffect: {
                                prev: { shadow: true, translate: ['-20%', 0, -1] },
                                next: { translate: ['100%', 0, 0] }
                            },
                            pagination: { el: '.swiper-hero .swiper-pagination', clickable: true },
                            on: {
                                autoplayTimeLeft(s, time, progress) {
                                    const progressLine = document.querySelector('.hero-progress-line');
                                    if(progressLine) {
                                        progressLine.style.width = ((1 - progress) * 100) + '%';
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
        <?php else: $featured = $featuredMovies[0]; ?>
            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10"></div>
            <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-90">
            
            <div class="absolute inset-0 z-20 flex flex-col justify-center px-6 md:px-16 lg:px-24 w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto w-full">
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
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="flex items-center justify-center bg-white text-black hover:bg-gray-200 px-8 py-3.5 rounded-lg font-medium transition-colors">
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
    <div class="w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto grid grid-cols-1 lg:grid-cols-4 gap-12 lg:gap-16">
        
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
                        <i data-lucide="clock" class="w-6 h-6 mr-2 text-red-500"></i> Tiếp tục xem
                    </h2>
                </div>
                <div class="flex overflow-x-auto gap-4 custom-scrollbar pb-4">
                    <?php foreach ($historyItems as $item): 
                        $progress = $item['duration'] > 0 ? min(1, max(0, $item['current_time'] / $item['duration'])) * 100 : 0;
                        // Build direct watch link using episode_slug if available, fallback to movie page
                        $historyLink = !empty($item['episode_slug']) 
                            ? '/' . ($settings["slugWatch"] ?? "xem-phim") . '/' . urlencode($item['movie_slug']) . '/' . urlencode($item['episode_slug'])
                            : '/' . ($settings["slugMovie"] ?? "phim") . '/' . urlencode($item['movie_slug']);
                    ?>
                        <a href="<?= $historyLink ?>" class="group shrink-0 w-64 block">
                            <div class="relative aspect-video w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                                <img src="<?= htmlspecialchars(getPhimImgUrl($item['thumb_url'])) ?>" alt="<?= htmlspecialchars($item['movie_name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <i data-lucide="play-circle" class="w-12 h-12 text-white"></i>
                                </div>
                                <?php if ($item['duration'] > 0): ?>
                                    <div class="absolute bottom-0 left-0 w-full h-1 bg-white/20">
                                        <div class="h-full bg-red-600" style="width: <?= $progress ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-sm font-medium text-gray-100 line-clamp-1 mb-1 group-hover:text-white"><?= htmlspecialchars($item['movie_name']) ?></h3>
                            <p class="text-xs text-red-500"><?= htmlspecialchars($item['episode_name']) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- AI Recommend Section -->
            <div id="ai-recommend-container" class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white tracking-tight flex items-center">
                        <i data-lucide="sparkles" class="w-6 h-6 mr-2 text-cyan-400"></i> Dành Riêng Cho Bạn
                    </h2>
                </div>
                <div class="flex overflow-x-auto gap-4 custom-scrollbar pb-4" id="ai-recommend-list">
                    <!-- Skeleton Loader to prevent layout shift -->
                    <?php for($i=0; $i<6; $i++): ?>
                        <div class="group shrink-0 w-40 sm:w-48 block">
                            <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] animate-pulse mb-3 border border-gray-800"></div>
                            <div class="h-4 bg-[#111] animate-pulse rounded w-3/4 mb-1"></div>
                            <div class="h-3 bg-[#111] animate-pulse rounded w-1/2"></div>
                        </div>
                    <?php endfor; ?>
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
                                <a href="/phim/${item.slug}" class="group shrink-0 w-40 sm:w-48 block">
                                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                                        <img src="${thumb}" alt="${item.name}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <i data-lucide="play-circle" class="w-10 h-10 text-white"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-sm font-medium text-gray-100 line-clamp-1 mb-1 group-hover:text-white">${item.name}</h3>
                                    <p class="text-xs text-gray-500 line-clamp-1">${item.origin_name || ''}</p>
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

            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-white tracking-tight">Mới Cập Nhật</h2>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="text-sm font-medium text-gray-500 hover:text-white transition-colors">
                    Xem tất cả
                </a>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-x-5 gap-y-10">
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
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col">
                        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            
                            <!-- Minimal Overlays -->
                            <div class="absolute top-2 left-2 flex gap-1.5">
                                <?php if (!empty($movie['quality'])): ?>
                                    <span class="bg-black/80 text-white text-[10px] font-medium px-2 py-0.5 rounded"><?= htmlspecialchars($movie['quality']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($movie['episode_current'])): ?>
                                <div class="absolute bottom-2 right-2 bg-black/90 text-white text-[10px] font-medium px-2 py-1 rounded">
                                    <?= htmlspecialchars($movie['episode_current']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex flex-col">
                            <h3 class="text-sm font-medium text-gray-100 line-clamp-1 mb-1 group-hover:text-white transition-colors" title="<?= htmlspecialchars($movie['name']) ?>">
                                <?= htmlspecialchars($movie['name']) ?>
                            </h3>
                            <p class="text-xs text-gray-500 line-clamp-1">
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
                            <img src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
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
