<?php
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = $_SESSION['user'];
$pdo = getPDO();
if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

$action = $_GET['action'] ?? 'list';
$input = json_decode(file_get_contents('php://input'), true);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($action === 'list') {
        $stmt = $pdo->prepare("SELECT * FROM playlists WHERE user_email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch items for each playlist
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
            echo json_encode(['status' => 'error', 'message' => 'Missing slug']);
            exit;
        }

        // Find all playlist IDs owned by user that contain this movie
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
            echo json_encode(['status' => 'error', 'message' => 'Playlist ID required']);
            exit;
        }

        // Check ownership
        $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_email = ?");
        $stmt->execute([$id, $user['email']]);
        if (!$stmt->fetch()) {
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
            echo json_encode(['status' => 'error', 'message' => 'Missing data']);
            exit;
        }

        // Check ownership
        $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_email = ?");
        $stmt->execute([$playlist_id, $user['email']]);
        if (!$stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Not found or unauthorized']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO playlist_items (playlist_id, movie_slug, movie_name, thumb_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$playlist_id, $slug, $name, $thumb]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                echo json_encode(['status' => 'success', 'message' => 'Already in playlist']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
            }
        }
        exit;
    }

    if ($action === 'remove_item') {
        $playlist_id = $input['playlist_id'] ?? 0;
        $slug = $input['movie_slug'] ?? '';

        if (!$playlist_id || !$slug) {
            echo json_encode(['status' => 'error', 'message' => 'Missing data']);
            exit;
        }

        // Check ownership
        $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_email = ?");
        $stmt->execute([$playlist_id, $user['email']]);
        if (!$stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Not found or unauthorized']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM playlist_items WHERE playlist_id = ? AND movie_slug = ?");
        $stmt->execute([$playlist_id, $slug]);
        echo json_encode(['status' => 'success']);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
