<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$keyword = $_GET['keyword'] ?? '';
$settings = getSettings();
$movies = [];
$title = "Kết quả tìm kiếm cho: " . htmlspecialchars($keyword);
$domain = 'https://phimimg.com/';


    if ($keyword) {
        $apiResult = fetchApiFilms('search', '', 1, $keyword);
        if ($apiResult && !empty($apiResult['items'])) {
            $movies = $apiResult['items'];
            $domain = $apiResult['domain'];
        }
    }
$theme = $settings['theme'] ?? 'dark';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
?>
