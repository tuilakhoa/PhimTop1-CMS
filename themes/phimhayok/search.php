<?php include __DIR__ . '/header.php'; ?>

<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1400px] py-8">
    <div class="flex flex-col md:flex-row items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-white flex items-center mb-4 md:mb-0">
            <i data-lucide="search" class="w-6 h-6 mr-3 text-red-600"></i>
            Kết quả tìm kiếm cho: "<span class="text-red-500"><?= htmlspecialchars($keyword ?? '') ?></span>"
        </h1>
        
        <?php if (!empty($movies)): ?>
        <div class="text-gray-400 text-sm">
            Hiển thị <?= count($movies) ?> kết quả
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($movies)): ?>
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-24 h-24 bg-gray-900 rounded-full flex items-center justify-center mb-4 border border-gray-800">
                <i data-lucide="search-x" class="w-12 h-12 text-gray-600"></i>
            </div>
            <h2 class="text-xl font-medium text-gray-300 mb-2">Không tìm thấy kết quả</h2>
            <p class="text-gray-500">Vui lòng thử lại với từ khóa khác.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 md:gap-5">
            <?php foreach ($movies as $item): ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group block relative overflow-hidden rounded-lg bg-[#141414] border border-gray-800/50 hover:border-gray-600  ">
                    <div class="aspect-[3/4] relative overflow-hidden">
                        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['poster_url']) ? $item['poster_url'] : ($item['thumb_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy"
                             class="w-full h-full object-cover   ">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 "></div>
                        
                        <div class="absolute top-2 left-2 right-2 flex justify-between">
                            <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg uppercase tracking-wider">
                                <?= htmlspecialchars($item['quality'] ?? 'HD') ?>
                            </span>
                            <?php if (!empty($item['lang'])): ?>
                                <span class="bg-black/60 backdrop-blur-md text-white text-[10px] font-medium px-2 py-1 rounded border border-white/10">
                                    <?= htmlspecialchars($item['lang']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100  ">
                            <div class="w-12 h-12 bg-red-600/90 rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(220,38,38,0.5)]   ">
                                <i data-lucide="play" class="w-5 h-5 text-white ml-1"></i>
                            </div>
                        </div>
                        
                        <?php if (!empty($item['episode_current'])): ?>
                            <div class="absolute bottom-2 right-2">
                                <span class="bg-gray-900/80 backdrop-blur-sm text-gray-300 text-[11px] font-medium px-2 py-1 rounded shadow-lg border border-gray-700">
                                    <?= htmlspecialchars($item['episode_current']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <h3 class="text-white font-medium text-sm truncate group-hover:text-red-500 "><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="text-gray-500 text-xs truncate mt-1"><?= htmlspecialchars($item['origin_name']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="mt-12 flex justify-center">
                <div class="flex items-center space-x-2 bg-[#141414] p-2 rounded-xl border border-gray-800">
                    <?php if ($currentPage > 1): ?>
                        <a href="?keyword=<?= urlencode($keyword) ?>&page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-gray-900 hover:bg-red-600 text-white rounded-lg  border border-gray-800 hover:border-red-600">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                    
                    <span class="px-4 py-2 text-gray-400 font-medium">Trang <?= $currentPage ?> / <?= $totalPages ?></span>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?keyword=<?= urlencode($keyword) ?>&page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-gray-900 hover:bg-red-600 text-white rounded-lg  border border-gray-800 hover:border-red-600">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
