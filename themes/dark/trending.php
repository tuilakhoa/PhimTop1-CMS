<?php
include __DIR__ . '/header.php';
?>
<div class="bg-[#000000] min-h-screen text-gray-200 font-sans pb-20">
    <div class="w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto pt-8 lg:pt-12">
        <div class="flex items-center justify-between mb-8 border-b border-gray-900 pb-4">
            <h2 class="text-2xl font-bold text-white tracking-tight">Bảng Xếp Hạng Thịnh Hành</h2>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-5 gap-y-10">
            <?php 
            $rank = ($currentPage - 1) * 24;
            foreach ($movies as $movie): 
                $rank++;
                $thumb = !empty($movie['poster_url']) ? $movie['poster_url'] : (!empty($movie['thumb_url']) ? $movie['thumb_url'] : '');
                if (!preg_match('/^http/', $thumb) && $thumb) {
                    if (preg_match('/^\/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/i', $thumb)) {
                        $thumb = 'https://image.tmdb.org/t/p/w500' . $thumb;
                    } else {
                        $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
                        $thumb = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
                    }
                }
                
                // Rank Color
                $rankColor = 'text-gray-400';
                if ($rank === 1) $rankColor = 'text-white';
                else if ($rank === 2) $rankColor = 'text-gray-200';
                else if ($rank === 3) $rankColor = 'text-gray-300';
            ?>
                <a href="/<?= $settings['slugMovie'] ?? 'phim' ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col">
                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-100"></div>
                        
                        <div class="absolute top-2 left-2 flex gap-1.5">
                            <span class="bg-black/70 backdrop-blur-md <?= $rank <= 3 ? 'text-white' : 'text-gray-300' ?> text-xs font-bold px-2 py-0.5 rounded">
                                #<?= $rank ?>
                            </span>
                            <?php if (!empty($movie['quality'])): ?>
                                <span class="bg-black/70 backdrop-blur-md text-white text-[10px] font-medium px-2 py-0.5 rounded"><?= htmlspecialchars($movie['quality']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="absolute bottom-2 left-2 right-2 flex justify-between items-center text-gray-300 text-[10px] font-medium">
                            <span><i data-lucide="eye" class="w-3 h-3 inline mr-1"></i><?= number_format($movie['view'] ?? 0) ?></span>
                        </div>
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
                Chưa có dữ liệu lượt xem phim.
            </div>
        <?php endif; ?>

        <!-- Pagination Simple -->
        <div class="flex justify-center mt-12 gap-2">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-[#111] border border-gray-900 hover:bg-white hover:text-black hover:border-white text-gray-300 rounded font-medium transition-colors">Trang trước</a>
            <?php endif; ?>
            <span class="px-4 py-2 text-gray-500 font-medium flex items-center">Trang <?= $currentPage ?> / <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-[#111] border border-gray-900 hover:bg-white hover:text-black hover:border-white text-gray-300 rounded font-medium transition-colors">Trang sau</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
