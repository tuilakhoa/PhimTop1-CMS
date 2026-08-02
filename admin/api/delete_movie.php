<?php
require_once __DIR__ . '/../../includes/db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $slug = $data['slug'] ?? '';
    if ($slug) {
        $repo = getMovieRepository();
        if ($repo->deleteMovie($slug)) {
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}
echo json_encode(['status' => 'error', 'message' => 'Lỗi xóa phim']);
