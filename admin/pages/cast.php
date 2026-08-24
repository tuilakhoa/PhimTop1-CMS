<?php
$repo = getMovieRepository();
$allCasts = $repo->getAllCasts();

// Sort by count descending
usort($allCasts, function($a, $b) {
    return $b['count'] <=> $a['count'];
});

// Search filter
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $searchLower = mb_strtolower($q);
    $allCasts = array_filter($allCasts, function($c) use ($searchLower) {
        return str_contains(mb_strtolower($c['name']), $searchLower);
    });
}

// Pagination
$perPage = 50;
$total = count($allCasts);
$totalPages = ceil($total / $perPage);
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

$offset = ($page - 1) * $perPage;
$pagedCasts = array_slice($allCasts, $offset, $perPage);
?>

<h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-8 tracking-tight">Quản Lý Diễn Viên & Đạo Diễn (Tự Động)</h2>

<div class="bg-admin-panel backdrop-blur-xl border border-admin-border rounded-[2rem] p-6 mb-10 shadow-2xl relative overflow-hidden group">
    <div class="absolute -top-32 -right-32 w-64 h-64 bg-admin-primary/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-admin-primary/10 transition-colors duration-700"></div>
    <form method="GET" action="" class="flex items-center gap-4 relative z-10">
        <input type="hidden" name="page" value="cast">
        <div class="flex-1 relative group/input">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-focus-within/input:scale-110">
                <i data-lucide="search" class="w-5 h-5 text-gray-500 group-focus-within/input:text-admin-primary transition-colors"></i>
            </div>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm kiếm tên diễn viên/đạo diễn..." class="w-full bg-black/40 backdrop-blur-sm border border-white/10 text-white rounded-xl pl-12 pr-4 py-3.5 focus:outline-none focus:border-admin-primary focus:ring-1 focus:ring-admin-primary transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)]">
        </div>
        <button type="submit" class="bg-gradient-to-r from-admin-primary to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-[0_0_20px_rgba(244,63,94,0.3)] hover:shadow-[0_0_25px_rgba(244,63,94,0.5)] flex items-center gap-2 transform hover:-translate-y-0.5">
            <i data-lucide="search" class="w-4 h-4"></i> Tìm Kiếm
        </button>
    </form>
    <p class="text-xs text-gray-400 mt-4 relative z-10"><i data-lucide="info" class="w-3 h-3 inline-block -mt-0.5 mr-1"></i> Dữ liệu này được tự động trích xuất và tổng hợp từ cơ sở dữ liệu phim (cả API và Crawl). Không cần thêm thủ công.</p>
</div>

<div class="bg-admin-panel backdrop-blur-2xl border border-admin-border rounded-[2rem] overflow-hidden shadow-2xl relative mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-400 relative z-10">
            <thead class="text-[11px] text-gray-400 uppercase tracking-widest bg-black/40 backdrop-blur-md border-b border-white/5">
                <tr>
                    <th scope="col" class="px-8 py-5 font-bold">Hồ Sơ</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Vai Trò</th>
                    <th scope="col" class="px-6 py-5 font-bold text-center">Số Phim Tham Gia</th>
                    <th scope="col" class="px-8 py-5 font-bold text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($pagedCasts)): ?>
                    <tr>
                        <td colspan="4" class="px-8 py-10 text-center text-gray-500">Không tìm thấy dữ liệu phù hợp.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagedCasts as $cast): ?>
                        <tr class="hover:bg-white/[0.02] transition-colors duration-300 group/row">
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-5">
                                    <div class="relative w-12 h-12 rounded-full overflow-hidden shadow-lg bg-black/50 shrink-0 border border-white/5 group-hover/row:border-admin-primary/30 transition-colors">
                                        <div class="w-full h-full bg-gray-800 flex items-center justify-center text-gray-500">
                                            <i data-lucide="user" class="w-6 h-6"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-white font-bold text-base group-hover/row:text-admin-primary transition-colors tracking-wide">
                                            <?= htmlspecialchars($cast['name']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-500/10 border border-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap shadow-[0_0_10px_rgba(59,130,246,0.1)]">
                                    <?= htmlspecialchars($cast['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-emerald-400 font-bold"><?= $cast['count'] ?></span> phim
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2 opacity-0 group-hover/row:opacity-100 transition-opacity duration-300">
                                    <a href="?page=movies&q=<?= urlencode($cast['name']) ?>" class="text-blue-400 hover:text-blue-300 hover:bg-blue-400/10 transition-colors p-2 rounded-lg flex items-center text-xs font-medium" title="Xem Phim">
                                        <i data-lucide="film" class="w-4 h-4 mr-1.5"></i> Xem Phim
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
    <div class="flex justify-center mb-10">
        <div class="inline-flex bg-admin-panel border border-admin-border rounded-xl shadow-xl overflow-hidden">
            <?php if ($page > 1): ?>
                <a href="?page=cast&q=<?= urlencode($q) ?>&p=<?= $page - 1 ?>" class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-r border-admin-border">Trước</a>
            <?php endif; ?>
            
            <span class="px-4 py-2 text-sm font-medium text-white bg-admin-primary/20 border-r border-admin-border">
                Trang <?= $page ?> / <?= $totalPages ?>
            </span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=cast&q=<?= urlencode($q) ?>&p=<?= $page + 1 ?>" class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Sau</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
