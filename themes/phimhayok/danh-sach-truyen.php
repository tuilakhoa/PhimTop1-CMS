<?php
$settings = getSettings();
if (isset($settings['enableComics']) && $settings['enableComics'] == 0) {
    header("Location: /");
    exit;
}
$comicApiUrl = rtrim($settings['comicApiUrl'] ?? 'https://otruyenapi.com/v1/api', '/');
$slugComic = $settings['slugComic'] ?? 'truyen';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$title = "Truyện Tranh Mới Cập Nhật";
$pageTitle = "$title - Trang $page";
$pageDesc = "Đọc truyện tranh mới cập nhật, thể loại đa dạng tại {$settings['siteName']}";
$pageKeywords = "đọc truyện, truyện tranh, truyện mới";

$movies = [];
$totalPages = 1;
$currentPage = 1;

$url = $comicApiUrl . "/danh-sach/truyen-moi?page=" . $page;
$res = @file_get_contents($url);
$domainImg = 'https://img.otruyenapi.com/uploads/comics/';

if ($res) {
    $data = json_decode($res, true);
    if (isset($data['data']['items'])) {
        $movies = $data['data']['items'];
        $totalPages = $data['data']['params']['pagination']['totalPages'] ?? 1;
        $currentPage = $data['data']['params']['pagination']['currentPage'] ?? $page;
        $domainImg = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? $data['APP_DOMAIN_CDN_IMAGE'] ?? 'https://img.otruyenapi.com';
        $domainImg = rtrim($domainImg, '/') . '/uploads/comics/';
    }
}

include __DIR__ . '/header.php';
?>

<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1400px] py-8">
    <div class="flex flex-col md:flex-row items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white flex items-center mb-4 md:mb-0">
            <i data-lucide="book-open" class="w-6 h-6 mr-3 text-red-600"></i>
            <?= htmlspecialchars($title) ?>
        </h1>
        
        <?php if (!empty($movies)): ?>
        <div class="text-gray-400 text-sm bg-[#141414] px-4 py-2 rounded-lg border border-gray-800">
            Trang <?= $currentPage ?> / <?= $totalPages ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($movies)): ?>
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-24 h-24 bg-gray-900 rounded-full flex items-center justify-center mb-4 border border-gray-800">
                <i data-lucide="book-x" class="w-12 h-12 text-gray-600"></i>
            </div>
            <h2 class="text-xl font-medium text-gray-300 mb-2">Không tìm thấy truyện nào</h2>
            <p class="text-gray-500">Danh mục này hiện tại chưa có truyện nào được cập nhật.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 md:gap-5">
            <?php foreach ($movies as $item): 
                $thumb = !empty($item['thumb_url']) ? $item['thumb_url'] : (!empty($item['poster_url']) ? $item['poster_url'] : '');
                if (!preg_match('/^http/', $thumb) && $thumb) {
                    $thumb = rtrim($domainImg, '/') . '/' . ltrim($thumb, '/');
                }
            ?>
                <a href="/<?= htmlspecialchars($slugComic) ?>/<?= urlencode($item['slug']) ?>" class="group block relative overflow-hidden rounded-lg bg-[#141414] border border-gray-800/50 hover:border-gray-600 transition-all duration-300">
                    <div class="aspect-[2/3] relative overflow-hidden">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                        
                        <!-- Top Labels -->
                        <div class="absolute top-2 left-2 right-2 flex justify-between">
                            <?php if (!empty($item['status'])): ?>
                                <span class="bg-blue-600/90 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg uppercase tracking-wider">
                                    <?= htmlspecialchars($item['status']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Read Icon Hover -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-12 h-12 bg-red-600/90 rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(220,38,38,0.5)] transform group-hover:scale-110 transition-transform">
                                <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Episode count -->
                        <?php 
                        $latestChap = null;
                        if (isset($item['chaptersLatest']) && is_array($item['chaptersLatest']) && count($item['chaptersLatest']) > 0) {
                            $latestChap = $item['chaptersLatest'][0]['chapter_name'] ?? null;
                        }
                        if ($latestChap): 
                        ?>
                            <div class="absolute bottom-2 right-2">
                                <span class="bg-gray-900/80 backdrop-blur-sm text-gray-300 text-[11px] font-medium px-2 py-1 rounded shadow-lg border border-gray-700">
                                    Chương <?= htmlspecialchars($latestChap) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <h3 class="text-white font-medium text-sm truncate group-hover:text-red-500 transition-colors" title="<?= htmlspecialchars($item['name']) ?>">
                            <?= htmlspecialchars($item['name']) ?>
                        </h3>
                        <p class="text-gray-500 text-xs truncate mt-1"><?= htmlspecialchars(is_array($item['origin_name'] ?? '') ? implode(', ', $item['origin_name']) : ($item['origin_name'] ?? '')) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="mt-12 flex justify-center">
                <div class="flex items-center space-x-2 bg-[#141414] p-2 rounded-xl border border-gray-800">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-gray-900 hover:bg-red-600 text-white rounded-lg transition-colors border border-gray-800 hover:border-red-600">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                    
                    <span class="px-4 py-2 text-gray-400 font-medium">Trang <?= $currentPage ?> / <?= $totalPages ?></span>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-gray-900 hover:bg-red-600 text-white rounded-lg transition-colors border border-gray-800 hover:border-red-600">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
