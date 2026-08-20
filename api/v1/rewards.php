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

$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');

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

$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
$user = verifyToken($token);

if (!$user) {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }
}

if (!$user) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'balance';
$pdo = getPDO();

if (!$pdo) {
    echo json_encode(['status' => 'success', 'coins' => 0]);
    exit;
}

if ($action === 'balance') {
    try {
        $stmt = $pdo->prepare("SELECT coins FROM members WHERE email = ?");
        $stmt->execute([$user['email']]);
        $u = $stmt->fetch();
        $coins = $u ? (int)$u['coins'] : 0;
        echo json_encode(['status' => 'success', 'coins' => $coins]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'success', 'coins' => 0]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
