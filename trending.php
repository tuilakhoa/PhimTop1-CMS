<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$settings = getSettings();
$movies = [];
$title = "Bảng Xếp Hạng Thịnh Hành";
$domain = '';
global $pageTitle, $pageDesc, $pageKeywords;
$siteName = $settings['siteName'] ?? 'PhimTop1';

$pageTitle = $title . ' - ' . $siteName;
$pageDesc = "Bảng xếp hạng những bộ phim được xem nhiều nhất tại $siteName.";

$limit = 24;
$offset = ($page - 1) * $limit;
$pdo = getPDO();

if ($pdo) {
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM movies WHERE view > 0");
    $stmtTotal->execute();
    $total = $stmtTotal->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE view > 0 ORDER BY view DESC LIMIT $limit OFFSET $offset");
    $stmt->execute();
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Nếu dùng firestore
    $repo = getMovieRepository();
    if ($repo->isFirestore()) {
        $fs = getFirestoreInstance();
        $allMovies = $fs->getAllDocuments('movies');
        $allMovies = array_filter($allMovies, function($m) {
            return ($m['view'] ?? 0) > 0;
        });
        usort($allMovies, function($a, $b) {
            $viewA = $a['view'] ?? 0;
            $viewB = $b['view'] ?? 0;
            return $viewB <=> $viewA;
        });
        $total = count($allMovies);
        $movies = array_slice($allMovies, $offset, $limit);
    } else {
        $total = 0;
        $movies = [];
    }
}

$totalPages = max(1, ceil($total / $limit));
$currentPage = $page;

$theme = $settings['theme'] ?? 'phimhayok';
$themeFile = __DIR__ . "/themes/{$theme}/trending.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/phimhayok/trending.php";
}
