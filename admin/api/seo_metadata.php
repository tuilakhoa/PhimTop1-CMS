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

if ($method === 'GET') {
    // List all
    $stmt = $pdo->query("SELECT * FROM seo_metadata ORDER BY updated_at DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
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
        if (!empty($data['id'])) {
            // Update
            $stmt = $pdo->prepare("UPDATE seo_metadata SET type=?, item_id=?, custom_slug=?, seo_title=?, seo_desc=?, seo_keywords=? WHERE id=?");
            $stmt->execute([$type, $item_id, $custom_slug, $seo_title, $seo_desc, $seo_keywords, $data['id']]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO seo_metadata (type, item_id, custom_slug, seo_title, seo_desc, seo_keywords) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$type, $item_id, $custom_slug, $seo_title, $seo_desc, $seo_keywords]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['id'])) {
        $stmt = $pdo->prepare("DELETE FROM seo_metadata WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
}
