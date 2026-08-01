<?php 
include __DIR__ . '/header.php'; 
?>

<!-- 1. Hero Slider Section -->
<?php if (!empty($movies)): $featured = $movies[0]; ?>
<div class="relative w-full h-[60vh] md:h-[85vh] overflow-hidden -mt-[72px]">
    <!-- Placeholder for actual slider. Just showing one static slide like the screenshot for now -->
    <div class="absolute inset-0">
        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['poster_url']) ? $featured['poster_url'] : ($featured['thumb_url'] ?? ''))) ?>" alt="Banner" class="w-full h-full object-cover">
        <!-- Overlay gradients to make text readable -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-80"></div>
    </div>
    
    <div class="absolute inset-0 flex flex-col justify-center px-4 md:px-12 lg:px-20 max-w-[1400px] mx-auto z-10 pt-20">
        <div class="max-w-2xl">
            <!-- Title with custom elegant styling based on screenshot -->
            <h1 class="text-4xl md:text-7xl font-serif text-white mb-6 leading-tight drop-shadow-xl" style="font-family: 'Playfair Display', serif;">
                <?= htmlspecialchars($featured['name'] ?? '') ?>
            </h1>
            
            <p class="text-gray-300 text-base md:text-lg mb-8 line-clamp-3 leading-relaxed max-w-xl">
                <?= htmlspecialchars(strip_tags(!empty($featured['content']) ? $featured['content'] : 'Nội dung đang cập nhật...')) ?>
            </p>
            
            <div class="flex items-center space-x-4">
                <!-- Big Yellow Play Button -->
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="w-16 h-16 md:w-20 md:h-20 bg-phim-yellow hover:bg-yellow-400 rounded-full flex items-center justify-center transition-transform hover:scale-105 shadow-[0_0_20px_rgba(234,179,8,0.4)]">
                    <i data-lucide="play" class="w-8 h-8 md:w-10 md:h-10 text-black ml-2"></i>
                </a>
                
                <button class="w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/20 transition-colors">
                    <i data-lucide="heart" class="w-5 h-5 text-white"></i>
                </button>
                
                <button class="w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/20 transition-colors">
                    <i data-lucide="info" class="w-5 h-5 text-white"></i>
                </button>
            </div>
        </div>
        
        <!-- Right side controls (Volume) -->
        <div class="absolute right-8 md:right-12 bottom-32 md:bottom-40 flex items-center space-x-4">
            <button class="w-12 h-12 rounded-full border border-gray-400 flex items-center justify-center hover:bg-white/10 transition-colors bg-black/40 backdrop-blur-sm">
                <i data-lucide="volume-x" class="w-5 h-5 text-white"></i>
            </button>
        </div>
        
        <!-- Small thumbnails at bottom right -->
        <div class="absolute right-8 md:right-12 bottom-12 flex space-x-2">
            <?php foreach (array_slice($movies, 1, 4) as $m): ?>
                <div class="w-24 h-14 rounded-md overflow-hidden border border-white/30 cursor-pointer hover:border-white transition-colors opacity-70 hover:opacity-100">
                    <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($m['poster_url']) ? $m['poster_url'] : ($m['thumb_url'] ?? ''))) ?>" class="w-full h-full object-cover">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="px-4 md:px-12 lg:px-20 max-w-[1920px] mx-auto py-8 bg-black relative z-20 space-y-16">

    <!-- 2. Mới Nhất Trên Phimhayok (16:9 Swiper) -->
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Mới Nhất Trên Phimhayok</h2>
            <button class="w-8 h-8 rounded-full border border-gray-600 flex items-center justify-center text-gray-400 hover:text-white hover:border-white transition-colors">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="swiper swiper-horizontal">
            <div class="swiper-wrapper pb-4">
                <?php foreach (array_slice($movies, 0, 8) as $item): ?>
                    <div class="swiper-slide w-[280px] md:w-[320px]">
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-2">
                            <!-- 16:9 Aspect Ratio Image -->
                            <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                                <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['poster_url']) ? $item['poster_url'] : ($item['thumb_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" loading="lazy"
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
            <a href="#" class="h-28 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-lg overflow-hidden relative group hover:scale-[1.02] transition-transform">
                Hành Động
                <div class="absolute -bottom-6 w-full h-12 bg-white/10 blur-xl group-hover:h-20 transition-all"></div>
            </a>
            <a href="#" class="h-28 rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 flex items-center justify-center text-white font-bold text-lg overflow-hidden relative group hover:scale-[1.02] transition-transform">
                Tâm Lý
            </a>
            <a href="#" class="h-28 rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white font-bold text-lg overflow-hidden relative group hover:scale-[1.02] transition-transform">
                Hài Hước
            </a>
            <a href="#" class="h-28 rounded-xl bg-gradient-to-br from-green-500 to-green-700 flex items-center justify-center text-white font-bold text-lg overflow-hidden relative group hover:scale-[1.02] transition-transform">
                Kinh Dị
            </a>
            <a href="#" class="h-28 rounded-xl bg-[#2a2a2a] hover:bg-[#333] flex items-center justify-center text-white font-bold text-lg border border-gray-800 transition-colors">
                +35 thể loại
            </a>
        </div>
    </section>
    
    <!-- 4. Phim Âu Mỹ (Mixed layout) -->
    <section class="flex flex-col lg:flex-row gap-8">
        <!-- Title area -->
        <div class="w-full lg:w-48 shrink-0 flex lg:flex-col justify-between lg:justify-center lg:-mt-12">
            <div>
                <h2 class="text-3xl font-bold text-white leading-none">Phim</h2>
                <h3 class="text-2xl font-black text-cyan-400 uppercase mt-1">Âu Mỹ</h3>
            </div>
            <a href="/<?= $settings["slugCountry"] ?? "quoc-gia" ?>/au-my" class="text-sm text-gray-500 hover:text-white flex items-center mt-4">
                Xem toàn bộ <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>
        
        <!-- Grid -->
        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach (array_slice($movies, 0, 4) as $item): ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative rounded-lg overflow-hidden cursor-pointer transition-transform duration-300 hover:-translate-y-1">
                    <div class="aspect-video relative overflow-hidden bg-gray-900 rounded-lg">
                        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['poster_url']) ? $item['poster_url'] : ($item['thumb_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" loading="lazy" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                        <?php if (!empty($item['episode_current'])): ?>
                            <div class="absolute top-2 left-2">
                                <span class="bg-phim-yellow text-black text-[11px] font-bold px-2 py-0.5 rounded-sm"><?= htmlspecialchars($item['episode_current'] ?? '') ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2">
                        <h3 class="text-white font-medium text-sm truncate"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                        <p class="text-gray-500 text-xs truncate mt-0.5"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 5. Top 10 Thịnh Hành (Vertical Posters + Big Numbers) -->
    <section>
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-white flex items-center">
                <span class="text-red-500 mr-2 text-2xl">🔥</span> Top 10 Thịnh Hành Tại Việt Nam Hôm Nay
            </h2>
            <div class="flex space-x-2">
                <button class="w-8 h-8 rounded bg-[#1a1a1a] flex items-center justify-center text-white hover:bg-gray-800"><i data-lucide="chevron-left" class="w-5 h-5"></i></button>
                <button class="w-8 h-8 rounded bg-[#1a1a1a] flex items-center justify-center text-white hover:bg-gray-800"><i data-lucide="chevron-right" class="w-5 h-5"></i></button>
            </div>
        </div>
        
        <div class="swiper swiper-top10">
            <div class="swiper-wrapper pb-12 pt-4 pl-4">
                <?php 
                $rank = 1;
                foreach (array_slice($movies, 0, 10) as $item): 
                ?>
                    <div class="swiper-slide w-[180px] md:w-[220px]">
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block relative group">
                            <div class="aspect-[2/3] relative rounded-lg overflow-hidden ml-8">
                                <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                
                                <!-- Small Tags at bottom center of poster -->
                                <div class="absolute bottom-3 left-0 right-0 flex justify-center space-x-2">
                                    <span class="bg-gray-900 text-gray-300 text-[10px] font-bold px-1.5 py-0.5 rounded">T13</span>
                                    <span class="bg-gray-900 text-gray-300 text-[10px] font-bold px-1.5 py-0.5 rounded"><?= htmlspecialchars($item['year'] ?? date('Y')) ?></span>
                                </div>
                            </div>
                            
                            <!-- Huge Number -->
                            <div class="absolute -left-6 bottom-4 md:-left-8 md:bottom-8 text-8xl md:text-[140px] font-black italic text-stroke drop-shadow-xl z-10 select-none group-hover:text-white transition-colors duration-300" style="-webkit-text-stroke: 4px #404040; color: #000;">
                                <?= $rank ?>
                            </div>
                            
                            <!-- Title below -->
                            <div class="mt-4 ml-8 text-center">
                                <h3 class="text-white font-bold text-sm truncate"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                                <p class="text-gray-500 text-xs truncate"><?= htmlspecialchars($item['origin_name'] ?? '') ?></p>
                            </div>
                        </a>
                    </div>
                <?php $rank++; endforeach; ?>
            </div>
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
                <?php foreach (array_slice($movies, 0, 8) as $item): ?>
                    <div class="swiper-slide w-[180px] md:w-[200px]">
                        <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="block group relative cursor-pointer">
                            <div class="aspect-[2/3] relative overflow-hidden rounded-lg">
                                <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['thumb_url']) ? $item['thumb_url'] : ($item['poster_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name'] ?? '') ?>" loading="lazy"
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

</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script src="/themes/phimhayok/assets/js/home.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/footer.php'; ?>

