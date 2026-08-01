<?php
include __DIR__ . '/header.php';
?>

<div class="container mx-auto px-4">
    <!-- Hero Section / Featured (just a banner) -->
    <div class="relative w-full h-[50vh] md:h-[60vh] rounded-2xl overflow-hidden mb-12 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-transparent z-10"></div>
        <img src="https://images.unsplash.com/photo-1626814026160-2237a95fc5a0?q=80&w=2070&auto=format&fit=crop" 
             alt="Featured" class="absolute inset-0 w-full h-full object-cover">
        
        <div class="absolute inset-0 z-20 flex flex-col justify-center px-8 md:px-16 lg:px-24 max-w-4xl">
            <span class="px-3 py-1 bg-red-600 text-gray-900 text-xs font-bold rounded-full w-fit mb-4 uppercase tracking-wider">Phim Mới Nổi Bật</span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 mb-4 leading-tight drop-shadow-lg">
                Khám Phá Thế Giới <span class="text-red-500">Điện Ảnh</span> Bất Tận
            </h1>
            <p class="text-gray-700 text-lg md:text-xl mb-8 max-w-2xl line-clamp-3">
                Thưởng thức những bộ phim bom tấn đỉnh cao với chất lượng tuyệt vời nhất. Cập nhật liên tục mỗi ngày.
            </p>
            <div class="flex flex-wrap items-center gap-4">
                <a href="#new-movies" class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-gray-900 px-8 py-3.5 rounded-full font-semibold transition-all transform hover:scale-105 shadow-lg shadow-red-600/30">
                    <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                    <span>Xem Ngay</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Phim Mới Cập Nhật -->
    <div id="new-movies" class="mb-12">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-gray-900 border-l-4 border-red-500 pl-3">Phim Mới Cập Nhật</h2>
            <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-le" class="text-gray-600 hover:text-gray-900 transition-colors text-sm flex items-center">
                Xem tất cả <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach ($movies as $movie): 
                $thumb = !empty($movie['thumb_url']) ? $movie['thumb_url'] : (!empty($movie['poster_url']) ? $movie['poster_url'] : '');
                if (!preg_match('/^http/', $thumb) && $thumb) {
                    if (preg_match('/^\/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/i', $thumb)) {
                        $thumb = 'https://image.tmdb.org/t/p/w500' . $thumb;
                    } else {
                        $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
                        $thumb = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
                    }
                }
            ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-white transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-500/20">
                    <div class="relative aspect-[2/3] w-full overflow-hidden">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                            <?php if (!empty($movie['quality'])): ?>
                                <span class="bg-red-600 text-gray-900 text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($movie['quality']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($movie['lang'])): ?>
                                <span class="bg-blue-600 text-gray-900 text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($movie['lang']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-12 h-12 bg-red-600/90 rounded-full flex items-center justify-center transform scale-50 group-hover:scale-100 transition-transform duration-300 shadow-lg">
                                <i data-lucide="play" class="w-6 h-6 text-gray-900 fill-current ml-1"></i>
                            </div>
                        </div>
                        
                        <?php if (!empty($movie['episode_current'])): ?>
                            <div class="absolute bottom-2 right-2 bg-black/80 backdrop-blur-sm text-gray-900 text-xs font-semibold px-2 py-1 rounded border border-white/10">
                                <?= htmlspecialchars($movie['episode_current']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-3 relative z-10 flex flex-col flex-grow">
                        <h3 class="text-sm font-semibold text-gray-900 line-clamp-1 mb-1 group-hover:text-red-400 transition-colors" title="<?= htmlspecialchars($movie['name']) ?>">
                            <?= htmlspecialchars($movie['name']) ?>
                        </h3>
                        <p class="text-xs text-gray-600 line-clamp-1">
                            <?= htmlspecialchars($movie['origin_name'] ?? '') ?> 
                            <?= !empty($movie['year']) ? '(' . $movie['year'] . ')' : '' ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($movies)): ?>
            <div class="text-center py-12 text-gray-600">Không có phim nào để hiển thị.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
