<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$slug = $_GET['slug'] ?? '';
$chap = $_GET['chap'] ?? '';

if (!$slug || !$chap) {
    header("Location: index.php");
    exit;
}

$originalSlug = resolveCustomSlug('comic', $slug);

$settings = getSettings();
$comic = null;
$chapters = [];
$currentChapter = null;
$domain = 'https://otruyencdn.com/';

$apiResult = fetchApiComicDetail($originalSlug);

if ($apiResult && $apiResult['comic']) {
    $comic = $apiResult['comic'];
    $chapters = $apiResult['chapters'];
    $domain = $apiResult['domain'];
    
    // Find current chapter by matching slug or name
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

// Apply SEO Overrides if they exist
$seoOverride = getSeoMetadata('comic_chapter', $originalSlug . '_' . $chap);
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
