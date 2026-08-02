<?php
require_once __DIR__ . '/../../includes/db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$pdo = getPDO();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$repo = getSeoRepository();

if ($method === 'GET') {
    // List all
    $data = $repo->getAllSeoMetadata();
    echo json_encode(['success' => true, 'data' => $data]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $type = trim($data['type'] ?? '');
    $item_id = trim($data['item_id'] ?? '');
    $custom_slug = trim($data['custom_slug'] ?? '');
    $seo_title = trim($data['seo_title'] ?? '');
    $seo_desc = trim($data['seo_desc'] ?? '');
    $seo_keywords = trim($data['seo_keywords'] ?? '');

    if (!$type || !$item_id) {
        echo json_encode(['success' => false, 'message' => 'Loại trang và ID/Slug gốc là bắt buộc.']);
        exit;
    }

    if ($custom_slug === '') $custom_slug = null;

    try {
        $saveData = [
            'id' => $data['id'] ?? null,
            'type' => $type,
            'item_id' => $item_id,
            'custom_slug' => $custom_slug,
            'seo_title' => $seo_title,
            'seo_desc' => $seo_desc,
            'seo_keywords' => $seo_keywords
        ];
        $repo->saveSeoMetadata($saveData);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['id'])) {
        $repo->deleteSeoMetadata($data['id']);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
}
