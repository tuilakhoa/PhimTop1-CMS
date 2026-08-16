<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-App-API-Key, X-Profile-Id');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

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

$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
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

$action = $_GET['action'] ?? 'list';
$pdo = getPDO();

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
    $stmt->execute([$user['email']]);
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Auto-create a default profile if none exists
    if (empty($profiles)) {
        $stmt = $pdo->prepare("INSERT INTO user_profiles (user_email, profile_name, is_kids_mode, avatar_url) VALUES (?, ?, 0, ?)");
        $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=random';
        $stmt->execute([$user['email'], 'Default', $defaultAvatar]);
        
        $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
        $stmt->execute([$user['email']]);
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['status' => 'success', 'data' => $profiles]);
    exit;
}

if ($action === 'create') {
    $input = json_decode(file_get_contents('php://input'), true);
    $profileName = $input['profile_name'] ?? '';
    $isKidsMode = isset($input['is_kids_mode']) ? (int)$input['is_kids_mode'] : 0;
    
    if (empty($profileName)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tên hồ sơ không được để trống']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_profiles WHERE user_email = ?");
    $stmt->execute([$user['email']]);
    if ($stmt->fetchColumn() >= 5) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Bạn chỉ có thể tạo tối đa 5 hồ sơ']);
        exit;
    }

    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($profileName) . '&background=random';
    $stmt = $pdo->prepare("INSERT INTO user_profiles (user_email, profile_name, is_kids_mode, avatar_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user['email'], $profileName, $isKidsMode, $avatarUrl]);
    
    echo json_encode(['status' => 'success', 'profile_id' => $pdo->lastInsertId()]);
    exit;
}

if ($action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $profileId = $input['profile_id'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_profiles WHERE user_email = ?");
    $stmt->execute([$user['email']]);
    if ($stmt->fetchColumn() <= 1) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Không thể xóa hồ sơ cuối cùng']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM user_profiles WHERE id = ? AND user_email = ?");
    $stmt->execute([$profileId, $user['email']]);
    
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'select') {
    // Web only - Sets session variable for the current profile
    $input = json_decode(file_get_contents('php://input'), true);
    $profileId = $input['profile_id'] ?? 0;

    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE id = ? AND user_email = ?");
    $stmt->execute([$profileId, $user['email']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION['current_profile'] = $profile;
        echo json_encode(['status' => 'success', 'profile' => $profile]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy hồ sơ']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
