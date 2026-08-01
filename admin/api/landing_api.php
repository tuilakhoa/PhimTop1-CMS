<?php
session_start();
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$guidesDir = __DIR__ . '/../../cms_landing_page/guides/';

if (!is_dir($guidesDir)) {
    mkdir($guidesDir, 0755, true);
}

if ($action === 'list') {
    $files = array_diff(scandir($guidesDir), array('.', '..'));
    $guides = [];
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $guides[] = pathinfo($file, PATHINFO_FILENAME);
        }
    }
    echo json_encode(['status' => true, 'files' => array_values($guides)]);
    exit;
}

if ($action === 'read') {
    $slug = $_GET['slug'] ?? '';
    if (!$slug) {
        echo json_encode(['status' => false, 'message' => 'Missing slug']);
        exit;
    }
    
    $file = $guidesDir . basename($slug) . '.html';
    if (file_exists($file)) {
        echo json_encode(['status' => true, 'content' => file_get_contents($file)]);
    } else {
        echo json_encode(['status' => false, 'message' => 'File not found']);
    }
    exit;
}

if ($action === 'save') {
    $slug = $_POST['slug'] ?? '';
    $content = $_POST['content'] ?? '';
    
    // Validate slug (letters, numbers, hyphens)
    if (!preg_match('/^[a-zA-Z0-9\-]+$/', $slug)) {
        echo json_encode(['status' => false, 'message' => 'Slug không hợp lệ. Chỉ chấp nhận chữ, số, và dấu gạch ngang.']);
        exit;
    }
    
    $file = $guidesDir . basename($slug) . '.html';
    if (file_put_contents($file, $content) !== false) {
        echo json_encode(['status' => true]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Failed to save file']);
    }
    exit;
}

if ($action === 'delete') {
    $slug = $_GET['slug'] ?? '';
    if (!$slug) {
        echo json_encode(['status' => false, 'message' => 'Missing slug']);
        exit;
    }
    
    $file = $guidesDir . basename($slug) . '.html';
    if (file_exists($file) && unlink($file)) {
        echo json_encode(['status' => true]);
    } else {
        echo json_encode(['status' => false, 'message' => 'File not found or cannot be deleted']);
    }
    exit;
}

echo json_encode(['status' => false, 'message' => 'Invalid action']);
exit;
