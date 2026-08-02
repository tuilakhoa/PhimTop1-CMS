<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$settings = getSettings();
$movies = [];

if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $repo = getMovieRepository();
    $result = $repo->getMovies(1, 24, '');
    $movies = $result['items'];
} else {
    $apiResult = fetchApiFilms('home');
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
