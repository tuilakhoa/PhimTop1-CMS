<?php
// Tên Plugin: Truyện Tranh (OTruyen)
// Phiên bản: 1.0.0

add_filter('admin_menu_groups', function($groups) {
    if (!isset($groups['Quản Lý Nội Dung'])) {
        $groups['Quản Lý Nội Dung'] = [];
    }
    
    // Bổ sung menu Truyện Tranh vào sau "Quản Lý Phim"
    $newGroups = [];
    foreach ($groups['Quản Lý Nội Dung'] as $key => $val) {
        $newGroups[$key] = $val;
        if ($key === 'movies') {
            $newGroups['plugin_comics'] = ['icon' => 'book-open', 'title' => 'Quản Lý Truyện'];
        }
    }
    
    if (!isset($newGroups['plugin_comics'])) {
        $newGroups['plugin_comics'] = ['icon' => 'book-open', 'title' => 'Quản Lý Truyện'];
    }
    
    $groups['Quản Lý Nội Dung'] = $newGroups;
    return $groups;
});

add_filter('admin_page_file', function($file, $page) {
    if ($page === 'plugin_comics') {
        return __DIR__ . '/admin_page.php';
    }
    return $file;
});

// Chèn menu Truyện Tranh vào Desktop Header
add_action('theme_header_menu', function() {
    $settings = getSettings();
    $slugComicList = $settings["slugComicList"] ?? "danh-sach-truyen";
    
    if (($settings['theme'] ?? 'dark') === 'phimhayok') {
        echo '<a href="/' . $slugComicList . '" class="hover:text-white flex items-center transition-colors"><i data-lucide="book-open" class="w-4 h-4 mr-1.5"></i> Truyện tranh</a>';
    } else {
        echo '<a href="/' . $slugComicList . '" class="text-gray-300 hover:text-white transition-colors">Truyện Tranh</a>';
    }
});

// Chèn menu Truyện Tranh vào Mobile Header
add_action('theme_mobile_menu', function() {
    $settings = getSettings();
    $slugComicList = $settings["slugComicList"] ?? "danh-sach-truyen";
    echo '<a href="/' . $slugComicList . '" class="hover:text-white">Truyện Tranh</a>';
});

// Xử lý trang Chi Tiết Truyện (Thay thế root/comic.php)
add_filter('render_comic_page', function($handled, $slug) {
    if (!$slug) return false;
    
    $originalSlug = resolveCustomSlug('comic', $slug);
    $settings = getSettings();
    
    $comic = null;
    $chapters = [];
    $domain = 'https://otruyencdn.com/';
    
    $apiResult = fetchApiComicDetail($originalSlug);
    if ($apiResult && !empty($apiResult['comic'])) {
        $comic = $apiResult['comic'];
        $chapters = $apiResult['chapters'];
        $domain = $apiResult['domain'];
        
        global $pageTitle, $pageDesc, $pageKeywords;
        $siteName = $settings['siteName'] ?? 'PhimTop1';
        $pageTitle = ($comic['name'] ?? $comic['title'] ?? '') . ' - Đọc Truyện Tranh Tại ' . $siteName;
        
        $contentDesc = strip_tags(html_entity_decode($comic['content'] ?? ''));
        if (mb_strlen($contentDesc) > 160) {
            $contentDesc = mb_substr($contentDesc, 0, 157) . '...';
        }
        $pageDesc = $contentDesc ?: ($apiResult['seoOnPage']['descriptionHead'] ?? null);
    }
    
    $seoOverride = getSeoMetadata('comic', $originalSlug);
    if ($seoOverride) {
        if (!empty($seoOverride['seo_title'])) { global $pageTitle; $pageTitle = $seoOverride['seo_title']; }
        if (!empty($seoOverride['seo_desc'])) { global $pageDesc; $pageDesc = $seoOverride['seo_desc']; }
        if (!empty($seoOverride['seo_keywords'])) { global $pageKeywords; $pageKeywords = $seoOverride['seo_keywords']; }
    }
    
    $theme = $settings['theme'] ?? 'dark';
    $themeFile = __DIR__ . "/../../themes/{$theme}/comic.php";
    if (file_exists($themeFile)) {
        require $themeFile;
    } else {
        require __DIR__ . "/../../themes/dark/comic.php";
    }
    
    return true; // Báo hiệu hook đã xử lý
});

// Xử lý trang Đọc Truyện (Thay thế root/read.php)
add_filter('render_read_page', function($handled, $slug, $chap) {
    if (!$slug || !$chap) return false;
    
    $originalSlug = resolveCustomSlug('comic', $slug);
    $settings = getSettings();
    
    $comic = null;
    $chapters = [];
    $currentChapter = null;
    $domain = 'https://otruyencdn.com/';
    
    $apiResult = fetchApiComicDetail($originalSlug);
    if ($apiResult && !empty($apiResult['comic'])) {
        $comic = $apiResult['comic'];
        $chapters = $apiResult['chapters'];
        $domain = $apiResult['domain'];
        
        if (!empty($chapters[0]['server_data'])) {
            foreach ($chapters[0]['server_data'] as $c) {
                $cSlug = $c['slug'] ?? $c['chapter_name'];
                if ($cSlug === $chap || $c['chapter_name'] === $chap) {
                    $currentChapter = $c;
                    break;
                }
            }
        }
        
        global $pageTitle, $pageDesc, $pageKeywords;
        $siteName = $settings['siteName'] ?? 'PhimTop1';
        
        if ($currentChapter) {
            $pageTitle = ($comic['name'] ?? $comic['title'] ?? '') . ' - Chương ' . ($currentChapter['chapter_name'] ?? '') . ' - ' . $siteName;
        } else {
            $pageTitle = ($comic['name'] ?? $comic['title'] ?? '') . ' - ' . $siteName;
        }
        
        $contentDesc = strip_tags(html_entity_decode($comic['content'] ?? ''));
        if (mb_strlen($contentDesc) > 160) {
            $contentDesc = mb_substr($contentDesc, 0, 157) . '...';
        }
        $pageDesc = $contentDesc ?: ($apiResult['seoOnPage']['descriptionHead'] ?? null);
    }
    
    $seoOverride = getSeoMetadata('comic_chapter', $originalSlug . '_' . $chap);
    if ($seoOverride) {
        if (!empty($seoOverride['seo_title'])) { global $pageTitle; $pageTitle = $seoOverride['seo_title']; }
        if (!empty($seoOverride['seo_desc'])) { global $pageDesc; $pageDesc = $seoOverride['seo_desc']; }
        if (!empty($seoOverride['seo_keywords'])) { global $pageKeywords; $pageKeywords = $seoOverride['seo_keywords']; }
    }
    
    $theme = $settings['theme'] ?? 'dark';
    $themeFile = __DIR__ . "/../../themes/{$theme}/read.php";
    if (file_exists($themeFile)) {
        require $themeFile;
    } else {
        require __DIR__ . "/../../themes/dark/read.php";
    }
    
    return true;
});

// Xử lý trang Danh Sách Truyện (Thay thế root/danh-sach-truyen.php)
add_filter('render_comic_list', function($handled) {
    $settings = getSettings();
    $theme = $settings['theme'] ?? 'dark';
    $themeFile = __DIR__ . "/../../themes/{$theme}/danh-sach-truyen.php";
    
    if (file_exists($themeFile)) {
        require $themeFile;
    } else {
        require __DIR__ . "/../../themes/dark/danh-sach-truyen.php";
    }
    
    return true;
});
