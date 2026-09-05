<?php 
include __DIR__ . '/header.php'; 
?>

<!-- 1. Hero Slider Section -->
<?php 
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

// Fetch additional lists for Homepage (Optimized to use existing $movies array)
$phimLeData = array_values(array_filter($movies, function($m) { return isset($m['type']) && ($m['type'] === 'single' || $m['type'] === 'phim-le'); }));
$phimBoData = array_values(array_filter($movies, function($m) { return isset($m['type']) && ($m['type'] === 'series' || $m['type'] === 'phim-bo'); }));
$hoatHinhData = array_values(array_filter($movies, function($m) { return isset($m['type']) && ($m['type'] === 'hoathinh' || $m['type'] === 'hoat-hinh'); }));

$homeSliders = [
    [
        'title' => 'Phim Lẻ Mới',
        'url' => '/' . ($settings["slugList"] ?? "danh-sach") . '/phim-le',
        'data' => $phimLeData
    ],
    [
        'title' => 'Phim Bộ Mới',
        'url' => '/' . ($settings["slugList"] ?? "danh-sach") . '/phim-bo',
        'data' => $phimBoData
    ],
    [
        'title' => 'Hoạt Hình',
        'url' => '/' . ($settings["slugList"] ?? "danh-sach") . '/hoat-hinh',
        'data' => $hoatHinhData
    ]
];

$trungQuocData = array_values(array_filter($movies, function($m) { return stripos(json_encode($m['country'] ?? ''), 'Trung Quốc') !== false || stripos(json_encode($m['country'] ?? ''), 'trung-quoc') !== false; }));
$hanQuocData = array_values(array_filter($movies, function($m) { return stripos(json_encode($m['country'] ?? ''), 'Hàn Quốc') !== false || stripos(json_encode($m['country'] ?? ''), 'han-quoc') !== false; }));
$auMyData = array_values(array_filter($movies, function($m) { return stripos(json_encode($m['country'] ?? ''), 'Âu Mỹ') !== false || stripos(json_encode($m['country'] ?? ''), 'au-my') !== false; }));
?>

