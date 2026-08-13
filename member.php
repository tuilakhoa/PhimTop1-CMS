<?php
require_once __DIR__ . '/includes/db.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$settings = getSettings();
$theme = $settings['theme'] ?? 'dark';

$isLoggedIn = isset($_SESSION['user']);
$user = $isLoggedIn ? $_SESSION['user'] : null;

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$mode = $_GET['mode'] ?? 'login'; // login or register

// Fetch follows and playlists if logged in
$follows = [];
$playlists = [];
if ($isLoggedIn) {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM user_follows WHERE user_email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $follows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM playlists WHERE user_email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch items for each playlist
        foreach ($playlists as &$pl) {
            $stmtItems = $pdo->prepare("SELECT * FROM playlist_items WHERE playlist_id = ? ORDER BY created_at DESC");
            $stmtItems->execute([$pl['id']]);
            $pl['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

// Load theme specific layout
$themeMemberFile = __DIR__ . "/themes/{$theme}/member.php";
if (file_exists($themeMemberFile)) {
    require_once $themeMemberFile;
} else {
    // Fallback to dark theme if the theme doesn't have a member.php
    $fallbackFile = __DIR__ . "/themes/dark/member.php";
    if (file_exists($fallbackFile)) {
        require_once $fallbackFile;
    } else {
        die("Member template not found for theme: " . htmlspecialchars($theme));
    }
}
