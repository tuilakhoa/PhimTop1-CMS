<?php 
include __DIR__ . '/header.php'; 

// Fetch suggestions
$suggestions = [];
$sugDomain = 'https://otruyencdn.com/';
if (!empty($comic['category']) && is_array($comic['category'])) {
    $firstCat = is_array($comic['category'][0]) ? ($comic['category'][0]['slug'] ?? '') : '';
    if ($firstCat) {
        $sugRes = @file_get_contents("https://otruyenapi.com/v1/api/the-loai/" . urlencode($firstCat) . "?limit=12");
        if ($sugRes) {
            $sugData = json_decode($sugRes, true);
            if (isset($sugData['data']['items'])) {
                $suggestions = $sugData['data']['items'];
                $sugDomain = $sugData['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://otruyencdn.com/';
            }
        }
    }
}

// Fetch images for the current chapter
$chapterImages = [];
$chapterCdn = '';
if ($currentChapter && !empty($currentChapter['chapter_api_data'])) {
    $chRes = @file_get_contents($currentChapter['chapter_api_data']);
    if ($chRes) {
        $chData = json_decode($chRes, true);
        if (isset($chData['data']['item']['chapter_image'])) {
            $chapterImages = $chData['data']['item']['chapter_image'];
            $chapterCdn = $chData['data']['domain_cdn'] ?? 'https://sv1.otruyencdn.com';
            $chapterPath = $chData['data']['item']['chapter_path'] ?? '';
            
            // Format full image urls
            foreach ($chapterImages as &$img) {
                $img['full_url'] = rtrim($chapterCdn, '/') . '/' . trim($chapterPath, '/') . '/' . $img['image_file'];
            }
        }
    }
}
?>
<div class="bg-gray-950 min-h-screen">
    <!-- Sticky Navigation Bar (Top) -->
    <div class="sticky top-0 z-50 bg-gray-900/80 backdrop-blur-md border-b border-gray-800 shadow-lg">
        <div class="container mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-white truncate">
                    <a href="/<?= $settings["slugComic"] ?? "truyen" ?>/<?= urlencode($slug) ?>" class="hover:text-red-500 transition-colors">
                        <?= htmlspecialchars($comic['name']) ?>
                    </a>
                </h1>
                <h2 class="text-sm text-gray-400 truncate">Chương <?= htmlspecialchars($currentChapter['chapter_name'] ?? '') ?></h2>
            </div>
            
            <div class="flex items-center space-x-2 shrink-0">
                <!-- Previous Chapter Button -->
                <?php 
                $prevChap = null;
                $nextChap = null;
                if (!empty($chapters[0]['server_data'])) {
                    $allChaps = $chapters[0]['server_data'];
                    // Chaps are usually sorted descending from API (Chap 2, Chap 1) or ascending. 
                    // Let's assume OTruyen sends ascending or descending, we just find the index.
                    $currentIndex = -1;
                    foreach ($allChaps as $idx => $c) {
                        $cSlug = $c['slug'] ?? $c['chapter_name'];
                        if ($cSlug === $chap || $c['chapter_name'] === $chap) {
                            $currentIndex = $idx;
                            break;
                        }
                    }
                    if ($currentIndex !== -1) {
                        // Assuming index 0 is newest (descending)
                        $nextChap = $allChaps[$currentIndex - 1] ?? null;
                        $prevChap = $allChaps[$currentIndex + 1] ?? null;
                    }
                }
                ?>
                <a <?= $prevChap ? 'href="/'.($settings['slugRead']??'doc-truyen').'/'.urlencode($slug).'/'.urlencode($prevChap['slug'] ?? $prevChap['chapter_name']).'"' : 'disabled' ?> 
                   class="p-2 bg-gray-800 text-white rounded-lg <?= $prevChap ? 'hover:bg-red-600' : 'opacity-50 cursor-not-allowed' ?> transition-colors" title="Chương trước">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </a>
                
                <!-- Chapter Selector Dropdown -->
                <select onchange="window.location.href=this.value" class="bg-gray-800 text-white border border-gray-700 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block p-2 max-w-[150px] md:max-w-xs">
                    <?php foreach ($chapters[0]['server_data'] as $c): 
                        $cSlug = $c['slug'] ?? $c['chapter_name'];
                        $isSelected = ($cSlug === $chap || $c['chapter_name'] === $chap);
                    ?>
                        <option value="/<?= $settings['slugRead'] ?? 'doc-truyen' ?>/<?= urlencode($slug) ?>/<?= urlencode($cSlug) ?>" <?= $isSelected ? 'selected' : '' ?>>
                            Chương <?= htmlspecialchars($c['chapter_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Next Chapter Button -->
                <a <?= $nextChap ? 'href="/'.($settings['slugRead']??'doc-truyen').'/'.urlencode($slug).'/'.urlencode($nextChap['slug'] ?? $nextChap['chapter_name']).'"' : 'disabled' ?> 
                   class="p-2 bg-gray-800 text-white rounded-lg <?= $nextChap ? 'hover:bg-red-600' : 'opacity-50 cursor-not-allowed' ?> transition-colors" title="Chương sau">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Reader Area -->
    <div class="max-w-4xl mx-auto min-h-[50vh] flex flex-col items-center justify-center py-8">
        <?php if (empty($chapterImages)): ?>
            <div class="text-center text-gray-400 p-8">
                <i data-lucide="image-off" class="w-16 h-16 mx-auto mb-4 opacity-50"></i>
                <p class="text-xl">Nội dung chương này chưa có hoặc bị lỗi ảnh.</p>
            </div>
        <?php else: ?>
            <div class="w-full flex flex-col items-center comic-reader-container bg-black/20 pb-12">
                <?php foreach ($chapterImages as $index => $img): ?>
                    <img src="<?= htmlspecialchars($img['full_url']) ?>" 
                         alt="Page <?= $index + 1 ?>" 
                         loading="lazy" 
                         class="w-full h-auto max-w-full object-contain mb-[-1px] transition-opacity duration-500" 
                         style="background-color: #111;"
                         onerror="this.src='/assets/placeholder.jpg'; this.classList.add('opacity-50');">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Navigation Bottom -->
    <div class="max-w-4xl mx-auto px-4 py-8 flex justify-center space-x-4 border-t border-gray-800 mt-8">
        <a <?= $prevChap ? 'href="/'.($settings['slugRead']??'doc-truyen').'/'.urlencode($slug).'/'.urlencode($prevChap['slug'] ?? $prevChap['chapter_name']).'"' : 'disabled' ?> 
           class="px-6 py-3 bg-gray-800 text-white rounded-xl font-bold flex items-center <?= $prevChap ? 'hover:bg-red-600 shadow-lg' : 'opacity-50 cursor-not-allowed' ?> transition-all">
            <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i> Chương trước
        </a>
        <a <?= $nextChap ? 'href="/'.($settings['slugRead']??'doc-truyen').'/'.urlencode($slug).'/'.urlencode($nextChap['slug'] ?? $nextChap['chapter_name']).'"' : 'disabled' ?> 
           class="px-6 py-3 bg-red-600 text-white rounded-xl font-bold flex items-center <?= $nextChap ? 'hover:bg-red-700 shadow-lg shadow-red-600/30' : 'opacity-50 cursor-not-allowed' ?> transition-all">
            Chương sau <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
        </a>
    </div>

    <!-- Suggestions below reader -->
    <div class="container mx-auto px-4 py-12">
        <?php if (!empty($suggestions)): ?>
        <h3 class="text-2xl font-bold text-white mb-6 border-l-4 border-red-500 pl-3">Truyện Khác</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <?php foreach ($suggestions as $item): ?>
                <a href="/<?= $settings["slugComic"] ?? "truyen" ?>/<?= urlencode($item['slug']) ?>" class="group flex flex-col relative overflow-hidden rounded-xl bg-gray-800 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-500/20">
                    <div class="relative aspect-[2/3] w-full overflow-hidden">
                        <img src="<?= htmlspecialchars(strpos($item['thumb_url'], 'http') === 0 ? $item['thumb_url'] : rtrim($sugDomain, '/') . '/' . ltrim($item['thumb_url'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                    <div class="p-3 relative z-10 flex flex-col flex-grow">
                        <h3 class="text-sm font-semibold text-white line-clamp-1 mb-1 group-hover:text-red-400 transition-colors"><?= htmlspecialchars($item['name']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
