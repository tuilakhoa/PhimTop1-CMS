<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-App-API-Key');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$settings = getSettings();
$apiKey = $settings['appApiKey'] ?? '';

// Verify App API Key if set
$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');
if (!empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

function verifyToken($token) {
    global $jwtSecret;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $jwtSecret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    if (hash_equals($base64UrlSignature, $parts[2])) {
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        if (isset($payload['exp']) && $payload['exp'] >= time()) {
            return $payload;
        }
    }
    return null;
}

// Authenticate user
$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
$user = verifyToken($token);

if (!$user) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'list';
$pdo = getPDO();

if ($action === 'list') {
    $type = $_GET['type'] ?? 'movie';
    $stmt = $pdo->prepare("SELECT * FROM user_follows WHERE user_email = ? AND item_type = ? ORDER BY created_at DESC");
    $stmt->execute([$user['email'], $type]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $items]);
    exit;
}

if ($action === 'check') {
    $slug = $_GET['slug'] ?? '';
    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing slug']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM user_follows WHERE user_email = ? AND item_slug = ? LIMIT 1");
    $stmt->execute([$user['email'], $slug]);
    $isFollowing = $stmt->fetch() ? true : false;
    
    echo json_encode(['status' => 'success', 'is_following' => $isFollowing]);
    exit;
}

if ($action === 'toggle') {
    $input = json_decode(file_get_contents('php://input'), true);
    $slug = $input['item_slug'] ?? '';
    $type = $input['item_type'] ?? 'movie';
    $name = $input['item_name'] ?? '';
    $thumb = $input['thumb_url'] ?? '';
    
    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing item_slug']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM user_follows WHERE user_email = ? AND item_slug = ?");
    $stmt->execute([$user['email'], $slug]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Remove follow
        $stmt = $pdo->prepare("DELETE FROM user_follows WHERE id = ?");
        $stmt->execute([$existing['id']]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // Add follow
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing item_name']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO user_follows (user_email, item_slug, item_type, item_name, thumb_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user['email'], $slug, $type, $name, $thumb]);
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
