<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$slug = $_GET['slug'] ?? '';
$ep = $_GET['ep'] ?? '';

if (!$slug || !$ep) {
    header("Location: index.php");
    exit;
}

$settings = getSettings();
$movie = null;
$episodes = [];
$domain = 'https://phimimg.com/';

// Fetch Data (Same logic as movie.php)
if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE slug = ?");
        $stmt->execute([$slug]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($movie) {
            $episodes = [['server_name' => 'VIP', 'server_data' => []]];
        }
    }
} else {
    $ch = curl_init("https://phimapi.com/phim/" . urlencode($slug));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
    $res = curl_exec($ch);
    curl_close($ch);
    
    if ($res) {
        $data = json_decode($res, true);
        
        if ($data && isset($data['data']['item'])) {
            $movie = $data['data']['item'];
            $episodes = $movie['episodes'] ?? [];
            if (isset($data['data']['seoOnPage'])) {
                global $pageTitle, $pageDesc, $pageKeywords;
                $siteName = $settings['siteName'] ?? 'PhimTop1';
                $pageTitle = 'Xem Phim ' . ($movie['name'] ?? $movie['title'] ?? '') . ' Tập ' . htmlspecialchars($ep) . ' - ' . $siteName;
                
                $contentDesc = strip_tags(html_entity_decode($movie['content'] ?? ''));
                if (mb_strlen($contentDesc) > 160) {
                    $contentDesc = mb_substr($contentDesc, 0, 157) . '...';
                }
                $pageDesc = $contentDesc ?: ($data['data']['seoOnPage']['descriptionHead'] ?? null);
            }
        } elseif ($data && isset($data['movie'])) {
            $movie = $data['movie'];
            $episodes = $data['episodes'] ?? [];
            global $pageTitle;
            $pageTitle = 'Xem Phim ' . ($movie['name'] ?? '') . ' Tập ' . htmlspecialchars($ep);
        }
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
            $stmt = $pdo->prepare("INSERT INTO watch_history (user_email, movie_slug, movie_name, episode_name) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE episode_name = VALUES(episode_name), movie_name = VALUES(movie_name)");
            $stmt->execute([$_SESSION['user']['email'], $slug, $movie['name'] ?? $slug, $currentEp['name'] ?? $ep]);
        } catch (Exception $e) {}
    }
}

$videoUrl = $currentEp['link_m3u8'] ?? $currentEp['link_embed'] ?? '';
$isM3U8 = strpos($videoUrl, '.m3u8') !== false;
// If link_m3u8 is empty but link_embed exists, we use iframe.
// If link_m3u8 exists, we check if it actually contains .m3u8 to use HLS.js

$theme = $settings['theme'] ?? 'dark';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
