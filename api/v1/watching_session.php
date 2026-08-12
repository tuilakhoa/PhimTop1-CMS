<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-App-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../includes/db.php';

$action = $_GET['action'] ?? 'heartbeat';

if ($action === 'heartbeat') {
    $settings = getSettings();
    if (isset($settings['enableWatchingSession']) && $settings['enableWatchingSession'] == 0) {
        echo json_encode(['status' => 'success', 'command' => null]);
        exit;
    }

    // Both App and Web use this
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $device_id = $data['device_id'] ?? '';
    if (empty($device_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing device_id']);
        exit;
    }

    $device_name = $data['device_name'] ?? 'Unknown Device';
    $platform = $data['platform'] ?? 'web';
    $movie_slug = $data['movie_slug'] ?? '';
    $movie_name = $data['movie_name'] ?? '';
    $episode_name = $data['episode_name'] ?? '';
    $user_name = $data['user_name'] ?? 'Guest';
    $is_logged_in = isset($data['is_logged_in']) ? (int)$data['is_logged_in'] : 0;
    $progress = isset($data['progress']) ? (int)$data['progress'] : 0;

    $trackAnonymous = isset($settings['trackAnonymousSession']) ? (int)$settings['trackAnonymousSession'] : 0;
    if ($is_logged_in === 0 && $trackAnonymous === 0) {
        // Ignored to save space
        echo json_encode(['status' => 'success', 'command' => null, 'ignored' => true]);
        exit;
    }

    $pdo = getPDO();
    $fs = getFirestore();
    
    if (!$pdo && !$fs) {
        echo json_encode(['status' => 'error', 'message' => 'DB Connection Failed']);
        exit;
    }
    
    // Auto cleanup old sessions with 1% probability to prevent DB growth
    if (rand(1, 100) === 1 && $pdo) {
        try {
            $pdo->query("DELETE FROM active_sessions WHERE last_seen < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        } catch(Exception $e) {}
    }

    $command = null;
    
    if ($fs) {
        $doc = $fs->getDocument('active_sessions', $device_id);
        if ($doc && !empty($doc['pending_command'])) {
            $command = $doc['pending_command'];
        }
        $dataToSave = [
            'device_id' => $device_id,
            'device_name' => $device_name,
            'platform' => $platform,
            'movie_slug' => $movie_slug,
            'movie_name' => $movie_name,
            'episode_name' => $episode_name,
            'user_name' => $user_name,
            'is_logged_in' => $is_logged_in,
            'progress' => $progress,
            'last_seen' => time(),
            'pending_command' => null
        ];
        $fs->setDocument('active_sessions', $device_id, $dataToSave);
    } else {
        $sql = "INSERT INTO active_sessions (device_id, device_name, platform, movie_slug, movie_name, episode_name, user_name, is_logged_in, progress, last_seen) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                device_name = VALUES(device_name), platform = VALUES(platform), movie_slug = VALUES(movie_slug), movie_name = VALUES(movie_name), 
                episode_name = VALUES(episode_name), user_name = VALUES(user_name), is_logged_in = VALUES(is_logged_in), progress = VALUES(progress), last_seen = NOW()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$device_id, $device_name, $platform, $movie_slug, $movie_name, $episode_name, $user_name, $is_logged_in, $progress]);

        $stmtCmd = $pdo->prepare("SELECT pending_command FROM active_sessions WHERE device_id = ?");
        $stmtCmd->execute([$device_id]);
        $row = $stmtCmd->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['pending_command'])) {
            $command = $row['pending_command'];
            $pdo->prepare("UPDATE active_sessions SET pending_command = NULL WHERE device_id = ?")->execute([$device_id]);
        }
    }

    echo json_encode(['status' => 'success', 'command' => $command]);
    exit;
}

// Admin actions below
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$isAdmin = isset($_SESSION['admin']) || (isset($_GET['key']) && $_GET['key'] === getSettings()['appApiKey']);

if (!$isAdmin) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($action === 'list') {
    $pdo = getPDO();
    $fs = getFirestore();
    if (!$pdo && !$fs) {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $sessions = [];
    if ($fs) {
        $all = $fs->getAllDocuments('active_sessions');
        $now = time();
        foreach ($all as $doc) {
            if (isset($doc['last_seen']) && ($now - $doc['last_seen']) < 30) {
                if (!isset($doc['device_id'])) $doc['device_id'] = $doc['_id'] ?? '';
                $sessions[] = $doc;
            } else if (isset($doc['last_seen']) && ($now - $doc['last_seen']) > 3600) {
                // Cleanup very old sessions
                $fs->deleteDocument('active_sessions', $doc['_id']);
            }
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM active_sessions WHERE last_seen > DATE_SUB(NOW(), INTERVAL 30 SECOND) ORDER BY device_id ASC");
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    usort($sessions, function($a, $b) {
        return strcmp($a['device_id'] ?? '', $b['device_id'] ?? '');
    });
    
    echo json_encode(['status' => 'success', 'data' => $sessions]);
    exit;
}

if ($action === 'command') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $device_id = $data['device_id'] ?? '';
    $command = $data['command'] ?? '';

    if (empty($device_id) || empty($command)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
        exit;
    }

    $pdo = getPDO();
    $fs = getFirestore();
    
    if ($fs) {
        $fs->setDocument('active_sessions', $device_id, ['pending_command' => $command]);
        echo json_encode(['status' => 'success']);
    } else if ($pdo) {
        $stmt = $pdo->prepare("UPDATE active_sessions SET pending_command = ? WHERE device_id = ?");
        $stmt->execute([$command, $device_id]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
