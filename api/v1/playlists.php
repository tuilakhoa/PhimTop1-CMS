<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-App-API-Key');
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

if (!$user) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'list';
$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'GET') {
    if ($action === 'list') {
        $stmt = $pdo->prepare("SELECT * FROM playlists WHERE user_email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($playlists as &$pl) {
            $stmtItems = $pdo->prepare("SELECT * FROM playlist_items WHERE playlist_id = ? ORDER BY created_at DESC");
            $stmtItems->execute([$pl['id']]);
            $pl['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['status' => 'success', 'data' => $playlists]);
        exit;
    }

    if ($action === 'check') {
        $slug = $_GET['slug'] ?? '';
        if (!$slug) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing slug']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT p.id 
            FROM playlists p
            JOIN playlist_items pi ON p.id = pi.playlist_id
            WHERE p.user_email = ? AND pi.movie_slug = ?
        ");
        $stmt->execute([$user['email'], $slug]);
        $playlist_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['status' => 'success', 'in_playlists' => $playlist_ids]);
        exit;
    }
}

if ($method === 'POST') {
    if ($action === 'create') {
        $name = $input['name'] ?? '';
        if (!$name) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Playlist name required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO playlists (user_email, name) VALUES (?, ?)");
        $stmt->execute([$user['email'], $name]);
        $id = $pdo->lastInsertId();

        echo json_encode(['status' => 'success', 'playlist_id' => $id]);
        exit;
    }

    if ($action === 'delete') {
        $id = $input['playlist_id'] ?? 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Playlist ID required']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_email = ?");
        $stmt->execute([$id, $user['email']]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Not found or unauthorized']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($action === 'add_item') {
        $playlist_id = $input['playlist_id'] ?? 0;
        $slug = $input['movie_slug'] ?? '';
        $name = $input['movie_name'] ?? '';
        $thumb = $input['thumb_url'] ?? '';

        if (!$playlist_id || !$slug || !$name) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing data']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_email = ?");
        $stmt->execute([$playlist_id, $user['email']]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Not found or unauthorized']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO playlist_items (playlist_id, movie_slug, movie_name, thumb_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$playlist_id, $slug, $name, $thumb]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'success', 'message' => 'Already in playlist']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
            }
        }
        exit;
    }

    if ($action === 'remove_item') {
        $playlist_id = $input['playlist_id'] ?? 0;
        $slug = $input['movie_slug'] ?? '';

        if (!$playlist_id || !$slug) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing data']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_email = ?");
        $stmt->execute([$playlist_id, $user['email']]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Not found or unauthorized']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM playlist_items WHERE playlist_id = ? AND movie_slug = ?");
        $stmt->execute([$playlist_id, $slug]);
        echo json_encode(['status' => 'success']);
        exit;
    }
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
