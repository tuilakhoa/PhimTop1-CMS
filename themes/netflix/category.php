<?php
include __DIR__ . '/header.php';
?>

<?php
// Lấy giá trị filter hiện tại từ URL
$currentCategory = $_GET['category'] ?? '';
$currentCountry = $_GET['country'] ?? '';
$currentYearVal = $_GET['year'] ?? '';
$currentSortField = $_GET['sort_field'] ?? 'modified.time';
$currentSortType = $_GET['sort_type'] ?? 'desc';
$currentSort = $currentSortField . '-' . $currentSortType;

$filterCategories = ['' => 'Tất cả thể loại', 'hanh-dong' => 'Hành động', 'tinh-cam' => 'Tình cảm', 'hai-huoc' => 'Hài hước', 'kinh-di' => 'Kinh dị', 'tam-ly' => 'Tâm lý', 'hoat-hinh' => 'Hoạt hình', 'thien-nhien' => 'Thiên nhiên', 'co-trang' => 'Cổ trang', 'hinh-su' => 'Hình sự', 'tai-lieu' => 'Tài liệu', 'khoa-hoc' => 'Khoa học'];
$filterCountries = ['' => 'Tất cả quốc gia', 'han-quoc' => 'Hàn Quốc', 'trung-quoc' => 'Trung Quốc', 'au-my' => 'Âu Mỹ', 'viet-nam' => 'Việt Nam', 'nhat-ban' => 'Nhật Bản', 'thai-lan' => 'Thái Lan', 'an-do' => 'Ấn Độ'];
$filterYears = ['' => 'Tất cả năm'];
$currentYear = (int)date('Y');
for ($y = $currentYear; $y >= 2010; $y--) $filterYears[$y] = (string)$y;
$filterSorts = ['modified.time-desc' => 'Thời gian cập nhật (Mới nhất)', 'modified.time-asc' => 'Thời gian cập nhật (Cũ nhất)', 'year-desc' => 'Năm phát hành (Mới nhất)', 'year-asc' => 'Năm phát hành (Cũ nhất)'];
?>
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white border-l-4 border-red-500 pl-3"><?= htmlspecialchars($title) ?></h2>
    </div>
    
    <!-- Filter Form -->
    <div class="bg-gray-800 rounded-xl p-4 mb-8">
        <form action="" method="GET" class="flex flex-wrap gap-4 items-end">
            <!-- Preserve other query string params except page and filters -->
            <?php foreach ($_GET as $k => $v): 
                if (in_array($k, ['category', 'country', 'year', 'sort_field', 'sort_type', 'sort', 'page'])) continue;
            ?>
                <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
            <?php endforeach; ?>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Sắp xếp</label>
                <select name="sort" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-500 transition-colors">
                    <?php foreach ($filterSorts as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentSort === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Thể loại</label>
                <select name="category" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-500 transition-colors">
                    <?php foreach ($filterCategories as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentCategory === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Quốc gia</label>
                <select name="country" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-500 transition-colors">
                    <?php foreach ($filterCountries as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentCountry === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 min-w-[120px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Năm</label>
                <select name="year" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-500 transition-colors">
                    <?php foreach ($filterYears as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentYearVal == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-none w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center border border-red-500">
                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i> Lọc Phim
                </button>
            </div>
        </form>
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
        <div class="text-center py-12 text-gray-400">Không có phim nào để hiển thị.</div>
    <?php endif; ?>

    <!-- Pagination Simple -->
    <div class="flex justify-center mt-12 gap-2">
        <?php if (($currentPage ?? $page) > 1): ?>
            <a href="?type=<?= urlencode($type) ?>&slug=<?= urlencode($slug) ?>&page=<?= ($currentPage ?? $page) - 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-red-600 text-white rounded transition-colors">Trang trước</a>
        <?php endif; ?>
        <span class="px-4 py-2 text-gray-400">Trang <?= $currentPage ?? $page ?> / <?= $totalPages ?? 1 ?></span>
        <?php if (($currentPage ?? $page) < ($totalPages ?? 1)): ?>
            <a href="?type=<?= urlencode($type) ?>&slug=<?= urlencode($slug) ?>&page=<?= ($currentPage ?? $page) + 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-red-600 text-white rounded transition-colors">Trang sau</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
