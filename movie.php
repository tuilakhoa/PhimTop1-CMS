<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header("Location: index.php");
    exit;
}

$originalSlug = resolveCustomSlug('movie', $slug);

$settings = getSettings();
$movie = null;
$episodes = [];
$domain = 'https://phimimg.com/';

if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $repo = getMovieRepository();
    $movie = $repo->getMovieBySlug($originalSlug);
    if ($movie) {
        if (!empty($movie['episodes_json'])) {
            $episodes = json_decode($movie['episodes_json'], true) ?: [];
        } else {
            $episodes = [['server_name' => 'VIP', 'server_data' => []]];
        }
        global $pageTitle, $pageDesc, $pageKeywords;
        $siteName = $settings['siteName'] ?? 'PhimTop1';
        $pageTitle = ($movie['name'] ?? '') . ' - ' . $siteName;
        
        $contentDesc = strip_tags(html_entity_decode($movie['content'] ?? ''));
        if (mb_strlen($contentDesc) > 160) {
            $contentDesc = mb_substr($contentDesc, 0, 157) . '...';
        }
        $pageDesc = $contentDesc;
    }
} else {
    $apiResult = fetchApiMovieDetail($originalSlug);
    if ($apiResult && $apiResult['movie']) {
        $movie = $apiResult['movie'];
        $episodes = $apiResult['episodes'];
        $domain = $apiResult['domain'];
        
        global $pageTitle, $pageDesc, $pageKeywords;
        $siteName = $settings['siteName'] ?? 'PhimTop1';
        $pageTitle = ($movie['name'] ?? $movie['title'] ?? '') . ' - ' . $siteName;
        
        $contentDesc = strip_tags(html_entity_decode($movie['content'] ?? ''));
        if (mb_strlen($contentDesc) > 160) {
            $contentDesc = mb_substr($contentDesc, 0, 157) . '...';
        }
        
        $pageDesc = $contentDesc ?: ($apiResult['seoOnPage']['descriptionHead'] ?? null);
    }
}

// Apply SEO Overrides if they exist
$seoOverride = getSeoMetadata('movie', $originalSlug);
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
