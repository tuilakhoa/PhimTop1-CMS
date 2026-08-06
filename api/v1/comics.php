<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/db.php';

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

// Check if comics feature is enabled
$enableComics = $settings['enableComics'] ?? 0;
if (!$enableComics) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Comics feature is disabled']);
    exit;
}

$action = $_GET['action'] ?? 'list'; // 'list', 'detail'
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$slug = $_GET['slug'] ?? '';

if ($action === 'detail') {
    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing slug for comic detail']);
        exit;
    }
    $data = fetchApiComicDetail($slug);
} else {
    $type = $_GET['type'] ?? 'danh-sach';
    $data = fetchApiComics($type, $slug, $page);
}

if (!$data) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch comic data']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
