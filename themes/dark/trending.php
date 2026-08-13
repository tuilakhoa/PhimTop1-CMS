<?php
include __DIR__ . '/header.php';
?>
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white border-l-4 border-[#00E359] pl-3">Bảng Xếp Hạng Thịnh Hành</h2>
    </div>
    
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <?php 
        $rank = ($currentPage - 1) * 24;
        foreach ($movies as $movie): 
            $rank++;
            $thumb = !empty($movie['thumb_url']) ? $movie['thumb_url'] : (!empty($movie['poster_url']) ? $movie['poster_url'] : '');
            if (!preg_match('/^http/', $thumb) && $thumb) {
                if (preg_match('/^\/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/i', $thumb)) {
                    $thumb = 'https://image.tmdb.org/t/p/w500' . $thumb;
                } else {
                    $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
                    $thumb = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
                }
            }
            
            // Rank Color
            $rankColor = 'bg-gray-800 text-white';
            if ($rank === 1) $rankColor = 'bg-red-600 text-white';
            else if ($rank === 2) $rankColor = 'bg-orange-500 text-white';
            else if ($rank === 3) $rankColor = 'bg-yellow-500 text-white';
        ?>
            <a href="/<?= $settings['slugMovie'] ?? 'phim' ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-800 transition-all hover:scale-105 hover:shadow-xl hover:shadow-[#00E359]/20">
                <div class="relative aspect-[2/3] w-full overflow-hidden">
                    <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    
                    <!-- Rank Badge -->
                    <div class="absolute top-0 left-0 <?= $rankColor ?> font-black text-xl md:text-2xl px-3 py-1 rounded-br-xl shadow-lg z-10 flex items-center justify-center">
                        #<?= $rank ?>
                    </div>
                    
                    <div class="absolute top-2 right-2 flex flex-col gap-1 z-10">
                        <?php if (!empty($movie['quality'])): ?>
                            <span class="bg-[#00E359] text-black text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($movie['quality']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                        <div class="w-12 h-12 bg-[#00E359]/90 rounded-full flex items-center justify-center transform scale-50 group-hover:scale-100 transition-transform duration-300 shadow-lg">
                            <i data-lucide="play" class="w-6 h-6 text-black fill-current ml-1"></i>
                        </div>
                    </div>
                    
                    <div class="absolute bottom-2 left-2 text-[#00E359] text-xs font-bold z-10 bg-black/60 px-2 py-0.5 rounded backdrop-blur-sm">
                        <i data-lucide="eye" class="w-3 h-3 inline-block mr-1"></i><?= number_format($movie['view'] ?? 0) ?>
                    </div>
                </div>
                
                <div class="p-3 relative z-10 flex flex-col flex-grow">
                    <h3 class="text-sm font-semibold text-white line-clamp-1 mb-1 group-hover:text-[#00E359] transition-colors" title="<?= htmlspecialchars($movie['name']) ?>">
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
        <div class="text-center py-12 text-gray-400">Chưa có dữ liệu lượt xem phim.</div>
    <?php endif; ?>

    <!-- Pagination Simple -->
    <div class="flex justify-center mt-12 gap-2">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-[#00E359] hover:text-black text-white rounded transition-colors font-semibold">Trang trước</a>
        <?php endif; ?>
        <span class="px-4 py-2 text-gray-400 font-medium flex items-center">Trang <?= $currentPage ?> / <?= $totalPages ?></span>
        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-[#00E359] hover:text-black text-white rounded transition-colors font-semibold">Trang sau</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
