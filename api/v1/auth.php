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

function getAbsoluteUrl($url) {
    if (empty($url) || preg_match('/^http/', $url)) return $url;
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    return $baseUrl . '/' . ltrim($url, '/');
}

$pdo = getPDO();

// Routing logic
switch ($action) {
    case 'login':
        require_once __DIR__ . '/controllers/auth/login.php';
        break;
    case 'register':
        require_once __DIR__ . '/controllers/auth/register.php';
        break;
    case 'profile':
    case 'update_avatar':
        require_once __DIR__ . '/controllers/auth/profile.php';
        break;
    case 'firebase_login':
    case 'link_google':
        // Ensure google_id column exists before doing social actions
        if ($pdo) {
            try {
                $pdo->query("SELECT google_id FROM members LIMIT 1");
            } catch (PDOException $e) {
                try { $pdo->exec("ALTER TABLE members ADD COLUMN google_id VARCHAR(255) DEFAULT NULL"); } catch (PDOException $ex) {}
                try { $pdo->exec("ALTER TABLE members ADD UNIQUE KEY unique_google_id (google_id)"); } catch (PDOException $ex) {}
            }
        }
        require_once __DIR__ . '/controllers/auth/social.php';
        break;
    case 'forgot_password':
    case 'reset_password':
        require_once __DIR__ . '/controllers/auth/password.php';
        break;
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
}
