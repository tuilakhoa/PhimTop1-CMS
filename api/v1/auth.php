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

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$isWebUser = isset($_SESSION['user']) || isset($_SESSION['admin']);

if (!$isWebUser && !empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

$action = $_GET['action'] ?? '';

// Helper to generate a simple token
function generateToken($userId, $email, $role) {
    global $jwtSecret;
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode(['user_id' => $userId, 'email' => $email, 'role' => $role, 'exp' => time() + 86400 * 30]); // 30 days
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $jwtSecret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
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

$pdo = getPDO();

if ($action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    $user = null;
    $fs = getFirestore();
    if ($fs) {
        $results = $fs->runQuery('members', 'email', 'EQUAL', $email, 1);
        if (!empty($results)) {
            $user = $results[0];
            $user['id'] = $user['_id'];
        }
    } else if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT m.*, f.image_url as active_frame_url FROM members m LEFT JOIN avatar_frames f ON m.active_frame_id = f.id WHERE m.email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit;
    }
    
    // Very basic check, should use password_verify in production if passwords are hashed
    if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
        $token = generateToken($user['id'], $user['email'], $user['role']);
        echo json_encode([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'avatar' => $user['avatar'],
                'active_frame' => $user['active_frame_url'] ?? null
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }
    exit;
}

if ($action === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $name = $input['name'] ?? 'User';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        exit;
    }
    
    $fs = getFirestore();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $userId = null;
    
    if ($fs) {
        $results = $fs->runQuery('members', 'email', 'EQUAL', $email, 1);
        if (!empty($results)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit;
        }
        $userId = uniqid();
        $fs->setDocument('members', $userId, [
            'email' => $email,
            'name' => $name,
            'password' => $hashedPassword,
            'role' => 'user'
        ]);
    } else if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO members (email, name, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$email, $name, $hashedPassword]);
        $userId = $pdo->lastInsertId();
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit;
    }
    
    $token = generateToken($userId, $email, 'user');
    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'avatar' => null,
            'active_frame' => null
        ]
    ]);
    exit;
}

if ($action === 'firebase_login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $name = $input['name'] ?? 'User';
    $avatar = $input['avatar'] ?? '';
    $uid = $input['uid'] ?? '';
    
    if (empty($email) || empty($uid)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email and Firebase UID are required']);
        exit;
    }
    
    $fs = getFirestore();
    $userId = null;
    $userRole = 'user';
    
    if ($fs) {
        $results = $fs->runQuery('members', 'email', 'EQUAL', $email, 1);
        if (!empty($results)) {
            $user = $results[0];
            $userId = $user['_id'];
            $userRole = $user['role'] ?? 'user';
            
            // Update avatar/name
            $fs->setDocument('members', $userId, array_merge($user, ['name' => $name, 'avatar' => $avatar]));
        } else {
            // Register new user
            $userId = uniqid();
            $fs->setDocument('members', $userId, [
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar,
                'firebase_uid' => $uid,
                'role' => 'user'
            ]);
        }
    } else if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT m.id, m.role, f.image_url as active_frame_url FROM members m LEFT JOIN avatar_frames f ON m.active_frame_id = f.id WHERE m.email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stmt = $pdo->prepare("SELECT id, role FROM members WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($user) {
            $userId = $user['id'];
            $userRole = $user['role'] ?? 'user';
            $updateStmt = $pdo->prepare("UPDATE members SET name = ?, avatar = ? WHERE id = ?");
            $updateStmt->execute([$name, $avatar, $userId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO members (email, name, avatar, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$email, $name, $avatar]);
            $userId = $pdo->lastInsertId();
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit;
    }
    
    $token = generateToken($userId, $email, $userRole);
    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
            'active_frame' => $user['active_frame_url'] ?? null
        ]
    ]);
    exit;
}

if ($action === 'profile') {
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    $payload = verifyToken($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    $user = null;
    $fs = getFirestore();
    if ($fs) {
        $user = $fs->getDocument('members', $payload['user_id']);
        if ($user) $user['id'] = $payload['user_id'];
    } else if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT m.id, m.name, m.email, m.avatar, m.role, f.image_url as active_frame_url FROM members m LEFT JOIN avatar_frames f ON m.active_frame_id = f.id WHERE m.id = ?");
            $stmt->execute([$payload['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stmt = $pdo->prepare("SELECT id, name, email, avatar, role FROM members WHERE id = ?");
            $stmt->execute([$payload['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    if ($user) {
        echo json_encode([
            'status' => 'success',
            'user' => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
