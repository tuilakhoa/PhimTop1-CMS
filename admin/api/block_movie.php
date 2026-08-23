<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$slug = $data['slug'] ?? '';
$name = $data['name'] ?? '';

if (!$slug || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$repo = getMovieRepository();

if ($action === 'block') {
    // Delete from movies table if exists to free space
    $repo->deleteMovie($slug);
    
    // Add to blocked table
    if ($repo->blockMovie($slug, $name)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi database']);
    }
} else if ($action === 'restore') {
    if ($repo->restoreMovie($slug)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
