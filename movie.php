<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header("Location: index.php");
    exit;
}

$originalSlug = resolveCustomSlug('movie', $slug);

$repo = getMovieRepository();
if ($repo->isMovieBlocked($originalSlug)) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>Nội dung này không tồn tại hoặc đã bị gỡ bỏ.</p>";
    exit;
}

$settings = getSettings();
$movie = null;
$episodes = [];
$domain = 'https://phimimg.com/';


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


// Auto-select episode for inline playing on the detail page
$currentEp = null;
$videoUrl = '';
$isM3U8 = false;

if (!empty($episodes[0]['server_data'])) {
    $epToPlay = null;
    $epParam = $_GET['ep'] ?? '';
    
    // Check if user requested a specific episode via ?ep= parameter
    if ($epParam) {
        foreach ($episodes[0]['server_data'] as $e) {
            if ($e['slug'] === $epParam) {
                $epToPlay = $e;
                break;
            }
        }
    }
    
    // Check watch history
    if (!$epToPlay && isset($_SESSION['user']) && !empty($_SESSION['user']['email'])) {
        $pdo = getPDO();
        if ($pdo) {
            try {
                $profileId = isset($_SESSION['current_profile']) ? (int)$_SESSION['current_profile']['id'] : 0;
                $stmt = $pdo->prepare("SELECT episode_slug FROM watch_history WHERE user_email = ? AND movie_slug = ? AND profile_id = ? LIMIT 1");
                $stmt->execute([$_SESSION['user']['email'], $originalSlug, $profileId]);
                $row = $stmt->fetch();
                if ($row && !empty($row['episode_slug'])) {
                    foreach ($episodes[0]['server_data'] as $e) {
                        if ($e['slug'] === $row['episode_slug']) {
                            $epToPlay = $e;
                            break;
                        }
                    }
                }
            } catch (Exception $ex) {}
        }
    }
    
    // Fallback to first episode
    if (!$epToPlay) {
        $epToPlay = $episodes[0]['server_data'][0];
    }
    
    $currentEp = $epToPlay;
    $videoUrl = $currentEp['link_m3u8'] ?? $currentEp['link_embed'] ?? '';
    $isM3U8 = strpos($videoUrl, '.m3u8') !== false;
$theme = $settings['theme'] ?? 'dark';

$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
