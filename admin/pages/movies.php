<h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-8 tracking-tight">Quản Lý Phim</h2>

<?php
try {
$pdo = getPDO();
$settings = getSettings();


$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$totalMovies = 0;
$totalPages = 0;
$movies = [];

    $dataSource = $settings['dataSource'] ?? 'api';
    
    if ($dataSource === 'local') {
        // Chế độ Local (Database)
        if ($q !== '') {
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM movies WHERE name LIKE ? OR origin_name LIKE ? OR slug LIKE ?");
            $searchParam = "%$q%";
            $stmtCount->execute([$searchParam, $searchParam, $searchParam]);
            $totalMovies = $stmtCount->fetchColumn();
            $totalPages = ceil($totalMovies / $limit);
            
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE name LIKE ? OR origin_name LIKE ? OR slug LIKE ? ORDER BY updated_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute([$searchParam, $searchParam, $searchParam]);
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $totalMovies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
            $totalPages = ceil($totalMovies / $limit);
            
            $stmt = $pdo->prepare("SELECT * FROM movies ORDER BY updated_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Normalize local movie thumbnails
        foreach ($movies as &$movie) {
            $domain = 'https://phimimg.com/';
            $thumb = $movie['thumb_url'] ?? $movie['poster_url'] ?? '';
            if (!preg_match('/^http/', $thumb) && $thumb) {
                $movie['thumb_url'] = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
            }
        }
        
    } else {
        // Chế độ API (Dự phòng / Live)
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
} catch (\Throwable $e) {
    echo "<div class='text-red-500 bg-red-100 p-4 rounded mb-4'>Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "</div>";
}
?>

<div class="bg-admin-panel backdrop-blur-xl border border-admin-border rounded-[2rem] p-6 mb-10 shadow-2xl relative overflow-hidden group">
    <div class="absolute -top-32 -right-32 w-64 h-64 bg-admin-primary/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-admin-primary/10 transition-colors duration-700"></div>
    <form method="GET" action="" class="flex items-center gap-4 relative z-10">
        <input type="hidden" name="page" value="movies">
        <div class="flex-1 relative group/input">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-focus-within/input:scale-110">
                <i data-lucide="search" class="w-5 h-5 text-gray-500 group-focus-within/input:text-admin-primary transition-colors"></i>
            </div>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm kiếm phim theo tên, tên gốc, hoặc slug..." class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl pl-12 pr-4 py-3.5 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)]">
        </div>
        <button type="submit" class="bg-gradient-to-r from-admin-primary to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-[0_0_20px_rgba(244,63,94,0.3)] hover:shadow-[0_0_25px_rgba(244,63,94,0.5)] flex items-center gap-2 transform hover:-translate-y-0.5">
            <i data-lucide="search" class="w-4 h-4"></i> Tìm Kiếm
        </button>
        <?php if ($q !== ''): ?>
        <a href="?page=movies" class="bg-white/5 hover:bg-white/10 text-gray-300 font-bold py-3.5 px-6 rounded-xl transition-all border border-white/10 hover:border-white/20 hover:text-white flex items-center gap-2 transform hover:-translate-y-0.5">
            <i data-lucide="x" class="w-4 h-4"></i> Xóa
        </a>
        <?php endif; ?>
        
        
    </form>
</div>

<div class="bg-admin-panel backdrop-blur-2xl border border-admin-border rounded-[2rem] overflow-hidden shadow-2xl relative">
    <div class="absolute inset-0 bg-gradient-to-br from-white/[0.02] to-transparent pointer-events-none"></div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-400 relative z-10">
            <thead class="text-[11px] text-gray-400 uppercase tracking-widest bg-black/40 backdrop-blur-md border-b border-white/5">
                <tr>
                    <th scope="col" class="px-8 py-5 font-bold">Phim</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Năm</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Tập</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Lượt xem</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Cập nhật</th>
                    <th scope="col" class="px-8 py-5 font-bold text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php if (count($movies) > 0): ?>
                    <?php foreach ($movies as $movie): ?>
                        <tr class="hover:bg-white/[0.02] transition-colors duration-300 group/row" id="movie-<?= $movie['slug'] ?>">
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-5">
                                    <div class="relative w-14 h-20 rounded-xl overflow-hidden shadow-lg bg-black/50 shrink-0 border border-white/5 group-hover/row:border-admin-primary/30 transition-colors">
                                        <img src="<?= htmlspecialchars($movie['thumb_url'] ?? '') ?>" alt="<?= htmlspecialchars($movie['name'] ?? '') ?>" class="w-full h-full object-cover group-hover/row:scale-110 transition-transform duration-500">
                                        <?php if (!empty($movie['quality'])): ?>
                                            <div class="absolute top-0 right-0 bg-gradient-to-r from-admin-primary to-rose-600 text-[9px] font-black tracking-wider text-white px-1.5 py-0.5 rounded-bl-lg shadow-md">
                                                <?= htmlspecialchars($movie['quality']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-white font-bold text-base mb-1.5 line-clamp-1 group-hover/row:text-admin-primary transition-colors tracking-wide">
                                            <a href="/phim/<?= $movie['slug'] ?>" target="_blank">
                                                <?= htmlspecialchars($movie['name'] ?? '') ?>
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 font-medium tracking-wide line-clamp-1"><?= htmlspecialchars($movie['origin_name'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-medium"><?= $movie['year'] ?? 'N/A' ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-500/10 border border-blue-500/20 text-blue-400 px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap shadow-[0_0_10px_rgba(59,130,246,0.1)]"><?= htmlspecialchars($movie['episode_current'] ?? 'N/A') ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center text-gray-300 font-medium">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1.5 text-indigo-400"></i> <?= number_format($movie['view'] ?? 0) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-gray-400">
                                <?= date('d/m/Y H:i', strtotime($movie['updated_at'] ?? 'now')) ?>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2 opacity-0 group-hover/row:opacity-100 transition-opacity duration-300">
                                    <a href="/phim/<?= $movie['slug'] ?>" target="_blank" class="text-blue-400 hover:text-blue-300 hover:bg-blue-400/10 transition-colors p-2 rounded-lg" title="Xem trên web">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                    
                                    <button onclick="blockMovie('<?= $movie['slug'] ?>', '<?= htmlspecialchars($movie['name'] ?? '', ENT_QUOTES) ?>')" class="text-red-400 hover:text-red-300 hover:bg-red-400/10 transition-colors p-2 rounded-lg" title="Gỡ bỏ/Chặn phim này">
                                        <i data-lucide="ban" class="w-4 h-4"></i>
                                    </button>
                                    
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
        <div class="px-8 py-5 border-t border-white/5 flex justify-between items-center bg-black/40 backdrop-blur-md relative z-10">
            <div class="text-sm text-gray-400 font-medium">
                Hiển thị trang <span class="font-medium text-white"><?= $page ?></span> / <span class="font-medium text-white"><?= $totalPages ?></span>
                (Tổng cộng <span class="font-medium text-white"><?= number_format($totalMovies) ?></span> phim)
            </div>
            <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=movies&p=1<?= $q ? '&q=' . urlencode($q) : '' ?>" class="p-2 bg-white/5 border border-white/10 text-gray-400 rounded-lg hover:bg-white/10 hover:text-white transition-all hover:border-white/20" title="Trang đầu">
                        <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                    </a>
                    <a href="?page=movies&p=<?= $page - 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="p-2 bg-white/5 border border-white/10 text-gray-400 rounded-lg hover:bg-white/10 hover:text-white transition-all hover:border-white/20">
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
                        $activeClass = 'bg-gradient-to-r from-admin-primary to-rose-600 text-white font-bold shadow-[0_0_15px_rgba(244,63,94,0.4)] border-transparent';
                    } else {
                        $activeClass = 'bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white transition-all border-white/10 hover:border-white/20';
                    }
                ?>
                    <a href="?page=movies&p=<?= $i ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="w-9 h-9 flex items-center justify-center rounded-lg text-sm border <?= $activeClass ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=movies&p=<?= $page + 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="p-2 bg-white/5 border border-white/10 text-gray-400 rounded-lg hover:bg-white/10 hover:text-white transition-all hover:border-white/20">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                    <a href="?page=movies&p=<?= $totalPages ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="p-2 bg-white/5 border border-white/10 text-gray-400 rounded-lg hover:bg-white/10 hover:text-white transition-all hover:border-white/20" title="Trang cuối">
                        <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    

    

    async function blockMovie(slug, name) {
        if (!confirm(`Bạn có chắc chắn muốn GỠ BỎ và CHẶN phim "${name}"?\nPhim sẽ biến mất hoàn toàn trên web và app (trên toàn hệ thống).`)) {
            return;
        }
        
        try {
            const res = await fetch('api/block_movie.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'block', slug, name })
            });
            
            const data = await res.json();
            if (data.status === 'success') {
                const row = document.getElementById('movie-' + slug);
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể chặn phim'));
            }
        } catch (err) {
            alert('Lỗi kết nối: ' + err.message);
        }
    }
</script>
