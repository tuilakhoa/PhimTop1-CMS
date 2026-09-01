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
$clientApiKey = $_SERVER['HTTP_X_APP_API_KEY'] ?? ($headers['X-App-API-Key'] ?? ($headers['x-app-api-key'] ?? ($_GET['key'] ?? '')));

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

// Authenticate user
$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
$user = verifyToken($token);

// Fallback to PHP Session for Web Users
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

$enableContinueWatching = isset($settings['enableContinueWatching']) ? (int)$settings['enableContinueWatching'] : 1;
$action = $_GET['action'] ?? 'list';
$sessionProfileId = (isset($_SESSION['current_profile']) && isset($_SESSION['current_profile']['id'])) ? $_SESSION['current_profile']['id'] : 0;
$profileId = (int)($headers['X-Profile-Id'] ?? $_SERVER['HTTP_X_PROFILE_ID'] ?? $sessionProfileId);

if (!$enableContinueWatching) {
    if ($action === 'list') {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'Tính năng Xem Tiếp đang bị tắt.']);
    exit;
}

$pdo = getPDO();
if ($pdo) {
    try {
        $pdo->query("SELECT profile_id, thumb_url, episode_slug, current_time, duration FROM watch_history LIMIT 1");
    } catch (PDOException $e) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS watch_history (id INT AUTO_INCREMENT PRIMARY KEY, user_email VARCHAR(255) NOT NULL, movie_slug VARCHAR(255) NOT NULL, movie_name VARCHAR(255) NOT NULL, episode_name VARCHAR(255) NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
            $pdo->exec("ALTER TABLE watch_history ADD COLUMN profile_id INT DEFAULT 0");
            $pdo->exec("ALTER TABLE watch_history ADD COLUMN episode_slug VARCHAR(255)");
            $pdo->exec("ALTER TABLE watch_history ADD COLUMN thumb_url TEXT");
            $pdo->exec("ALTER TABLE watch_history ADD COLUMN current_time INT DEFAULT 0");
            $pdo->exec("ALTER TABLE watch_history ADD COLUMN duration INT DEFAULT 0");
            $pdo->exec("ALTER TABLE watch_history DROP INDEX user_movie");
            $pdo->exec("ALTER TABLE watch_history ADD UNIQUE KEY user_movie_profile (user_email, movie_slug, profile_id)");
        } catch (PDOException $ex) {}
    }
}

if ($action === 'list') {
    if (!$pdo) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    try {
        $pdo->exec("ALTER TABLE watch_history DROP INDEX user_movie");
        $pdo->exec("ALTER TABLE watch_history ADD UNIQUE KEY user_movie_profile (user_email, movie_slug, profile_id)");
    } catch (PDOException $e) {}

    try {
        $stmt = $pdo->prepare("SELECT * FROM watch_history WHERE user_email = ? AND profile_id = ? ORDER BY updated_at DESC LIMIT 100");
        $stmt->execute([$user['email'], $profileId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $items]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'success', 'data' => []]);
    }
    exit;
}

if ($action === 'add') {
    $input = json_decode(file_get_contents('php://input'), true);
    $slug = $input['movie_slug'] ?? '';
    $name = $input['movie_name'] ?? '';
    $episodeName = !empty($input['episode_name']) ? $input['episode_name'] : 'Tập 1';
    
    $thumb = $input['thumb_url'] ?? '';
    $episodeSlug = $input['episode_slug'] ?? '';
    $currentTime = (int)($input['current_time'] ?? 0);
    $duration = (int)($input['duration'] ?? 0);
    
    if (empty($slug) || empty($name)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing movie_slug or movie_name']);
        exit;
    }
    
    if (!$pdo) {
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, thumb_url, episode_slug, current_time, duration FROM watch_history WHERE user_email = ? AND movie_slug = ? AND profile_id = ?");
        $stmt->execute([$user['email'], $slug, $profileId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if (empty($thumb) && !empty($existing['thumb_url'])) {
                $thumb = $existing['thumb_url'];
            }
            
            // Preserve time if the app/web sends 0 (e.g. initial load) but it's the same episode
            if ($existing['episode_slug'] === $episodeSlug && $currentTime == 0) {
                $currentTime = (int)$existing['current_time'];
                if ($duration == 0) $duration = (int)$existing['duration'];
            }
            
            $stmt = $pdo->prepare("UPDATE watch_history SET episode_name = ?, episode_slug = ?, thumb_url = ?, current_time = ?, duration = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$episodeName, $episodeSlug, $thumb, $currentTime, $duration, $existing['id']]);
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO watch_history (user_email, movie_slug, movie_name, episode_name, episode_slug, thumb_url, current_time, duration, profile_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['email'], $slug, $name, $episodeName, $episodeSlug, $thumb, $currentTime, $duration, $profileId]);
            } catch (PDOException $e) {
                // In case of conflict, try updating again
                $stmt = $pdo->prepare("UPDATE watch_history SET episode_name = ?, episode_slug = ?, thumb_url = ?, current_time = ?, duration = ?, updated_at = CURRENT_TIMESTAMP WHERE user_email = ? AND movie_slug = ? AND profile_id = ?");
                $stmt->execute([$episodeName, $episodeSlug, $thumb, $currentTime, $duration, $user['email'], $slug, $profileId]);
            }
        }
        
        // Reward Logic: Watch to Earn
        $enableWatchReward = isset($settings['enable_watch_reward']) ? (int)$settings['enable_watch_reward'] : 1;
        if ($enableWatchReward) {
            $watchIntervalMins = isset($settings['watch_reward_interval']) ? (int)$settings['watch_reward_interval'] : 1;
            $watchRewardCoins = isset($settings['watch_reward_coins']) ? (int)$settings['watch_reward_coins'] : 1;
            $intervalSeconds = $watchIntervalMins * 60;
            
            try {
                $stmt = $pdo->prepare("SELECT coins, last_reward_time FROM members WHERE email = ?");
                $stmt->execute([$user['email']]);
                $u = $stmt->fetch();
                if ($u !== false) {
                    $lastReward = $u['last_reward_time'] ? strtotime($u['last_reward_time']) : 0;
                    if (time() - $lastReward >= $intervalSeconds) {
                        $stmt = $pdo->prepare("UPDATE members SET coins = COALESCE(coins, 0) + ?, last_reward_time = CURRENT_TIMESTAMP WHERE email = ?");
                        $stmt->execute([$watchRewardCoins, $user['email']]);
                    }
                }
            } catch (PDOException $e) {
                // Ignore if columns don't exist yet
            }
        }
    } catch (PDOException $e) {
        // Ignore errors if table doesn't exist etc.
    }
    
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'clear') {
    if (!$pdo) {
        echo json_encode(['status' => 'success', 'message' => 'History cleared']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM watch_history WHERE user_email = ? AND profile_id = ?");
        $stmt->execute([$user['email'], $profileId]);
    } catch (PDOException $e) {}
    
    echo json_encode(['status' => 'success', 'message' => 'History cleared']);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
