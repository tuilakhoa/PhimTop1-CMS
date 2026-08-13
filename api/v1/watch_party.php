<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../includes/db.php';

$action = $_GET['action'] ?? '';
if (empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing action']);
    exit;
}

$pdo = getPDO();
if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'DB Connection Failed']);
    exit;
}

// Ensure sessions are cleaned up
try {
    $pdo->query("DELETE FROM watch_parties WHERE last_updated < DATE_SUB(NOW(), INTERVAL 24 HOUR) AND status = 'active'");
} catch(Exception $e) {}

// Check Admin
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$isAdmin = isset($_SESSION['admin']) || (isset($_GET['key']) && $_GET['key'] === getSettings()['appApiKey']);

// Admin Actions
if ($isAdmin) {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM watch_parties ORDER BY last_updated DESC");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $rooms]);
        exit;
    }

    if ($action === 'toggle_status') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;
        
        $room_code = $data['room_code'] ?? '';
        $new_status = $data['status'] ?? '';
        
        if ($room_code && in_array($new_status, ['active', 'disabled'])) {
            $stmt = $pdo->prepare("UPDATE watch_parties SET status = ? WHERE room_code = ?");
            $stmt->execute([$new_status, $room_code]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        }
        exit;
    }
}

// User Actions
if ($action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    
    $movie_slug = $data['movie_slug'] ?? '';
    $episode_name = $data['episode_name'] ?? '';
    $creator_name = $data['creator_name'] ?? 'Guest';
    $is_public = isset($data['is_public']) ? (int)$data['is_public'] : 0;
    
    if (empty($movie_slug) || empty($episode_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }
    
    $room_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO watch_parties (room_code, movie_slug, episode_name, creator_name, is_public) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$room_code, $movie_slug, $episode_name, $creator_name, $is_public]);
        echo json_encode(['status' => 'success', 'room_code' => $room_code]);
    } catch(Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create room']);
    }
    exit;
}

if ($action === 'list_public') {
    $movie_slug = $_GET['movie_slug'] ?? '';
    if (empty($movie_slug)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing movie_slug']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT room_code, episode_name, creator_name, `current_time` FROM watch_parties WHERE movie_slug = ? AND status = 'active' AND is_public = 1 ORDER BY last_updated DESC LIMIT 20");
    $stmt->execute([$movie_slug]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $rooms]);
    exit;
}

if ($action === 'join' || $action === 'state') {
    $room_code = $_GET['room_code'] ?? '';
    if (empty($room_code)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing room_code']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM watch_parties WHERE room_code = ?");
    $stmt->execute([$room_code]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$room) {
        echo json_encode(['status' => 'error', 'message' => 'Room not found']);
        exit;
    }
    
    if ($room['status'] !== 'active') {
        echo json_encode(['status' => 'error', 'message' => 'Room is disabled']);
        exit;
    }
    
    echo json_encode(['status' => 'success', 'data' => $room]);
    exit;
}

if ($action === 'sync') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    
    $room_code = $data['room_code'] ?? '';
    $is_playing = isset($data['is_playing']) ? (int)$data['is_playing'] : 0;
    $current_time = isset($data['current_time']) ? (int)$data['current_time'] : 0;
    
    if (empty($room_code)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing room_code']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE watch_parties SET is_playing = ?, `current_time` = ?, last_updated = NOW() WHERE room_code = ? AND status = 'active'");
    $stmt->execute([$is_playing, $current_time, $room_code]);
    
    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
