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
        
            $res = fetchApiMovieDetail($s);
            if ($res && $res['movie']) $featuredMovies[] = $res['movie'];
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

// Set preload image for LCP
if (!empty($featuredMovies)) {
    $featured = $featuredMovies[0];
    $preloadImage = getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''));
}

include __DIR__ . '/header.php';
?>

<div class="bg-[#000000] min-h-screen text-gray-200 font-sans pb-20">
    <!-- Hero Section / Featured (Edge-to-Edge Minimalist) -->


<?php if (!empty($featuredMovies)): ?>
    <div class="relative w-full h-[60vh] md:h-[75vh] mb-12 lg:mb-20">
        <?php if ($featuredStyle === 'slider' && count($featuredMovies) > 1): ?>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
.swiper-button-next, .swiper-button-prev {
    color: white !important;
    background: rgba(0,0,0,0.6);
    border-radius: 50%;
    width: 36px !important;
    height: 36px !important;
    transition: all 0.3s ease;
    backdrop-filter: blur(4px);
}
.swiper-button-next:after, .swiper-button-prev:after {
    font-size: 16px !important;
    font-weight: 800;
}
.swiper-button-next:hover, .swiper-button-prev:hover {
    background: rgba(220, 38, 38, 0.9);
    transform: scale(1.1);
}
.swiper-button-disabled {
    opacity: 0 !important;
}
</style>

            <div class="swiper swiper-hero w-full h-full">
                <div class="swiper-wrapper">
                    <?php $slideIndex = 0; foreach($featuredMovies as $featured): ?>
                        <div class="swiper-slide relative w-full h-full">
                            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10"></div>
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" <?= $slideIndex === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-90">
                            
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
                    <?php $slideIndex++; endforeach; ?>
                </div>

                <div class="swiper-pagination !bottom-8 opacity-70"></div>
            </div>
            
            <script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
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

                        });
                    }
                });
            </script>
        <?php else: $featured = $featuredMovies[0]; ?>
            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10"></div>
            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" fetchpriority="high" decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-90">
            
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
    <div class="w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto flex flex-col gap-12 lg:gap-16">
        
        <!-- Main Content -->
        <div class="w-full">
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
                <div class="swiper swiper-list w-full pb-4" >
<div class="swiper-wrapper">
                    <?php foreach ($historyItems as $item): 
                        $progress = $item['duration'] > 0 ? min(1, max(0, $item['current_time'] / $item['duration'])) * 100 : 0;
                        // Build direct watch link using episode_slug if available, fallback to movie page
                        $historyLink = !empty($item['episode_slug']) 
                            ? '/' . ($settings["slugWatch"] ?? "xem-phim") . '/' . urlencode($item['movie_slug']) . '/' . urlencode($item['episode_slug'])
                            : '/' . ($settings["slugMovie"] ?? "phim") . '/' . urlencode($item['movie_slug']);
                    ?>
                        <a href="<?= $historyLink ?>" class="swiper-slide group shrink-0 w-[200px] sm:w-[240px] md:w-[280px] block">
                            <div class="relative aspect-video w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                                <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($item['thumb_url'])) ?>" alt="<?= htmlspecialchars($item['movie_name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
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
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
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
                <div class="swiper swiper-list w-full pb-4" id="ai-recommend-container-swiper">
