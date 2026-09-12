<?php
require_once __DIR__ . '/includes/db.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
checkSetup();

$settings = getSettings();
global $pageTitle, $pageDesc;
$siteName = $settings['siteName'] ?? 'PhimTop1';
$pageTitle = 'Donate & Nâng cấp VIP - ' . $siteName;
$pageDesc = "Ủng hộ $siteName để duy trì server và tận hưởng đặc quyền VIP không quảng cáo.";

$theme = $settings['theme'] ?? 'phimhayok';
$themeFile = __DIR__ . "/themes/{$theme}/donate.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/donate.php";
}
