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

$pdo = getPDO();
$items = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT slug, name, type FROM categories ORDER BY name ASC");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $items = [];
    }
}

// Add a "Trang chủ" (Home) item as a static category if needed by app, 
// but typically the app can prepend it. We will just return the list.
echo json_encode([
    'status' => 'success',
    'data' => [
        'items' => $items,
        'titlePage' => 'Danh mục',
        'domain' => ''
    ]
]);
