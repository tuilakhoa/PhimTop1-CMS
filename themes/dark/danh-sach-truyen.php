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

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white border-l-4 border-red-500 pl-3"><?= htmlspecialchars($title) ?></h2>
    </div>
    
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <?php foreach ($movies as $movie): 
            $thumb = !empty($movie['thumb_url']) ? $movie['thumb_url'] : (!empty($movie['poster_url']) ? $movie['poster_url'] : '');
            if (!preg_match('/^http/', $thumb) && $thumb) {
                $thumb = rtrim($domainImg, '/') . '/' . ltrim($thumb, '/');
            }
        ?>
            <a href="/<?= htmlspecialchars($slugComic) ?>/<?= htmlspecialchars($movie['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-800 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-500/20">
                <div class="relative aspect-[2/3] w-full overflow-hidden">
                    <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <?php if (!empty($movie['status'])): ?>
                            <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm"><?= htmlspecialchars($movie['status']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="w-12 h-12 bg-red-600/90 rounded-full flex items-center justify-center transform scale-50 group-hover:scale-100 transition-transform duration-300 shadow-lg">
                            <i data-lucide="book-open" class="w-6 h-6 text-white ml-1"></i>
                        </div>
                    </div>
                    
                    <?php 
                    $latestChap = null;
                    if (isset($movie['chaptersLatest']) && is_array($movie['chaptersLatest']) && count($movie['chaptersLatest']) > 0) {
                        $latestChap = $movie['chaptersLatest'][0]['chapter_name'] ?? null;
                    }
                    if ($latestChap): 
                    ?>
                        <div class="absolute bottom-2 right-2 bg-black/80 backdrop-blur-sm text-white text-xs font-semibold px-2 py-1 rounded border border-white/10">
                            Chương <?= htmlspecialchars($latestChap) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-3 relative z-10 flex flex-col flex-grow">
                    <h3 class="text-sm font-semibold text-white line-clamp-1 mb-1 group-hover:text-red-400 transition-colors" title="<?= htmlspecialchars($movie['name']) ?>">
                        <?= htmlspecialchars($movie['name']) ?>
                    </h3>
                    <p class="text-xs text-gray-400 line-clamp-1">
                        <?= htmlspecialchars(is_array($movie['origin_name'] ?? '') ? implode(', ', $movie['origin_name']) : ($movie['origin_name'] ?? '')) ?>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($movies)): ?>
        <div class="text-center py-12 text-gray-400 flex flex-col items-center">
            <i data-lucide="book-x" class="w-12 h-12 mb-3 text-gray-500"></i>
            Không có truyện nào để hiển thị.
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <div class="flex justify-center mt-12 gap-2">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-red-600 text-white rounded transition-colors">Trang trước</a>
        <?php endif; ?>
        <span class="px-4 py-2 text-gray-400">Trang <?= $currentPage ?> / <?= $totalPages ?></span>
        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-red-600 text-white rounded transition-colors">Trang sau</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
