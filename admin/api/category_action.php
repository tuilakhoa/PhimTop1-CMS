<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

$pdo = getPDO();
if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit;
}

$action = $_POST['action'] ?? '';
$slug = $_POST['slug'] ?? '';

if (empty($action) || empty($slug)) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa thành công']);
    } elseif ($action === 'toggle_block') {
        $stmt = $pdo->prepare("SELECT is_blocked FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cat) {
            $newStatus = empty($cat['is_blocked']) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE categories SET is_blocked = ? WHERE slug = ?");
            $stmt->execute([$newStatus, $slug]);
            $msg = $newStatus ? 'Đã chặn thành công' : 'Đã bỏ chặn';
            echo json_encode(['status' => 'success', 'message' => $msg, 'is_blocked' => $newStatus]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy mục này']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hành động không được hỗ trợ']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
}
