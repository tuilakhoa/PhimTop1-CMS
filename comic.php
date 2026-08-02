<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header("Location: index.php");
    exit;
}

$originalSlug = resolveCustomSlug('comic', $slug);

$settings = getSettings();
$comic = null;
$chapters = [];
$domain = 'https://otruyencdn.com/';

$apiResult = fetchApiComicDetail($originalSlug);
if ($apiResult && $apiResult['comic']) {
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

// Apply SEO Overrides if they exist
$seoOverride = getSeoMetadata('comic', $originalSlug);
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
