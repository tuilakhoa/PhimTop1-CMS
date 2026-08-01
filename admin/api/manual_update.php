<?php
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$path = $_POST['path'] ?? '';
$file = $_FILES['file'] ?? null;

if (empty($path) || !$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng cung cấp đường dẫn và file hợp lệ.']);
    exit;
}

$path = ltrim($path, '/');
if (strpos($path, '..') !== false) {
    echo json_encode(['success' => false, 'message' => 'Đường dẫn không hợp lệ.']);
    exit;
}

$rootDir = realpath(__DIR__ . '/../../');
$targetPath = $rootDir . '/' . $path;

// Ensure target directory exists
$targetDir = dirname($targetPath);
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo thư mục đích.']);
        exit;
    }
}

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => true, 'message' => 'Ghi đè file thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi di chuyển file tải lên. Vui lòng kiểm tra quyền (permissions).']);
}