<div class="swiper-wrapper" id="ai-recommend-list">
                    <!-- Skeleton Loader to prevent layout shift -->
                    <?php for($i=0; $i<6; $i++): ?>
                        <div class="swiper-slide group shrink-0 w-[130px] sm:w-[150px] md:w-[180px] block">
                            <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] animate-pulse mb-3 border border-gray-800"></div>
                            <div class="h-4 bg-[#111] animate-pulse rounded w-3/4 mb-1"></div>
                            <div class="h-3 bg-[#111] animate-pulse rounded w-1/2"></div>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
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
                            let thumb = item.poster_url || item.thumb_url;
                            if (thumb && !thumb.startsWith('http')) {
                                thumb = 'https://phimimg.com/' + thumb;
                            }
                            html += `
                                <a href="/phim/${item.slug}" class="swiper-slide group shrink-0 w-[130px] sm:w-[150px] md:w-[180px] block">
                                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                                        <img loading="lazy" src="${thumb}" alt="${item.name}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
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
                        if (typeof Swiper !== 'undefined') {
                            new Swiper('#ai-recommend-container-swiper', {
                                slidesPerView: 'auto',
                                spaceBetween: 16,
                                freeMode: true,
                                observer: true,
                                observeParents: true,
                                navigation: {
                                    nextEl: '#ai-recommend-container-swiper .swiper-button-next',
                                    prevEl: '#ai-recommend-container-swiper .swiper-button-prev',
                                },
                            });
                        }
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
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7 gap-x-5 gap-y-10">
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
                            <img loading="lazy" src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            
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

        <!-- Ranking Section: Horizontal Layout -->
        <div class="w-full mb-12">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 border-b border-gray-800 pb-2">
                <h2 class="text-2xl font-bold text-white tracking-tight mb-4 sm:mb-0">Bảng Xếp Hạng</h2>
                
                <!-- Minimal Tabs -->
                <div class="flex">
                    <button onclick="switchRankTab('day')" id="tab-day" class="pb-2 px-3 text-sm font-medium text-white border-b-2 border-white transition-colors">Ngày</button>
                    <button onclick="switchRankTab('week')" id="tab-week" class="pb-2 px-3 text-sm font-medium text-gray-500 hover:text-gray-300 border-b-2 border-transparent transition-colors">Tuần</button>
                    <button onclick="switchRankTab('month')" id="tab-month" class="pb-2 px-3 text-sm font-medium text-gray-500 hover:text-gray-300 border-b-2 border-transparent transition-colors">Tháng</button>
                </div>
            </div>
            
            <?php 
            $rankData = [
                'day' => array_slice($movies, 0, 10),
                'week' => array_slice($movies, 5, 10) ?: array_slice($movies, 0, 10),
                'month' => array_slice($movies, 10, 10) ?: array_slice($movies, 0, 10)
            ];
            foreach ($rankData as $type => $list): 
            ?>
            <div id="rank-<?= $type ?>" class="<?= $type === 'day' ? 'block' : 'hidden' ?>">
                <div class="swiper swiper-list w-full pb-6" >
<div class="swiper-wrapper">
                    <?php 
                    $rank = 1;
                    foreach ($list as $item): 
                        $thumb = !empty($item['poster_url']) ? $item['poster_url'] : (!empty($item['thumb_url']) ? $item['thumb_url'] : '');
                        $rankColor = $rank <= 3 ? 'text-red-500' : 'text-gray-500';
                        $views = !empty($item['view']) ? $item['view'] : rand(1000, 50000);
                    ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="swiper-slide group shrink-0 w-[130px] sm:w-[150px] md:w-[180px] block relative">
                        <div class="absolute -left-3 -bottom-4 text-6xl md:text-8xl font-black <?= $rankColor ?> opacity-80 z-20" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8); -webkit-text-stroke: 1px #fff;"><?= $rank ?></div>
                        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-2 z-10 ml-4">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <i data-lucide="play-circle" class="w-10 h-10 text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-100 line-clamp-1 group-hover:text-white"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-xs text-gray-500"><?= number_format($views) ?> views</p>
                        </div>
                    </a>
                    <?php $rank++; endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div><div class="swiper-button-next"></div>
            </div>
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
        </div>
        
    </div>
</div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swiper !== 'undefined') {
                        document.querySelectorAll('.swiper-list').forEach(function(el) {
                            new Swiper(el, {
                                slidesPerView: 'auto',
                                spaceBetween: 16,
                                freeMode: true,
                                observer: true,
                                observeParents: true,
                                navigation: {
                                    nextEl: el.querySelector('.swiper-button-next'),
                                    prevEl: el.querySelector('.swiper-button-prev'),
                                },
                            });
                        });
                    }
                });
            </script>

<?php include __DIR__ . '/footer.php'; ?>
