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
$clientApiKey = $_SERVER['HTTP_X_APP_API_KEY'] ?? ($headers['X-App-API-Key'] ?? ($headers['x-app-api-key'] ?? ($_GET['key'] ?? '')));

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

$action = $_GET['action'] ?? 'list';
$pdo = getPDO();

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

// Check and run migrations if table doesn't exist
try {
    $pdo->query("SELECT active_frame_id, coins FROM members LIMIT 1");
} catch (PDOException $e) {
    // Ensure columns exist (fallback for MySQL versions that don't support IF NOT EXISTS for ADD COLUMN)
    try { $pdo->exec("ALTER TABLE members ADD COLUMN coins INT DEFAULT 0"); } catch (PDOException $ex) {}
    try { $pdo->exec("ALTER TABLE members ADD COLUMN last_reward_time TIMESTAMP NULL DEFAULT NULL"); } catch (PDOException $ex) {}
    try { $pdo->exec("ALTER TABLE members ADD COLUMN active_frame_id INT DEFAULT NULL"); } catch (PDOException $ex) {}
}

try {
    $pdo->query("SELECT 1 FROM avatar_frames LIMIT 1");
} catch (PDOException $e) {
    require_once __DIR__ . '/../../includes/migrate.php';
    runMigrations();
}

if ($action === 'list') {
    // List all frames and mark which ones the user owns
    try {
        $stmt = $pdo->prepare("SELECT f.*, 
            IF(uf.id IS NOT NULL, 1, 0) as is_owned,
            IF(u.active_frame_id = f.id, 1, 0) as is_active
            FROM avatar_frames f
            LEFT JOIN user_frames uf ON uf.frame_id = f.id AND uf.user_email = ?
            LEFT JOIN members u ON u.email = ?
            ORDER BY f.price ASC");
        $stmt->execute([$user['email'], $user['email']]);
        $frames = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        
        // Convert to boolean for JSON
        foreach ($frames as &$f) {
            $f['is_owned'] = $f['is_owned'] == 1;
            $f['is_active'] = $f['is_active'] == 1;
            $f['price'] = (int)$f['price'];
            if (!preg_match('/^http/', $f['image_url'])) {
                $f['image_url'] = $baseUrl . '/' . ltrim($f['image_url'], '/');
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $frames]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'buy') {
    $input = json_decode(file_get_contents('php://input'), true);
    $frameId = (int)($input['frame_id'] ?? 0);
    
    if (!$frameId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid frame_id']);
        exit;
    }
    
    try {
        // Get frame info
        $stmt = $pdo->prepare("SELECT price FROM avatar_frames WHERE id = ?");
        $stmt->execute([$frameId]);
        $frame = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$frame) {
            echo json_encode(['status' => 'error', 'message' => 'Frame not found']);
            exit;
        }
        
        // Check if already owned
        $stmt = $pdo->prepare("SELECT id FROM user_frames WHERE user_email = ? AND frame_id = ?");
        $stmt->execute([$user['email'], $frameId]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Bạn đã sở hữu khung này']);
            exit;
        }
        
        // Check user coins
        $stmt = $pdo->prepare("SELECT coins FROM members WHERE email = ?");
        $stmt->execute([$user['email']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        $coins = (int)($u['coins'] ?? 0);
        $price = (int)$frame['price'];
        
        if ($coins < $price) {
            echo json_encode(['status' => 'error', 'message' => "Không đủ Xu. Cần $price Xu."]);
            exit;
        }
        
        // Deduct coins and add frame (Transaction)
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE members SET coins = coins - ? WHERE email = ?");
        $stmt->execute([$price, $user['email']]);
        
        $stmt = $pdo->prepare("INSERT INTO user_frames (user_email, frame_id) VALUES (?, ?)");
        $stmt->execute([$user['email'], $frameId]);
        $pdo->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Mua thành công']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống']);
    }
    exit;
}

if ($action === 'equip') {
    $input = json_decode(file_get_contents('php://input'), true);
    $frameId = (int)($input['frame_id'] ?? 0);
    
    try {
        if ($frameId > 0) {
            // Check if user owns it
            $stmt = $pdo->prepare("SELECT id FROM user_frames WHERE user_email = ? AND frame_id = ?");
            $stmt->execute([$user['email'], $frameId]);
            if (!$stmt->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Bạn chưa sở hữu khung này']);
                exit;
            }
        } else {
            $frameId = null; // Unequip
        }
        
        $stmt = $pdo->prepare("UPDATE members SET active_frame_id = ? WHERE email = ?");
        $stmt->execute([$frameId, $user['email']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Trang bị thành công']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
