<?php
require_once __DIR__ . '/../../includes/db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$configFile = __DIR__ . '/../../config/crawl_sources.json';

function getSources() {
    global $configFile;
    if (file_exists($configFile)) {
        $data = json_decode(file_get_contents($configFile), true);
        if (is_array($data)) return $data;
    }
    return [];
}

function saveSources($sources) {
    global $configFile;
    return file_put_contents($configFile, json_encode($sources, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(['success' => true, 'sources' => getSources()]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $url = trim($data['url'] ?? '');
    
    if (empty($name) || empty($url)) {
        echo json_encode(['success' => false, 'message' => 'Tên và URL không được để trống']);
        exit;
    }
    
    $sources = getSources();
    $id = 'custom_' . time() . '_' . rand(100, 999);
    
    $sources[] = [
        'id' => $id,
        'name' => $name,
        'url' => $url
    ];
    
    if (saveSources($sources)) {
        echo json_encode(['success' => true, 'message' => 'Thêm nguồn thành công', 'sources' => $sources]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi lưu file']);
    }
    exit;
}

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }
    
    $sources = getSources();
    $newSources = [];
    foreach ($sources as $s) {
        if ($s['id'] !== $id) {
            $newSources[] = $s;
        }
    }
    
    if (saveSources($newSources)) {
        echo json_encode(['success' => true, 'message' => 'Xoá nguồn thành công', 'sources' => $newSources]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi lưu file']);
    }
    exit;
}
