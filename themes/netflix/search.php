<?php include __DIR__ . '/header.php'; ?>
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 max-w-xl mx-auto md:hidden">
        <form action="search.php" method="GET" class="relative">
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm kiếm phim..." 
                class="w-full bg-gray-800 text-gray-200 text-sm rounded-full pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 border border-gray-700 transition-all">
            <i data-lucide="search" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400"></i>
        </form>
    </div>

    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-white border-l-4 border-red-500 pl-3"><?= $title ?></h2>
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
            <a href="/<?= $settings['slugMovie'] ?? 'phim' ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-800 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-500/20">
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
        <div class="text-center py-12 text-gray-400">Không tìm thấy kết quả nào phù hợp.</div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
