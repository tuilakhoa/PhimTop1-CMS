<?php include __DIR__ . '/header.php'; ?>

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
<div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1400px] py-8">
    <div class="flex flex-col md:flex-row items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white flex items-center mb-4 md:mb-0">
            <i data-lucide="folder" class="w-6 h-6 mr-3 text-red-600"></i>
            <?= htmlspecialchars($title ?? 'Danh sách phim') ?>
        </h1>
        
        <?php if (!empty($movies)): ?>
        <div class="text-gray-400 text-sm">
            Hiển thị <?= count($movies) ?> kết quả
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter Form -->
    <div class="bg-[#141414] border border-gray-800 rounded-xl p-4 mb-8">
        <form action="" method="GET" class="flex flex-wrap gap-4 items-end">
            <!-- Preserve other query string params except page and filters -->
            <?php foreach ($_GET as $k => $v): 
                if (in_array($k, ['category', 'country', 'year', 'sort_field', 'sort_type', 'sort', 'page'])) continue;
            ?>
                <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
            <?php endforeach; ?>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Sắp xếp</label>
                <select name="sort" class="w-full bg-gray-900 border border-gray-800 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-600 transition-colors">
                    <?php foreach ($filterSorts as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentSort === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Thể loại</label>
                <select name="category" class="w-full bg-gray-900 border border-gray-800 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-600 transition-colors">
                    <?php foreach ($filterCategories as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentCategory === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Quốc gia</label>
                <select name="country" class="w-full bg-gray-900 border border-gray-800 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-600 transition-colors">
                    <?php foreach ($filterCountries as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentCountry === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 min-w-[120px]">
                <label class="block text-gray-400 text-xs font-medium mb-2 uppercase">Năm</label>
                <select name="year" class="w-full bg-gray-900 border border-gray-800 text-white rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-red-600 transition-colors">
                    <?php foreach ($filterYears as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $currentYearVal == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-none w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center border border-red-500 shadow-[0_0_15px_rgba(220,38,38,0.3)]">
                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i> Lọc Phim
                </button>
            </div>
        </form>
    </div>

    <?php if (empty($movies)): ?>
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-24 h-24 bg-gray-900 rounded-full flex items-center justify-center mb-4 border border-gray-800">
                <i data-lucide="film" class="w-12 h-12 text-gray-600"></i>
            </div>
            <h2 class="text-xl font-medium text-gray-300 mb-2">Không tìm thấy phim nào</h2>
            <p class="text-gray-500">Danh mục này hiện tại chưa có bộ phim nào được cập nhật.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 md:gap-5">
            <?php foreach ($movies as $item): ?>
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($item['slug']) ?>" class="group block relative overflow-hidden rounded-lg bg-[#141414] border border-gray-800/50 hover:border-gray-600 transition-all duration-300">
                    <div class="aspect-[3/4] relative overflow-hidden">
                        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($item['poster_url']) ? $item['poster_url'] : ($item['thumb_url'] ?? ''))) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                        
                        <!-- Top Labels -->
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
                        
                        <!-- Play Icon Hover -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-12 h-12 bg-red-600/90 rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(220,38,38,0.5)] transform group-hover:scale-110 transition-transform">
                                <i data-lucide="play" class="w-5 h-5 text-white ml-1"></i>
                            </div>
                        </div>
                        
                        <!-- Episode count -->
                        <?php if (!empty($item['episode_current'])): ?>
                            <div class="absolute bottom-2 right-2">
                                <span class="bg-gray-900/80 backdrop-blur-sm text-gray-300 text-[11px] font-medium px-2 py-1 rounded shadow-lg border border-gray-700">
                                    <?= htmlspecialchars($item['episode_current']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <h3 class="text-white font-medium text-sm truncate group-hover:text-red-500 transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="text-gray-500 text-xs truncate mt-1"><?= htmlspecialchars($item['origin_name']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
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
