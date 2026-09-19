<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/meilisearch.php';
checkSetup();

$keyword = $_GET['keyword'] ?? '';
$settings = getSettings();
$movies = [];
$title = "Kết quả tìm kiếm cho: " . htmlspecialchars($keyword);
$domain = 'https://phimimg.com/';

if ($keyword) {
    // 1. Try Meilisearch first (AI Search Engine)
    $meiliRes = searchMeilisearch($keyword, 36, 0);
    if ($meiliRes && !empty($meiliRes['hits'])) {
        foreach ($meiliRes['hits'] as $hit) {
            $movies[] = [
                'name' => $hit['name'],
                'slug' => $hit['id'] ?? $hit['slug'],
                'year' => $hit['year'],
                'origin_name' => $hit['origin_name'],
                'thumb_url' => $hit['thumb_url'],
                'type' => $hit['type']
            ];
        }
        $domain = ''; // Thumb URLs in Meilisearch might already be absolute or correct relative paths
    } else {
        // 2. Fallback to API if Meilisearch fails or has no results
        $apiResult = fetchApiFilms('search', '', 1, $keyword);
        if ($apiResult && !empty($apiResult['items'])) {
            $movies = $apiResult['items'];
            $domain = $apiResult['domain'];
        }
    }
}
$theme = $settings['theme'] ?? 'phimhayok';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/" . basename(__FILE__);
}
?>
