<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$slug = $_GET['slug'] ?? '';
$ep = $_GET['ep'] ?? '';
$displayEp = preg_replace('/^tap-/', '', $ep);

if (!$slug || !$ep) {
    header("Location: index.php");
    exit;
}

$originalSlug = resolveCustomSlug('movie', $slug); // Watch and Movie share the same custom slug mapping

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

// Fetch Data (Same logic as movie.php)
if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $repo = getMovieRepository();
    $movie = $repo->getMovieBySlug($originalSlug);
    if ($movie) {
        $episodes = [['server_name' => 'VIP', 'server_data' => []]];
        
        global $pageTitle, $pageDesc, $pageKeywords;
        $siteName = $settings['siteName'] ?? 'PhimTop1';
        $pageTitle = 'Xem Phim ' . ($movie['name'] ?? '') . ' Tập ' . htmlspecialchars($displayEp) . ' - ' . $siteName;
        
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
        
        global $pageTitle, $pageDesc, $pageKeywords;
        $siteName = $settings['siteName'] ?? 'PhimTop1';
        $pageTitle = 'Xem Phim ' . ($movie['name'] ?? $movie['title'] ?? '') . ' Tập ' . htmlspecialchars($displayEp) . ' - ' . $siteName;
        
        $contentDesc = strip_tags(html_entity_decode($movie['content'] ?? ''));
        if (mb_strlen($contentDesc) > 160) {
            $contentDesc = mb_substr($contentDesc, 0, 157) . '...';
        }
        $pageDesc = $contentDesc ?: ($apiResult['seoOnPage']['descriptionHead'] ?? null);
    }
}

if (!$movie) {
    die("Phim không tồn tại.");
}

$currentEp = null;
if (!empty($episodes)) {
    foreach ($episodes[0]['server_data'] as $e) {
        if ($e['slug'] === $ep) {
            $currentEp = $e;
            break;
        }
    }
}

if (!$currentEp) {
    die("Tập phim không tồn tại.");
}

// Ghi nhận lịch sử xem phim
if (isset($_SESSION['user']) && !empty($_SESSION['user']['email'])) {
    $pdo = getPDO();
    if ($pdo) {
        try {
            $pdo->query("SELECT profile_id FROM watch_history LIMIT 1");
        } catch (PDOException $e) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS watch_history (id INT AUTO_INCREMENT PRIMARY KEY, user_email VARCHAR(255) NOT NULL, movie_slug VARCHAR(255) NOT NULL, movie_name VARCHAR(255) NOT NULL, episode_name VARCHAR(255) NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
                $pdo->exec("ALTER TABLE watch_history ADD COLUMN profile_id INT DEFAULT 0");
                $pdo->exec("ALTER TABLE watch_history ADD COLUMN episode_slug VARCHAR(255)");
                $pdo->exec("ALTER TABLE watch_history ADD COLUMN thumb_url TEXT");
                $pdo->exec("ALTER TABLE watch_history ADD COLUMN current_time INT DEFAULT 0");
                $pdo->exec("ALTER TABLE watch_history ADD COLUMN duration INT DEFAULT 0");
                $pdo->exec("ALTER TABLE watch_history DROP INDEX user_movie");
                $pdo->exec("ALTER TABLE watch_history ADD UNIQUE KEY user_movie_profile (user_email, movie_slug, profile_id)");
            } catch (PDOException $ex) {}
        }
        
        try {
            $profileId = isset($_SESSION['current_profile']) ? (int)$_SESSION['current_profile']['id'] : 0;
            $stmt = $pdo->prepare("SELECT id FROM watch_history WHERE user_email = ? AND movie_slug = ? AND profile_id = ?");
            $stmt->execute([$_SESSION['user']['email'], $slug, $profileId]);
            $existing = $stmt->fetch();
            
            $thumbUrl = $movie['thumb_url'] ?? $movie['poster_url'] ?? '';
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE watch_history SET episode_name = ?, episode_slug = ?, thumb_url = ?, movie_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$currentEp['name'] ?? $ep, $ep, $thumbUrl, $movie['name'] ?? $slug, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO watch_history (user_email, movie_slug, movie_name, episode_name, episode_slug, thumb_url, profile_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user']['email'], $slug, $movie['name'] ?? $slug, $currentEp['name'] ?? $ep, $ep, $thumbUrl, $profileId]);
            }
        } catch (Exception $e) {}
    }
}

$videoUrl = $currentEp['link_m3u8'] ?? $currentEp['link_embed'] ?? '';
$isM3U8 = strpos($videoUrl, '.m3u8') !== false;
// If link_m3u8 is empty but link_embed exists, we use iframe.
// If link_m3u8 exists, we check if it actually contains .m3u8 to use HLS.js

// Apply SEO Overrides if they exist (using type 'watch' or fallback to 'movie')
$seoOverride = getSeoMetadata('watch', $originalSlug);
if (!$seoOverride) {
    // If no specific watch page SEO, fallback to movie page SEO
    $seoOverride = getSeoMetadata('movie', $originalSlug);
}

if ($seoOverride) {
    if (!empty($seoOverride['seo_title'])) {
        // Allow dynamic injection of episode number if {ep} is in the string
        $pageTitle = str_replace('{ep}', htmlspecialchars($displayEp), $seoOverride['seo_title']);
    }
    if (!empty($seoOverride['seo_desc'])) {
        $pageDesc = str_replace('{ep}', htmlspecialchars($displayEp), $seoOverride['seo_desc']);
    }
    if (!empty($seoOverride['seo_keywords'])) {
        $pageKeywords = str_replace('{ep}', htmlspecialchars($displayEp), $seoOverride['seo_keywords']);
    }
}

$theme = $settings['theme'] ?? 'dark';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
