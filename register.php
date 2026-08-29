<?php
session_start();
require_once __DIR__ . '/includes/db.php';
checkSetup();

$settings = getSettings();

if (isset($_SESSION['user'])) {
    header("Location: /");
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

$theme = $settings['theme'] ?? 'phimhayok';
$themeFile = __DIR__ . "/themes/{$theme}/register.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/register.php";
}
