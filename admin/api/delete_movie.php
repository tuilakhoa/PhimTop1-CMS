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
        $pdo = getPDO();
        if ($pdo) {
            $stmt = $pdo->prepare("DELETE FROM movies WHERE slug = ?");
            if ($stmt->execute([$slug])) {
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
    }
}
echo json_encode(['status' => 'error', 'message' => 'Lỗi xóa phim']);
