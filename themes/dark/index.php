<?php
include __DIR__ . '/header.php';
?>

<div class="container mx-auto px-4">
    <!-- Hero Section / Featured -->
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
    <?php if ($featuredStyle === 'slider' && count($featuredMovies) > 1): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
        <div class="swiper swiper-hero w-full h-[50vh] md:h-[60vh] rounded-2xl overflow-hidden mb-12 shadow-2xl relative">
            <div class="swiper-wrapper">
                <?php foreach($featuredMovies as $featured): ?>
                    <div class="swiper-slide relative w-full h-full">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-transparent z-10"></div>
                        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover">
                        
                        <div class="absolute inset-0 z-20 flex flex-col justify-center px-8 md:px-16 lg:px-24 max-w-4xl">
                            <span class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full w-fit mb-4 uppercase tracking-wider">Phim Nổi Bật</span>
                            <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 leading-tight drop-shadow-lg">
                                <?= htmlspecialchars($featured['name'] ?? '') ?>
                            </h1>
                            <?php if (!empty(trim(strip_tags($featured['content'] ?? '')))): ?>
                            <p class="text-gray-300 text-lg md:text-xl mb-8 max-w-2xl line-clamp-3">
                                <?= htmlspecialchars(strip_tags($featured['content'])) ?>
                            </p>
                            <?php endif; ?>
                            <div class="flex flex-wrap items-center gap-4">
                                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3.5 rounded-full font-semibold transition-all transform hover:scale-105 shadow-lg shadow-red-600/30">
                                    <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                                    <span>Xem Ngay</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Pagination & Navigation -->
            <div class="swiper-pagination !bottom-4"></div>
            <div class="swiper-button-prev !text-white/50 hover:!text-white after:!text-2xl transition-colors hidden md:flex"></div>
            <div class="swiper-button-next !text-white/50 hover:!text-white after:!text-2xl transition-colors hidden md:flex"></div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swiper !== 'undefined') {
                    new Swiper('.swiper-hero', {
                        slidesPerView: 1,
                        loop: true,
                        autoplay: { delay: 5000, disableOnInteraction: false },
                        effect: 'fade',
                        fadeEffect: { crossFade: true },
                        pagination: { el: '.swiper-hero .swiper-pagination', clickable: true },
                        navigation: { nextEl: '.swiper-hero .swiper-button-next', prevEl: '.swiper-hero .swiper-button-prev' }
                    });
                }
            });
        </script>
    <?php else: $featured = $featuredMovies[0]; ?>
        <div class="relative w-full h-[50vh] md:h-[60vh] rounded-2xl overflow-hidden mb-12 shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-transparent z-10"></div>
            <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['thumb_url']) ? $featured['thumb_url'] : ($featured['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover">
            
            <div class="absolute inset-0 z-20 flex flex-col justify-center px-8 md:px-16 lg:px-24 max-w-4xl">
                <span class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full w-fit mb-4 uppercase tracking-wider">Phim Nổi Bật</span>
                <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 leading-tight drop-shadow-lg">
                    <?= htmlspecialchars($featured['name'] ?? '') ?>
                </h1>
                <?php if (!empty(trim(strip_tags($featured['content'] ?? '')))): ?>
                <p class="text-gray-300 text-lg md:text-xl mb-8 max-w-2xl line-clamp-3">
                    <?= htmlspecialchars(strip_tags($featured['content'])) ?>
                </p>
                <?php endif; ?>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3.5 rounded-full font-semibold transition-all transform hover:scale-105 shadow-lg shadow-red-600/30">
                        <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                        <span>Xem Ngay</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

    <!-- Phim Mới Cập Nhật -->
    <div id="new-movies" class="mb-12">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-white border-l-4 border-red-500 pl-3">Phim Mới Cập Nhật</h2>
            <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center">
                Xem tất cả <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
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
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-800 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-500/20">
                    <div class="relative aspect-[2/3] w-full overflow-hidden">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                            <?php if (!empty($movie['quality'])): ?>
                                <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($movie['quality']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($movie['lang'])): ?>
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($movie['lang']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-12 h-12 bg-red-600/90 rounded-full flex items-center justify-center transform scale-50 group-hover:scale-100 transition-transform duration-300 shadow-lg">
                                <i data-lucide="play" class="w-6 h-6 text-white fill-current ml-1"></i>
                            </div>
                        </div>
                        
                        <?php if (!empty($movie['episode_current'])): ?>
                            <div class="absolute bottom-2 right-2 bg-black/80 backdrop-blur-sm text-white text-xs font-semibold px-2 py-1 rounded border border-white/10">
                                <?= htmlspecialchars($movie['episode_current']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-3 relative z-10 flex flex-col flex-grow">
                        <h3 class="text-sm font-semibold text-white line-clamp-1 mb-1 group-hover:text-red-400 transition-colors" title="<?= htmlspecialchars($movie['name']) ?>">
                            <?= htmlspecialchars($movie['name']) ?>
                        </h3>
                        <p class="text-xs text-gray-400 line-clamp-1">
                            <?= htmlspecialchars($movie['origin_name'] ?? '') ?> 
                            <?= !empty($movie['year']) ? '(' . $movie['year'] . ')' : '' ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($movies)): ?>
            <div class="text-center py-12 text-gray-400">Không có phim nào để hiển thị.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
