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

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$isWebUser = isset($_SESSION['user']) || isset($_SESSION['admin']);

if (!$isWebUser && !empty($apiKey) && $clientApiKey !== $apiKey) {
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
    if (!$pdo) {
        $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=random';
        echo json_encode(['status' => 'success', 'data' => [
            ['id' => 1, 'profile_name' => 'Default', 'avatar_url' => $defaultAvatar, 'is_kids_mode' => 0]
        ]]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
        $stmt->execute([$user['email']]);
    } catch (PDOException $e) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS user_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_email VARCHAR(255) NOT NULL,
            profile_name VARCHAR(255) NOT NULL,
            avatar_url TEXT,
            is_kids_mode TINYINT(1) DEFAULT 0,
            pin_code VARCHAR(10) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_email = ?");
        $stmt->execute([$user['email']]);
    }
    
    try {
        $pdo->exec("ALTER TABLE user_profiles ADD COLUMN pin_code VARCHAR(10) NULL");
    } catch (PDOException $e) {
        // ignore
    }

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

    foreach ($profiles as &$p) {
        $p['has_pin'] = !empty($p['pin_code']) ? 1 : 0;
        unset($p['pin_code']);
    }

    echo json_encode(['status' => 'success', 'data' => $profiles]);
    exit;
}

if ($action === 'create') {
    if (!$pdo) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Hệ thống Firestore chưa hỗ trợ tạo nhiều hồ sơ.']);
        exit;
    }

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
    if (!$pdo) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Hệ thống Firestore chưa hỗ trợ xóa hồ sơ.']);
        exit;
    }

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

if ($action === 'update') {
    if (!$pdo) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Hệ thống Firestore chưa hỗ trợ cập nhật hồ sơ.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $profileId = $input['profile_id'] ?? 0;
    $profileName = $input['profile_name'] ?? '';
    $avatarUrl = $input['avatar_url'] ?? '';
    
    if (empty($profileId) || empty($profileName)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Profile ID và tên không được để trống']);
        exit;
    }

    if (isset($input['pin_code'])) {
        $stmt = $pdo->prepare("UPDATE user_profiles SET profile_name = ?, avatar_url = ?, pin_code = ? WHERE id = ? AND user_email = ?");
        $stmt->execute([$profileName, $avatarUrl, $input['pin_code'], $profileId, $user['email']]);
    } else {
        $stmt = $pdo->prepare("UPDATE user_profiles SET profile_name = ?, avatar_url = ? WHERE id = ? AND user_email = ?");
        $stmt->execute([$profileName, $avatarUrl, $profileId, $user['email']]);
    }
    
    // Update session if it's the current profile
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    if (isset($_SESSION['current_profile']) && $_SESSION['current_profile']['id'] == $profileId) {
        $_SESSION['current_profile']['profile_name'] = $profileName;
        if (!empty($avatarUrl)) {
            $_SESSION['current_profile']['avatar_url'] = $avatarUrl;
        }
    }
    
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'select') {
    if (!$pdo) {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=random';
        $_SESSION['current_profile'] = ['id' => 1, 'profile_name' => 'Default', 'avatar_url' => $defaultAvatar, 'is_kids_mode' => 0];
        echo json_encode(['status' => 'success', 'profile' => $_SESSION['current_profile']]);
        exit;
    }

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

if ($action === 'verify_pin') {
    if (!$pdo) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Hệ thống Firestore chưa hỗ trợ.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $profileId = $input['profile_id'] ?? 0;
    $pinCode = $input['pin_code'] ?? '';

    $stmt = $pdo->prepare("SELECT pin_code FROM user_profiles WHERE id = ? AND user_email = ?");
    $stmt->execute([$profileId, $user['email']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile && $profile['pin_code'] === $pinCode) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Mã PIN không đúng']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
