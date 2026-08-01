<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header("Location: index.php");
    exit;
}

$settings = getSettings();
$movie = null;
$episodes = [];
$domain = 'https://phimimg.com/';

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
        
        // Support multiple JSON structures (v1 vs v2 API)
        if ($data && isset($data['data']['item'])) {
            $movie = $data['data']['item'];
            $episodes = $movie['episodes'] ?? [];
            
            if (isset($data['data']['seoOnPage'])) {
                global $pageTitle, $pageDesc, $pageKeywords;
                $siteName = $settings['siteName'] ?? 'PhimTop1';
                $pageTitle = ($movie['name'] ?? $movie['title'] ?? '') . ' - ' . $siteName;
                
                $contentDesc = strip_tags(html_entity_decode($movie['content'] ?? ''));
                if (mb_strlen($contentDesc) > 160) {
                    $contentDesc = mb_substr($contentDesc, 0, 157) . '...';
                }
                
                $pageDesc = $contentDesc ?: ($data['data']['seoOnPage']['descriptionHead'] ?? null);
            }
            $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
        } elseif ($data && isset($data['movie'])) {
            $movie = $data['movie'];
            $episodes = $data['episodes'] ?? [];
            $domain = 'https://phimimg.com/';
        }
        
        if ($movie) {
            if (!empty($movie['thumb_url']) && !preg_match('/^http/', $movie['thumb_url'])) {
                $movie['thumb_url'] = rtrim($domain, '/') . '/' . ltrim($movie['thumb_url'], '/');
            }
            if (!empty($movie['poster_url']) && !preg_match('/^http/', $movie['poster_url'])) {
                $movie['poster_url'] = rtrim($domain, '/') . '/' . ltrim($movie['poster_url'], '/');
            }
        }
    }
}

$theme = $settings['theme'] ?? 'dark';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
