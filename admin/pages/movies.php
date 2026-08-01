<h2 class="text-2xl font-bold text-white mb-6">Quản Lý Phim</h2>

<?php
$pdo = getPDO();
$settings = getSettings();
$displayMode = $settings['displayMode'] ?? 'api';

$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$totalMovies = 0;
$totalPages = 0;
$movies = [];

if ($displayMode === 'crawl') {
    $where = "1=1";
    $params = [];

    if ($q !== '') {
        $where .= " AND (name LIKE ? OR origin_name LIKE ? OR slug LIKE ?)";
        $search = "%{$q}%";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    if ($pdo) {
        $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM movies WHERE $where");
        $stmtTotal->execute($params);
        $totalMovies = $stmtTotal->fetchColumn();
        $totalPages = ceil($totalMovies / $limit);

        $sql = "SELECT * FROM movies WHERE $where ORDER BY updated_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    // Chế độ API
    if ($q !== '') {
        $url = "https://phimapi.com/v1/api/tim-kiem?keyword=" . urlencode($q) . "&page=" . $page;
        $res = @file_get_contents($url);
        if ($res) {
            $data = json_decode($res, true);
            if (isset($data['data']['items'])) {
                $movies = $data['data']['items'];
                $totalMovies = $data['data']['params']['pagination']['totalItems'] ?? 0;
                $totalPages = $data['data']['params']['pagination']['totalPages'] ?? 0;
            }
        }
    } else {
        $url = "https://phimapi.com/danh-sach/phim-moi-cap-nhat?page=" . $page;
        $res = @file_get_contents($url);
        if ($res) {
            $data = json_decode($res, true);
            if (isset($data['items'])) {
                $movies = $data['items'];
                $totalMovies = $data['pagination']['totalItems'] ?? 0;
                $totalPages = $data['pagination']['totalPages'] ?? 0;
            }
        }
    }
    
    // Normalize API movie thumbnails
    foreach ($movies as &$movie) {
        $domain = $data['APP_DOMAIN_CDN_IMAGE'] ?? $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
        $thumb = $movie['thumb_url'] ?? $movie['poster_url'] ?? '';
        if (!preg_match('/^http/', $thumb) && $thumb) {
            $movie['thumb_url'] = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
        }
        $movie['updated_at'] = $movie['modified']['time'] ?? date('c');
    }
}
?>

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-8">
    <form method="GET" action="" class="flex items-center gap-4">
        <input type="hidden" name="page" value="movies">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-5 h-5 text-gray-500"></i>
            </div>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm kiếm phim theo tên, tên gốc, hoặc slug..." class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
        </div>
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center shadow-lg shadow-red-600/20">
            Tìm Kiếm
        </button>
        <?php if ($q !== ''): ?>
        <a href="?page=movies" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center border border-gray-700">
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
                    <th scope="col" class="px-6 py-4">Phim</th>
                    <th scope="col" class="px-6 py-4 text-center">Năm</th>
                    <th scope="col" class="px-6 py-4 text-center">Tập</th>
                    <th scope="col" class="px-6 py-4 text-center">Lượt xem</th>
                    <th scope="col" class="px-6 py-4 text-center">Cập nhật</th>
                    <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movies) > 0): ?>
                    <?php foreach ($movies as $movie): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors" id="movie-<?= $movie['slug'] ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="relative w-12 h-16 rounded-md overflow-hidden shadow-lg bg-gray-800 shrink-0">
                                        <img src="<?= htmlspecialchars($movie['thumb_url'] ?? '') ?>" alt="<?= htmlspecialchars($movie['name'] ?? '') ?>" class="w-full h-full object-cover">
                                        <?php if (!empty($movie['quality'])): ?>
                                            <div class="absolute top-0 right-0 bg-red-600 text-[10px] font-bold text-white px-1 py-0.5 rounded-bl">
                                                <?= htmlspecialchars($movie['quality']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-white font-medium text-base mb-1 line-clamp-1">
                                            <a href="/phim/<?= $movie['slug'] ?>" target="_blank" class="hover:text-red-500 transition-colors">
                                                <?= htmlspecialchars($movie['name'] ?? '') ?>
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 line-clamp-1"><?= htmlspecialchars($movie['origin_name'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center"><?= $movie['year'] ?? 'N/A' ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-500/10 text-blue-400 px-2 py-1 rounded text-xs whitespace-nowrap"><?= htmlspecialchars($movie['episode_current'] ?? 'N/A') ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center text-gray-300">
                                    <i data-lucide="eye" class="w-3 h-3 mr-1"></i> <?= number_format($movie['view'] ?? 0) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-xs">
                                <?= date('d/m/Y H:i', strtotime($movie['updated_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="/phim/<?= $movie['slug'] ?>" target="_blank" class="text-blue-400 hover:text-blue-300 hover:bg-blue-400/10 transition-colors p-2 rounded-lg" title="Xem trên web">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    <?php if ($displayMode === 'crawl'): ?>
                                    <a href="?page=edit_movie&slug=<?= $movie['slug'] ?>" class="text-yellow-400 hover:text-yellow-300 hover:bg-yellow-400/10 transition-colors p-2 rounded-lg" title="Sửa phim">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                    <button onclick="deleteMovie('<?= $movie['slug'] ?>')" class="text-red-400 hover:text-red-300 hover:bg-red-400/10 transition-colors p-2 rounded-lg" title="Xóa phim">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    <?php else: ?>
                                    <a href="?page=crawl&action=crawl_movie&slug=<?= $movie['slug'] ?>" class="text-green-400 hover:text-green-300 hover:bg-green-400/10 transition-colors p-2 rounded-lg" title="Cào phim này về DB">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i data-lucide="film" class="w-12 h-12 mb-3 opacity-50"></i>
                                <p>Không tìm thấy phim nào.</p>
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
                (Tổng cộng <span class="font-medium text-white"><?= number_format($totalMovies) ?></span> phim)
            </div>
            <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=movies&p=1<?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors" title="Trang đầu">
                        <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                    </a>
                    <a href="?page=movies&p=<?= $page - 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                // Adjust if near ends
                if ($page <= 2) $endPage = min($totalPages, 5);
                if ($page >= $totalPages - 1) $startPage = max(1, $totalPages - 4);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                    if ($i === $page) {
                        $activeClass = 'bg-red-600 text-white font-medium shadow-md';
                    } else {
                        $activeClass = 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors';
                    }
                ?>
                    <a href="?page=movies&p=<?= $i ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 rounded-md min-w-[32px] text-center <?= $activeClass ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=movies&p=<?= $page + 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                    <a href="?page=movies&p=<?= $totalPages ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="px-3 py-1.5 bg-gray-800 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white transition-colors" title="Trang cuối">
                        <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    async function deleteMovie(slug) {
        if (!confirm('Bạn có chắc chắn muốn xóa phim này khỏi cơ sở dữ liệu? Phim sẽ không thể hiển thị trên web nữa.')) {
            return;
        }
        
        try {
            const res = await fetch('api/delete_movie.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ slug })
            });
            
            const data = await res.json();
            
            if (data.status === 'success') {
                const row = document.getElementById('movie-' + slug);
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể xóa phim'));
            }
        } catch (err) {
            alert('Lỗi kết nối: ' + err.message);
        }
    }
</script>
