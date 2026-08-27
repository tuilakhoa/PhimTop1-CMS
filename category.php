<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$type = $_GET['type'] ?? '';
$slug = $_GET['slug'] ?? '';
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

// Resolve Custom Slugs
$originalType = $type;
$originalSlug = $slug;

if ($slug) {
    // If slug is present, it's a genre or country. We use the type as the SEO type (e.g. 'the-loai' or 'quoc-gia')
    $originalSlug = resolveCustomSlug($type, $slug);
} else if ($type) {
    // If no slug, it's a generic list (like phim-bo, phim-le). The $type itself is the identifier.
    $originalType = resolveCustomSlug('list', $type);
}

$settings = getSettings();
$movies = [];
$title = "Danh sách phim";
$domain = 'https://phimimg.com/';
global $pageTitle, $pageDesc, $pageKeywords;
$siteName = $settings['siteName'] ?? 'PhimTop1';

if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $repo = getMovieRepository();
    $limit = 24;
    
    if ($originalType) {
        $result = $repo->getMovies($page, $limit, '', $originalType);
        $movies = $result['items'];
        
        $title = "Danh sách: " . htmlspecialchars($originalType);
        $pageTitle = $title . ' - ' . $siteName;
    } else {
        $result = $repo->getMovies($page, $limit, '');
        $movies = $result['items'];
    }
} else {
    $apiType = in_array($originalType, ['the-loai', 'quoc-gia', 'danh-sach', 'nam-phat-hanh']) ? $originalType : 'danh-sach';
    
    // Convert to NguonC list format if necessary (phim-le, phim-bo, vv)
    $apiSlug = $originalSlug ?: $originalType;
    if (!$apiSlug) $apiSlug = 'phim-le';
    
    $filterCategory = $_GET['category'] ?? '';
    $filterCountry = $_GET['country'] ?? '';
    $filterYear = $_GET['year'] ?? '';
    $filterSort = $_GET['sort'] ?? '';
    
    $apiResult = fetchApiFilms($apiType, $apiSlug, $page, '', $filterCategory, $filterCountry, $filterYear, $filterSort);
    
    if ($apiResult) {
        if (!empty($apiResult['titlePage'])) {
            $title = $apiResult['titlePage'];
            $pageTitle = $title . ' - ' . $siteName;
        }
        
        $movies = $apiResult['items'];
        $domain = $apiResult['domain'];
        $seoOnPage = $apiResult['seoOnPage'] ?? [];
        
        if (!empty($seoOnPage['descriptionHead'])) {
            $pageDesc = $seoOnPage['descriptionHead'];
        } else {
            $pageDesc = "Danh sách phim $title tuyển chọn mới nhất tại $siteName.";
        }
        
        $totalPages = $apiResult['pagination']['totalPages'] ?? 1;
        $currentPage = $apiResult['pagination']['currentPage'] ?? $page;
    }
}

// Apply SEO Overrides if they exist
$seoType = $originalType;
$seoItem = $originalSlug ?: $originalType;
// Determine if it's generic list or specific genre/country
$seoCategoryType = $originalSlug ? $originalType : 'list'; 

$seoOverride = getSeoMetadata($seoCategoryType, $seoItem);
if ($seoOverride) {
    if (!empty($seoOverride['seo_title'])) $pageTitle = $seoOverride['seo_title'];
    if (!empty($seoOverride['seo_desc'])) $pageDesc = $seoOverride['seo_desc'];
    if (!empty($seoOverride['seo_keywords'])) $pageKeywords = $seoOverride['seo_keywords'];
}


$theme = $settings['theme'] ?? 'dark';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
