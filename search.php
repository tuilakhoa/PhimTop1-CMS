<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$keyword = $_GET['keyword'] ?? '';
$settings = getSettings();
$movies = [];
$title = "Kết quả tìm kiếm cho: " . htmlspecialchars($keyword);
$domain = 'https://phimimg.com/';

if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $pdo = getPDO();
    if ($pdo && $keyword) {
        $search = "%$keyword%";
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE name LIKE ? OR origin_name LIKE ? LIMIT 50");
        $stmt->bindValue(1, $search, PDO::PARAM_STR);
        $stmt->bindValue(2, $search, PDO::PARAM_STR);
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    if ($keyword) {
        $apiResult = fetchApiFilms('search', '', 1, $keyword);
        if ($apiResult && !empty($apiResult['items'])) {
            $movies = $apiResult['items'];
            $domain = $apiResult['domain'];
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
?>
