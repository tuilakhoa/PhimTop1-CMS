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

$action = $_GET['action'] ?? 'list';
$pdo = getPDO();

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM watch_history WHERE user_email = ? ORDER BY updated_at DESC LIMIT 100");
    $stmt->execute([$user['email']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $items]);
    exit;
}

if ($action === 'add') {
    $input = json_decode(file_get_contents('php://input'), true);
    $slug = $input['movie_slug'] ?? '';
    $name = $input['movie_name'] ?? '';
    $episodeName = $input['episode_name'] ?? 'Tập 1';
    
    // We should also save the thumbnail to show in the list
    $thumb = $input['thumb_url'] ?? '';
    $episodeSlug = $input['episode_slug'] ?? '';
    $currentTime = (int)($input['current_time'] ?? 0);
    $duration = (int)($input['duration'] ?? 0);
    
    if (empty($slug) || empty($name)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing movie_slug or movie_name']);
        exit;
    }
    
    // We check if the column thumb_url exists, if not we ignore it
    // Wait, the schema in includes/db.php doesn't have thumb_url in watch_history!
    // "id, user_email, movie_slug, movie_name, episode_name, updated_at"
    
    // Check if it already exists to update episode_name and updated_at
    $stmt = $pdo->prepare("SELECT id FROM watch_history WHERE user_email = ? AND movie_slug = ?");
    $stmt->execute([$user['email'], $slug]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE watch_history SET episode_name = ?, episode_slug = ?, thumb_url = ?, current_time = ?, duration = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$episodeName, $episodeSlug, $thumb, $currentTime, $duration, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO watch_history (user_email, movie_slug, movie_name, episode_name, episode_slug, thumb_url, current_time, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user['email'], $slug, $name, $episodeName, $episodeSlug, $thumb, $currentTime, $duration]);
    }
    
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'clear') {
    $stmt = $pdo->prepare("DELETE FROM watch_history WHERE user_email = ?");
    $stmt->execute([$user['email']]);
    
    echo json_encode(['status' => 'success', 'message' => 'History cleared']);
    exit;
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
