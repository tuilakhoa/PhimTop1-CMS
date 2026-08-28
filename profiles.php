<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /member.php');
    exit;
}

$settings = getSettings();
$theme = $settings['theme'] ?? 'dark';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);

if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
