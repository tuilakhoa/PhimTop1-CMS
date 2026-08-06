<h2 class="text-2xl font-bold text-white mb-6">Quản Lý Truyện (OTruyen)</h2>

<?php
$pdo = getPDO();
$settings = getSettings();
$comicApiUrl = rtrim($settings['comicApiUrl'] ?? 'https://otruyenapi.com/v1/api', '/');
$slugComic = $settings['slugComic'] ?? 'truyen';

$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$limit = 24;

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$totalComics = 0;
$totalPages = 0;
$comics = [];

if ($q !== '') {
    $url = $comicApiUrl . "/tim-kiem?keyword=" . urlencode($q) . "&page=" . $page;
} else {
    $url = $comicApiUrl . "/danh-sach/truyen-moi?page=" . $page;
}

$res = @file_get_contents($url);
if ($res) {
    $data = json_decode($res, true);
    if (isset($data['data']['items'])) {
        $comics = $data['data']['items'];
        $totalComics = $data['data']['params']['pagination']['totalItems'] ?? 0;
        $totalPages = $data['data']['params']['pagination']['totalPages'] ?? 0;
    } elseif (isset($data['items'])) {
        $comics = $data['items'];
        $totalComics = $data['pagination']['totalItems'] ?? 0;
        $totalPages = $data['pagination']['totalPages'] ?? 0;
    }
}

// Normalize API comic thumbnails
foreach ($comics as &$comic) {
    $domain = $data['APP_DOMAIN_CDN_IMAGE'] ?? $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://img.otruyenapi.com/uploads/comics/';
    $thumb = $comic['thumb_url'] ?? $comic['poster_url'] ?? '';
    if (!preg_match('/^http/', $thumb) && $thumb) {
        $comic['thumb_url'] = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
    }
    $comic['updatedAt'] = $comic['updatedAt'] ?? ($comic['modified']['time'] ?? date('c'));
}
?>

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-8">
    <form method="GET" action="" class="flex items-center gap-4">
        <input type="hidden" name="page" value="comics">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-5 h-5 text-gray-500"></i>
            </div>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm kiếm truyện theo tên, slug..." class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
        </div>
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center shadow-lg shadow-red-600/20">
            Tìm Kiếm
        </button>
        <?php if ($q !== ''): ?>
        <a href="?page=comics" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center border border-gray-700">
            Xóa Lọc
        </a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-400">
            <thead class="text-xs text-gray-400 uppercase bg-gray-800/50">
                <tr>
                    <th scope="col" class="px-6 py-4">Truyện</th>
                    <th scope="col" class="px-6 py-4 text-center">Trạng thái</th>
                    <th scope="col" class="px-6 py-4 text-center">Chương mới nhất</th>
                    <th scope="col" class="px-6 py-4 text-center">Cập nhật</th>
                    <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($comics) > 0): ?>
                    <?php foreach ($comics as $comic): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="relative w-12 h-16 rounded-md overflow-hidden shadow-lg bg-gray-800 shrink-0">
                                        <img src="<?= htmlspecialchars($comic['thumb_url'] ?? '') ?>" alt="<?= htmlspecialchars($comic['name'] ?? '') ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="text-white font-medium text-base mb-1 line-clamp-1">
                                            <a href="/<?= htmlspecialchars($slugComic) ?>/<?= $comic['slug'] ?>" target="_blank" class="hover:text-red-500 transition-colors">
                                                <?= htmlspecialchars($comic['name'] ?? '') ?>
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 line-clamp-1"><?= htmlspecialchars($comic['origin_name'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-500/10 text-blue-400 px-2 py-1 rounded text-xs whitespace-nowrap"><?= htmlspecialchars($comic['status'] ?? 'N/A') ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php 
                                $latestChap = 'N/A';
                                if (isset($comic['chaptersLatest']) && is_array($comic['chaptersLatest']) && count($comic['chaptersLatest']) > 0) {
                                    $latestChap = $comic['chaptersLatest'][0]['chapter_name'] ?? 'N/A';
                                }
                                ?>
                                <span class="text-gray-300 font-semibold">Chương <?= htmlspecialchars($latestChap) ?></span>
                            </td>
                            <td class="px-6 py-4 text-center text-xs">
                                <?= date('d/m/Y H:i', strtotime($comic['updatedAt'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="/<?= htmlspecialchars($slugComic) ?>/<?= $comic['slug'] ?>" target="_blank" class="text-blue-400 hover:text-blue-300 hover:bg-blue-400/10 transition-colors p-2 rounded-lg" title="Xem trên web">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i data-lucide="book-open" class="w-12 h-12 mb-3 opacity-50"></i>
                                <p>Không tìm thấy truyện nào.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="p-4 border-t border-gray-800 flex justify-between items-center bg-gray-800/30">
            <div class="text-sm text-gray-400">
                Hiển thị trang <span class="font-medium text-white"><?= $page ?></span> / <span class="font-medium text-white"><?= $totalPages ?></span>
                (Tổng cộng <span class="font-medium text-white"><?= number_format($totalComics) ?></span> truyện)
            </div>
            <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=comics&p=1<?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors" title="Trang đầu">
                        <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                    </a>
                    <a href="?page=comics&p=<?= $page - 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($page <= 2) $endPage = min($totalPages, 5);
                if ($page >= $totalPages - 1) $startPage = max(1, $totalPages - 4);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                    if ($i === $page) {
                        $activeClass = 'bg-red-600 text-white font-medium shadow-md';
                    } else {
                        $activeClass = 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors';
                    }
                ?>
                    <a href="?page=comics&p=<?= $i ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 rounded-md min-w-[32px] text-center <?= $activeClass ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=comics&p=<?= $page + 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                    <a href="?page=comics&p=<?= $totalPages ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors" title="Trang cuối">
                        <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
