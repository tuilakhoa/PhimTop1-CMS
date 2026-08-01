<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$settings = getSettings();
$movies = [];

if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM movies ORDER BY updated_at DESC LIMIT 24");
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $res = @file_get_contents('https://phimapi.com/v1/api/home');
    if ($res) {
        $data = json_decode($res, true);
        if (isset($data['data']['items'])) {
            $movies = $data['data']['items'];
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
