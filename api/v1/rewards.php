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
        $stmt = $pdo->prepare("SELECT coins, last_checkin, checkin_streak FROM members WHERE email = ?");
        $stmt->execute([$user['email']]);
        $u = $stmt->fetch();
        $coins = $u ? (int)$u['coins'] : 0;
        $lastCheckin = $u ? $u['last_checkin'] : null;
        $checkinStreak = $u ? (int)$u['checkin_streak'] : 0;
        $isCheckedInToday = ($lastCheckin === date('Y-m-d'));
        echo json_encode(['status' => 'success', 'coins' => $coins, 'last_checkin' => $lastCheckin, 'checkin_streak' => $checkinStreak, 'is_checked_in_today' => $isCheckedInToday]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'success', 'coins' => 0, 'is_checked_in_today' => false]);
    }
    exit;
}

if ($action === 'checkin') {
    $enableCheckin = isset($settings['enable_daily_checkin']) ? (int)$settings['enable_daily_checkin'] : 1;
    if (!$enableCheckin) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tính năng điểm danh đang tắt.']);
        exit;
    }
    
    $dailyCoins = isset($settings['daily_checkin_coins']) ? (int)$settings['daily_checkin_coins'] : 20;
    $streakBonus = isset($settings['daily_checkin_streak_bonus']) ? (int)$settings['daily_checkin_streak_bonus'] : 50;
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    try {
        // Ensure columns exist
        try { $pdo->exec("ALTER TABLE members ADD COLUMN last_checkin DATE NULL DEFAULT NULL"); } catch (PDOException $e) {}
        try { $pdo->exec("ALTER TABLE members ADD COLUMN checkin_streak INT DEFAULT 0"); } catch (PDOException $e) {}
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT coins, last_checkin, checkin_streak FROM members WHERE email = ? FOR UPDATE");
        $stmt->execute([$user['email']]);
        $u = $stmt->fetch();
        
        if ($u) {
            if ($u['last_checkin'] === $today) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Bạn đã điểm danh hôm nay rồi!']);
                exit;
            }
            
            $streak = (int)$u['checkin_streak'];
            if ($u['last_checkin'] === $yesterday) {
                $streak++;
            } else {
                $streak = 1; // Reset streak
            }
            
            $reward = $dailyCoins;
            $message = "Bạn đã nhận được {$dailyCoins} Xu điểm danh!";
            
            // Check streak bonus (e.g., every 7 days)
            if ($streak > 0 && $streak % 7 === 0) {
                $reward += $streakBonus;
                $message = "Chuỗi {$streak} ngày! Bạn được nhận thêm {$streakBonus} Xu thưởng!";
            }
            
            $stmt = $pdo->prepare("UPDATE members SET coins = COALESCE(coins, 0) + ?, last_checkin = ?, checkin_streak = ? WHERE email = ?");
            $stmt->execute([$reward, $today, $streak, $user['email']]);
            $pdo->commit();
            
            echo json_encode([
                'status' => 'success', 
                'message' => $message,
                'reward' => $reward,
                'checkin_streak' => $streak
            ]);
        } else {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
