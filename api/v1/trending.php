<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';

$settings = getSettings();
$apiKey = $settings['appApiKey'] ?? '';

// Verify API Key
$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');

if (!empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;
$pdo = getPDO();

if ($pdo) {
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM movies WHERE view > 0");
    $stmtTotal->execute();
    $total = $stmtTotal->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE view > 0 ORDER BY view DESC LIMIT $limit OFFSET $offset");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $items = array_slice($allMovies, $offset, $limit);
    } else {
        $total = 0;
        $items = [];
    }
}

$data = [
    'items' => $items,
    'titlePage' => 'Bảng Xếp Hạng Thịnh Hành',
    'domain' => '', // Các ảnh trong DB đã được format đầy đủ link nếu từ KKPhim/Ophim
    'seoOnPage' => (object)[],
    'params' => (object)[],
    'pagination' => [
        'totalPages' => max(1, ceil($total / $limit)),
        'currentPage' => $page
    ]
];

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