<?php if (!empty($featuredMovies)): ?>
<div class="relative w-full h-[60vh] md:h-[90vh] overflow-hidden -mt-[72px] bg-[#0a0a0a] group">
    <?php if ($featuredStyle === 'slider' && count($featuredMovies) > 1): ?>
        <div class="swiper swiper-hero w-full h-full">
            <div class="swiper-wrapper">
                <?php foreach($featuredMovies as $featured): ?>
                <div class="swiper-slide relative w-full h-full">
                    <div class="absolute inset-0 bg-black animate-pulse"></div>
                    <img fetchpriority="high" src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['poster_url']) ? $featured['poster_url'] : ($featured['thumb_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? 'Banner') ?>" class="w-full h-full object-cover opacity-90 transition-transform duration-[10s] hover:scale-105">
                    
                    <!-- Advanced Gradients for Cinematic Feel -->
                    <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0a] via-[#0a0a0a]/80 to-transparent w-full md:w-[70%]"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/40 to-transparent"></div>
                    <div class="absolute top-0 inset-x-0 h-32 bg-gradient-to-b from-black/60 to-transparent"></div>
                    
                    <div class="absolute inset-0 flex flex-col justify-center px-4 md:px-12 lg:px-24 max-w-[1600px] mx-auto z-10 pt-24 pb-12">
                        <div class="max-w-3xl transform transition-all duration-700 translate-y-0 opacity-100">
                            
                            <!-- Metadata tags (Year, Rating, Quality) -->
                            <div class="flex flex-wrap items-center gap-3 mb-4 md:mb-6 text-xs md:text-sm font-semibold tracking-wider">
                                <?php if (!empty($featured['year'])): ?>
                                    <span class="text-white/80 bg-white/10 backdrop-blur-md px-3 py-1 rounded-full border border-white/10"><?= htmlspecialchars($featured['year']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($featured['quality'])): ?>
                                    <span class="text-phim-yellow bg-phim-yellow/10 backdrop-blur-md px-3 py-1 rounded-full border border-phim-yellow/20 uppercase shadow-[0_0_10px_rgba(234,179,8,0.2)]"><?= htmlspecialchars($featured['quality']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($featured['episode_current'])): ?>
                                    <span class="text-white bg-red-600/80 backdrop-blur-md px-3 py-1 rounded-full shadow-lg"><?= htmlspecialchars($featured['episode_current']) ?></span>
                                <?php endif; ?>
                            </div>

                            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white mb-4 leading-tight tracking-tight drop-shadow-2xl line-clamp-2 md:line-clamp-3">
                                <?= htmlspecialchars($featured['name'] ?? '') ?>
                            </h1>
                            
                            <?php if (!empty($featured['origin_name'])): ?>
                                <h2 class="text-lg md:text-xl text-gray-400 font-medium mb-4 italic font-serif">
                                    <?= htmlspecialchars($featured['origin_name']) ?>
                                </h2>
                            <?php endif; ?>

                            <?php if (!empty(trim(strip_tags($featured['content'] ?? '')))): ?>
                                <p class="text-gray-300/90 text-sm md:text-lg mb-8 md:mb-10 line-clamp-3 md:line-clamp-4 leading-relaxed max-w-2xl font-light text-shadow-sm">
                                    <?= htmlspecialchars(strip_tags($featured['content'])) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="flex flex-wrap items-center gap-4">
                                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="group relative inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-black bg-phim-yellow rounded-full overflow-hidden transition-all hover:scale-105 hover:shadow-[0_0_30px_rgba(234,179,8,0.5)] focus:outline-none focus:ring-4 focus:ring-yellow-400/50">
                                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                                    <i data-lucide="play" class="w-6 h-6 mr-2 fill-current"></i>
                                    Xem Phim Ngay
                                </a>
                                
                                <button class="group inline-flex items-center justify-center px-6 py-3.5 text-base font-bold text-white bg-white/10 backdrop-blur-md border border-white/20 rounded-full transition-all hover:bg-white/20 hover:border-white/40 hover:scale-105">
                                    <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                                    Chi Tiết
                                </button>

                                <button class="w-12 h-12 bg-white/5 hover:bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md border border-white/10 transition-all hover:scale-110 hover:text-red-500 group">
                                    <i data-lucide="heart" class="w-5 h-5 text-white/70 group-hover:text-red-500 transition-colors"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Modern Navigation & Pagination -->
            <div class="absolute bottom-0 left-0 w-full z-50">
                <div class="h-1.5 w-full bg-white/10">
                    <div class="hero-progress-line h-full bg-gradient-to-r from-phim-yellow to-yellow-300 w-0 transition-all duration-75 ease-linear shadow-[0_0_10px_rgba(234,179,8,0.5)]"></div>
                </div>
            </div>
            
            <div class="absolute bottom-8 right-8 md:right-16 lg:right-24 z-50 flex items-center gap-4">
                <div class="swiper-button-prev !static !w-12 !h-12 !mt-0 bg-black/40 backdrop-blur-md border border-white/20 rounded-full !text-white hover:bg-white hover:!text-black transition-all flex items-center justify-center after:!text-sm after:!font-bold"></div>
                <div class="swiper-pagination !static !w-auto space-x-2"></div>
                <div class="swiper-button-next !static !w-12 !h-12 !mt-0 bg-black/40 backdrop-blur-md border border-white/20 rounded-full !text-white hover:bg-white hover:!text-black transition-all flex items-center justify-center after:!text-sm after:!font-bold"></div>
            </div>
        </div>
    <?php else: $featured = $featuredMovies[0]; ?>
        <div class="absolute inset-0 bg-black animate-pulse"></div>
        <img fetchpriority="high" src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['poster_url']) ? $featured['poster_url'] : ($featured['thumb_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($featured['name'] ?? 'Banner') ?>" class="w-full h-full object-cover opacity-90 transition-transform duration-[10s] hover:scale-105">
        
        <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0a] via-[#0a0a0a]/80 to-transparent w-full md:w-[70%]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/40 to-transparent"></div>
        <div class="absolute top-0 inset-x-0 h-32 bg-gradient-to-b from-black/60 to-transparent"></div>
        
        <div class="absolute inset-0 flex flex-col justify-center px-4 md:px-12 lg:px-24 max-w-[1600px] mx-auto z-10 pt-24 pb-12">
            <div class="max-w-3xl transform transition-all duration-700 translate-y-0 opacity-100">
                
                <div class="flex flex-wrap items-center gap-3 mb-4 md:mb-6 text-xs md:text-sm font-semibold tracking-wider">
                    <?php if (!empty($featured['year'])): ?>
                        <span class="text-white/80 bg-white/10 backdrop-blur-md px-3 py-1 rounded-full border border-white/10"><?= htmlspecialchars($featured['year']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($featured['quality'])): ?>
                        <span class="text-phim-yellow bg-phim-yellow/10 backdrop-blur-md px-3 py-1 rounded-full border border-phim-yellow/20 uppercase shadow-[0_0_10px_rgba(234,179,8,0.2)]"><?= htmlspecialchars($featured['quality']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($featured['episode_current'])): ?>
                        <span class="text-white bg-red-600/80 backdrop-blur-md px-3 py-1 rounded-full shadow-lg"><?= htmlspecialchars($featured['episode_current']) ?></span>
                    <?php endif; ?>
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white mb-4 leading-tight tracking-tight drop-shadow-2xl line-clamp-2 md:line-clamp-3">
                    <?= htmlspecialchars($featured['name'] ?? '') ?>
                </h1>
                
                <?php if (!empty($featured['origin_name'])): ?>
                    <h2 class="text-lg md:text-xl text-gray-400 font-medium mb-4 italic font-serif">
                        <?= htmlspecialchars($featured['origin_name']) ?>
                    </h2>
                <?php endif; ?>
                
                <?php if (!empty(trim(strip_tags($featured['content'] ?? '')))): ?>
                    <p class="text-gray-300/90 text-sm md:text-lg mb-8 md:mb-10 line-clamp-3 md:line-clamp-4 leading-relaxed max-w-2xl font-light text-shadow-sm">
                        <?= htmlspecialchars(strip_tags($featured['content'])) ?>
                    </p>
                <?php endif; ?>
                
                <div class="flex flex-wrap items-center gap-4">
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="group relative inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-black bg-phim-yellow rounded-full overflow-hidden transition-all hover:scale-105 hover:shadow-[0_0_30px_rgba(234,179,8,0.5)] focus:outline-none focus:ring-4 focus:ring-yellow-400/50">
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                        <i data-lucide="play" class="w-6 h-6 mr-2 fill-current"></i>
                        Xem Phim Ngay
                    </a>
                    
                    <button class="group inline-flex items-center justify-center px-6 py-3.5 text-base font-bold text-white bg-white/10 backdrop-blur-md border border-white/20 rounded-full transition-all hover:bg-white/20 hover:border-white/40 hover:scale-105">
                        <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                        Chi Tiết
                    </button>

                    <button class="w-12 h-12 bg-white/5 hover:bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md border border-white/10 transition-all hover:scale-110 hover:text-red-500 group">
                        <i data-lucide="heart" class="w-5 h-5 text-white/70 group-hover:text-red-500 transition-colors"></i>
                    </button>
                </div>
            </div>
            
            <div class="absolute right-8 md:right-16 bottom-16 hidden lg:flex space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <?php foreach (array_slice($movies, 1, 3) as $m): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($m['slug']) ?>" class="w-32 h-20 rounded-lg overflow-hidden border-2 border-white/20 cursor-pointer hover:border-phim-yellow transition-all transform hover:-translate-y-2 hover:shadow-[0_10px_20px_rgba(0,0,0,0.5)]">
                        <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($m['poster_url']) ? $m['poster_url'] : ($m['thumb_url'] ?? ''))) ?>" class="w-full h-full object-cover">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="absolute right-8 md:right-12 top-24 md:top-32 flex items-center space-x-4 z-20 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
        <button class="w-12 h-12 rounded-full border border-white/20 flex items-center justify-center hover:bg-white/20 transition-all bg-black/40 backdrop-blur-md hover:scale-110">
            <i data-lucide="volume-2" class="w-5 h-5 text-white"></i>
        </button>
    </div>
</div>
<?php endif; ?>

<div class="px-3 sm:px-4 md:px-12 lg:px-20 max-w-[1920px] mx-auto py-8 md:py-12 bg-black relative z-20 space-y-16 md:space-y-24">

    <!-- Section: Tiếp Tục Xem (Local History) -->
    <?php $enableContinueWatching = isset($settings['enableContinueWatching']) ? (int)$settings['enableContinueWatching'] : 1; ?>
    <?php if ($enableContinueWatching): ?>
    <section id="continue-watching-section" class="hidden">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i data-lucide="history" class="w-6 h-6 mr-2 text-phim-yellow"></i> Tiếp Tục Xem
            </h2>
        </div>
        
        <div class="swiper swiper-history">
            <div class="swiper-wrapper pb-4" id="continue-watching-list">
                <!-- JS will populate this -->
            </div>
            <div class="swiper-button-prev hidden md:flex"></div>
            <div class="swiper-button-next hidden md:flex"></div>
        </div>
    </section>

    <?php endif; ?>

    <!-- Section: Phim Dành Riêng Cho Bạn (AI Gợi Ý) -->
    <section id="ai-recommend-section">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i data-lucide="sparkles" class="w-6 h-6 mr-2 text-cyan-400"></i> Dành Riêng Cho Bạn
            </h2>
        </div>
        
        <div class="swiper swiper-recommend">
            <div class="swiper-wrapper pb-4" id="ai-recommend-list">
                <!-- Skeleton Loader to prevent layout shift -->
                <?php for($i=0; $i<8; $i++): ?>
                <div class="swiper-slide w-48 md:w-52">
                    <div class="aspect-[2/3] bg-[#1a1a1a] rounded-lg animate-pulse mb-3 border border-gray-800"></div>
                    <div class="h-4 bg-[#1a1a1a] rounded animate-pulse w-3/4 mb-2"></div>
                    <div class="h-3 bg-[#1a1a1a] rounded animate-pulse w-1/2"></div>
                </div>
                <?php endfor; ?>
            </div>
            <div class="swiper-button-prev hidden md:flex"></div>
            <div class="swiper-button-next hidden md:flex"></div>
        </div>
    </section>


    <!-- 2. Mới Nhất Trên <?= htmlspecialchars($siteName) ?> (16:9 Swiper) -->
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Mới Nhất Trên <?= htmlspecialchars($siteName) ?></h2>
            <button class="w-8 h-8 rounded-full border border-gray-600 flex items-center justify-center text-gray-400 hover:text-white hover:border-white transition-colors">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="swiper swiper-horizontal">
            <div class="swiper-wrapper pb-4">
                <?php foreach (array_slice($movies, 0, 8) as $item): ?>
                    <div class="swiper-slide w-72 md:w-80">
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-2">
                            <!-- 16:9 Aspect Ratio Image -->
                            <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-xl border border-white/5 shadow-lg">
                                <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" loading="lazy"
                                     class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                                
                                <!-- Top Left Yellow Tag -->
                                <?php if (!empty($item['episode_current'])): ?>
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm">
                                            <?= htmlspecialchars($item['episode_current'] ?? '') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-3">
                                <h3 class="text-white font-medium text-sm md:text-base truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                                <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Navigation -->
            <div class="swiper-button-prev hidden md:flex"></div>
            <div class="swiper-button-next hidden md:flex"></div>
        </div>
    </section>


    <!-- 3. Bạn đang quan tâm gì? (Genre Blocks) -->
    <section>
        <h2 class="text-2xl font-bold text-white mb-6">Bạn đang quan tâm gì?</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php 
            $colors = [
                'from-blue-500 to-blue-700',
                'from-cyan-400 to-cyan-600',
                'from-purple-500 to-purple-700',
                'from-green-500 to-green-700',
                'from-red-500 to-red-700',
                'from-orange-500 to-orange-700',
                'from-pink-500 to-pink-700',
                'from-teal-500 to-teal-700',
                'from-indigo-500 to-indigo-700'
            ];
            $genreSlugList = $settings["slugGenre"] ?? "the-loai";
            $displayGenres = array_slice($genres, 0, 9);
            $remainingCount = max(0, count($genres) - count($displayGenres));
            
            foreach ($displayGenres as $index => $genre): 
                $color = $colors[$index % count($colors)];
            ?>
            <a href="/<?= $genreSlugList ?>/<?= htmlspecialchars($genre['slug']) ?>" class="h-28 rounded-xl bg-gradient-to-br <?= $color ?> flex items-center justify-center text-white font-bold text-lg overflow-hidden relative group hover:scale-[1.02] transition-transform text-center px-2">
                <?= htmlspecialchars($genre['name']) ?>
                <div class="absolute -bottom-6 w-full h-12 bg-white/10 blur-xl group-hover:h-20 transition-all"></div>
            </a>
            <?php endforeach; ?>
            
            <button onclick="document.querySelector('.xl\\:hidden')?.click() || document.querySelector('.group button')?.click()" class="h-28 rounded-xl bg-[#2a2a2a] hover:bg-[#333] flex items-center justify-center text-white font-bold text-lg border border-gray-800 transition-colors">
                +<?= $remainingCount ?> thể loại
            </button>
        </div>
    </section>

    
    <!-- KHỐI PHIM QUỐC GIA (NỔI BẬT, ÂU MỸ, TRUNG QUỐC, HÀN QUỐC) - Gom lại cho gần nhau -->
    <div class="space-y-12 lg:space-y-16">
    
        <!-- 4. Phim Nổi Bật -->
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-phim-yellow uppercase mt-1">Nổi Bật</h3>
                </div>
                <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-moi" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($movies, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-xl border border-white/5 shadow-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>


        <!-- Phim ÂU MỸ Section -->
        <?php if (!empty($auMyData)): ?>
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-cyan-400 uppercase mt-1">ÂU MỸ</h3>
                </div>
                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/au-my" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($auMyData, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-xl border border-white/5 shadow-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm shadow-md"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-cyan-400 transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <?php endif; ?>

        <!-- Phim TRUNG QUỐC Section -->
        <?php if (!empty($trungQuocData)): ?>
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-red-500 uppercase mt-1">TRUNG QUỐC</h3>
                </div>
                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/trung-quoc" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($trungQuocData, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-xl border border-white/5 shadow-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm shadow-md"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-red-500 transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <?php endif; ?>

        <!-- Phim HÀN QUỐC Section -->
        <?php if (!empty($hanQuocData)): ?>
        <section class="flex flex-col lg:flex-row gap-4 lg:gap-8 items-start">
            <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center pt-2 lg:pt-8">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-none">Phim</h2>
                    <h3 class="text-2xl lg:text-3xl font-black text-[#5b61f4] uppercase mt-1">HÀN QUỐC</h3>
                </div>
                <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/han-quoc" class="text-sm lg:text-base text-gray-500 hover:text-white flex items-center mt-2 lg:mt-6 transition-colors">
                    Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="flex-1 min-w-0 w-full grid grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($hanQuocData, 0, 4) as $item): ?>
                    <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                        <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-xl border border-white/5 shadow-lg">
                            <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                            <?php if (!empty($item['episode_current'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm shadow-md"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <h3 class="text-white font-medium text-sm lg:text-base truncate group-hover:text-[#5b61f4] transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                            <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <?php endif; ?>
        
    </div>

    <!-- 5. Bảng Xếp Hạng (Leaderboard) -->
    <section class="bg-gradient-to-b from-[#0a0a0a] to-[#000000] rounded-3xl p-6 md:p-10 lg:p-12 border border-gray-900 shadow-2xl relative overflow-hidden">
                
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 relative z-10 gap-6">
            <h2 class="text-3xl md:text-4xl font-black text-white flex items-center tracking-tight">
                <i data-lucide="bar-chart-2" class="w-8 h-8 mr-3 text-phim-yellow"></i> Bảng Xếp Hạng
            </h2>
            
            <!-- Tabs -->
            <div class="flex bg-[#141414] p-1.5 rounded-xl border border-gray-800 shadow-inner" id="leaderboard-tabs">
                <button onclick="switchRankTab('day')" class="rank-tab-btn px-6 py-2.5 rounded-lg bg-gray-800 text-white font-bold shadow-md text-sm transition-colors" data-tab="day">Ngày</button>
                <button onclick="switchRankTab('week')" class="rank-tab-btn px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm" data-tab="week">Tuần</button>
                <button onclick="switchRankTab('month')" class="rank-tab-btn px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm" data-tab="month">Tháng</button>
            </div>
        </div>
        
        <div class="relative z-10" id="leaderboard-content">
            <?php 
            $rankPeriods = ['day', 'week', 'month'];
            foreach ($rankPeriods as $period):
                // Shuffle movies deterministically for each period so they look different but remain consistent
                $periodMovies = $movies;
                if ($period === 'week') {
                    $periodMovies = array_reverse($movies);
                } elseif ($period === 'month') {
                    $keys = array_keys($periodMovies);
                    shuffle($keys);
                    $new = [];
                    foreach($keys as $key) { $new[$key] = $periodMovies[$key]; }
                    $periodMovies = $new;
                }
                
                $rankCategories = [
                    ['title' => 'Top Phim Lẻ', 'data' => array_slice($periodMovies, 0, 5)],
                    ['title' => 'Top Phim Bộ', 'data' => array_slice($periodMovies, 5, 5) ?: array_slice($periodMovies, 0, 5)],
                    ['title' => 'Top Hoạt Hình', 'data' => array_slice($periodMovies, 10, 5) ?: array_slice($periodMovies, 0, 5)]
                ];
            ?>
            <div id="rank-<?= $period ?>" class="rank-content-box grid grid-cols-1 lg:grid-cols-3 gap-10 lg:gap-16 <?= $period === 'day' ? 'block' : 'hidden' ?>">
                <?php foreach ($rankCategories as $catIdx => $category): ?>
                <div class="space-y-8">
                    <h3 class="text-2xl font-bold text-gray-100 flex items-center">
                        <span class="w-1.5 h-6 bg-phim-yellow rounded-full mr-3"></span>
                        <?= $category['title'] ?>
                    </h3>
                    <div class="space-y-6">
                        <?php 
                        $rank = 1;
                        foreach ($category['data'] as $item): 
                            $thumb = !empty($item['thumb_url']) ? $item['thumb_url'] : (!empty($item['poster_url']) ? $item['poster_url'] : '');
                            $rankColor = $rank === 1 ? 'text-yellow-400 [text-shadow:0_0_12px_rgba(250,204,21,0.8)]' : 
                                        ($rank === 2 ? 'text-gray-300 [text-shadow:0_0_12px_rgba(209,213,219,0.7)]' : 
                                        ($rank === 3 ? 'text-amber-600 [text-shadow:0_0_12px_rgba(217,119,6,0.6)]' : 'text-gray-600'));
                            $rankBg = $rank === 1 ? 'bg-yellow-400/10 border-yellow-400/30' : 
                                     ($rank === 2 ? 'bg-gray-300/10 border-gray-300/30' : 
                                     ($rank === 3 ? 'bg-amber-600/10 border-amber-600/30' : 'bg-transparent border-transparent'));
                        ?>
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="flex items-center gap-4 group cursor-pointer p-2 rounded-xl transition-all hover:bg-gray-800/50">
                            <div class="w-8 flex-shrink-0 text-center font-black text-4xl italic tracking-tighter <?= $rankColor ?>">
                                <?= $rank ?>
                            </div>
                            <div class="w-16 h-20 flex-shrink-0 rounded-lg overflow-hidden border <?= $rankBg ?> shadow-lg">
                                <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-white font-bold text-base truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h4>
                                <p class="text-gray-500 text-xs truncate mt-1"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                                <div class="flex items-center mt-2 space-x-3 text-[10px] font-medium">
                                    <span class="text-gray-400 flex items-center bg-gray-800 px-2 py-0.5 rounded">
                                        <i data-lucide="eye" class="w-3 h-3 mr-1"></i> <?= number_format(rand(1000, 99999)) ?>
                                    </span>
                                    <span class="text-phim-yellow flex items-center bg-phim-yellow/10 px-2 py-0.5 rounded">
                                        <i data-lucide="star" class="w-3 h-3 mr-1"></i> <?= number_format(rand(70, 99)/10, 1) ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                        <?php $rank++; endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>


    <!-- 6. Danh Sách Phim Chiếu Rạp (3:4 Posters + Bottom tags) -->
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-white">Danh Sách Phim Chiếu Rạp Hôm Nay</h2>
            <button class="w-8 h-8 rounded-full border border-gray-600 flex items-center justify-center text-gray-400 hover:text-white hover:border-white transition-colors">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="swiper swiper-vertical-posters">
            <div class="swiper-wrapper pb-4">
                <?php foreach (array_slice($movies, 0, 8) as $item): 
                    $thumb = !empty($item['thumb_url']) ? $item['thumb_url'] : (!empty($item['poster_url']) ? $item['poster_url'] : '');
                ?>
                    <div class="swiper-slide w-48 md:w-52">
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative cursor-pointer">
                            <div class="aspect-[2/3] relative overflow-hidden rounded-xl border border-white/5 shadow-lg">
                                <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors"></div>
                                
                                <!-- Top Right Vietsub -->
                                <div class="absolute top-2 right-2">
                                    <span class="bg-phim-yellow text-black text-[10px] font-bold px-2 py-1 rounded-sm shadow-md">
                                        <?= htmlspecialchars($item['lang'] ?? 'Vietsub') ?>
                                    </span>
                                </div>
                                
                                <!-- Bottom Tags (Tập mới, Hot) -->
                                <div class="absolute bottom-2 left-0 right-0 flex justify-center space-x-1">
                                    <?php if (!empty($item['episode_current'])): ?>
                                        <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded-sm"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                    <?php endif; ?>
                                    <span class="bg-orange-600 text-white text-[10px] font-bold px-2 py-1 rounded-sm">Hot</span>
                                </div>
                            </div>
                            
                            <div class="mt-3 flex justify-between items-start">
                                <div class="flex-1 min-w-0 pr-2">
                                    <h3 class="text-white font-bold text-sm truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                                    <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                                </div>
                                <div class="shrink-0 bg-gray-800 text-gray-400 text-[10px] px-1.5 py-0.5 rounded mt-0.5">
                                    <?= htmlspecialchars($item['year'] ?? date('Y')) ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-prev hidden md:flex"></div>
            <div class="swiper-button-next hidden md:flex"></div>
        </div>
    </section>


    <!-- 7. Dynamic Sliders from Settings/API -->
    <?php foreach ($homeSliders as $sliderIdx => $slider): ?>
        <?php if (!empty($slider['data'])): ?>
        <section>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-white"><?= htmlspecialchars($slider['title']) ?></h2>
                <a href="<?= htmlspecialchars($slider['url']) ?>" class="text-sm text-gray-500 hover:text-white flex items-center">
                    Xem tất cả <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
            
            <div class="swiper swiper-vertical-posters">
                <div class="swiper-wrapper pb-4">
                    <?php foreach ($slider['data'] as $item): 
                        $thumb = !empty($item['thumb_url']) ? $item['thumb_url'] : (!empty($item['poster_url']) ? $item['poster_url'] : '');
                    ?>
                        <div class="swiper-slide w-48 md:w-52">
                            <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative cursor-pointer">
                                <div class="aspect-[2/3] relative overflow-hidden rounded-xl border border-white/5 shadow-lg">
                                    <img loading="lazy" src="<?= htmlspecialchars(getPhimImgUrl($thumb)) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors"></div>
                                    
                                    <!-- Top Right Vietsub -->
                                    <div class="absolute top-2 right-2">
                                        <span class="bg-phim-yellow text-black text-[10px] font-bold px-2 py-1 rounded-sm shadow-md">
                                            <?= htmlspecialchars($item['quality'] ?? 'HD') ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Bottom Tags (Tập mới, Hot) -->
                                    <div class="absolute bottom-2 left-0 right-0 flex justify-center space-x-1">
                                        <?php if (!empty($item['episode_current'])): ?>
                                            <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded-sm"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mt-3 flex justify-between items-start">
                                    <div class="flex-1 min-w-0 pr-2">
                                        <h3 class="text-white font-bold text-sm truncate group-hover:text-phim-yellow transition-colors"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                                        <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                                    </div>
                                    <div class="shrink-0 bg-gray-800 text-gray-400 text-[10px] px-1.5 py-0.5 rounded mt-0.5">
                                        <?= htmlspecialchars($item['year'] ?? date('Y')) ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev hidden md:flex"></div>
                <div class="swiper-button-next hidden md:flex"></div>
            </div>
        </section>

        <?php endif; ?>
    <?php endforeach; ?>

</div>

<!-- Swiper JS -->
<script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script defer src="/themes/phimhayok/assets/js/home.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/footer.php'; ?>


<script>
    function switchRankTab(tab) {
        // Update buttons
        document.querySelectorAll('.rank-tab-btn').forEach(btn => {
            if (btn.dataset.tab === tab) {
                btn.className = 'rank-tab-btn px-6 py-2.5 rounded-lg bg-gray-800 text-white font-bold shadow-md text-sm transition-colors';
            } else {
                btn.className = 'rank-tab-btn px-6 py-2.5 rounded-lg text-gray-500 hover:text-white font-medium transition-colors text-sm';
            }
        });
        // Update content
        document.querySelectorAll('.rank-content-box').forEach(box => {
            if (box.id === 'rank-' + tab) {
                box.classList.remove('hidden');
                box.classList.add('block');
            } else {
                box.classList.remove('block');
                box.classList.add('hidden');
            }
        });
    }
</script>
