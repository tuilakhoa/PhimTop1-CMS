<?php
session_start();
require_once __DIR__ . '/includes/db.php';
checkSetup();


$settings = getSettings();
$movies = [];


    $apiResult = fetchApiFilms('home');
    if ($apiResult && !empty($apiResult['items'])) {
        $movies = $apiResult['items'];
        $domain = $apiResult['domain'];
    }
$theme = $settings['theme'] ?? 'phimhayok';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/" . basename(__FILE__);
}
