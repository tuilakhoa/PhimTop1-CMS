<?php include __DIR__ . '/header.php'; ?>
<div class="bg-[#000000] min-h-screen text-gray-200 font-sans pb-20">
    <div class="w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto pt-8 lg:pt-12">
        <div class="mb-8 max-w-xl mx-auto md:hidden">
            <form action="search.php" method="GET" class="relative">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm kiếm phim..." 
                    class="w-full bg-[#111] text-gray-200 text-sm rounded-full pl-10 pr-4 py-3 focus:outline-none focus:ring-1 focus:ring-white border border-gray-900 transition-all">
                <i data-lucide="search" class="absolute left-4 top-3.5 w-5 h-5 text-gray-500"></i>
            </form>
        </div>

        <div class="flex items-center justify-between mb-8 border-b border-gray-900 pb-4">
            <h2 class="text-2xl font-bold text-white tracking-tight"><?= $title ?></h2>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-5 gap-y-10">
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
                <a href="/<?= $settings['slugMovie'] ?? 'phim' ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col">
                    <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg bg-[#111] mb-3">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        
                        <div class="absolute top-2 left-2 flex gap-1.5">
                            <?php if (!empty($movie['quality'])): ?>
                                <span class="bg-black/70 backdrop-blur-md text-white text-[10px] font-medium px-2 py-0.5 rounded"><?= htmlspecialchars($movie['quality']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($movie['episode_current'])): ?>
                            <div class="absolute bottom-2 right-2 bg-black/80 backdrop-blur-md text-white text-[10px] font-medium px-2 py-1 rounded">
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
                Không tìm thấy kết quả nào phù hợp.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
