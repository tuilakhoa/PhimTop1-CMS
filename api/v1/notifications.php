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
    // Get notifications for this user OR system-wide notifications (where user_email IS NULL)
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_email = ? OR user_email IS NULL ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$user['email']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $items]);
    exit;
}

if ($action === 'mark_read') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notifId = $input['notification_id'] ?? 0;
    
    if ($notifId > 0) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_email = ? OR user_email IS NULL)");
        $stmt->execute([$notifId, $user['email']]);
    } else {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_email = ?");
        $stmt->execute([$user['email']]);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Marked as read']);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
