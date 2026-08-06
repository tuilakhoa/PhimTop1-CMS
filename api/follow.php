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

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

if ($action === 'check') {
    $slug = $_GET['slug'] ?? '';
    if (!$slug) {
        echo json_encode(['status' => 'error', 'message' => 'Missing slug']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id FROM user_follows WHERE user_email = ? AND item_slug = ? LIMIT 1");
    $stmt->execute([$user['email'], $slug]);
    echo json_encode(['status' => 'success', 'is_following' => (bool)$stmt->fetch()]);
    exit;
}

if ($action === 'toggle') {
    $slug = $input['item_slug'] ?? '';
    $type = $input['item_type'] ?? 'movie';
    $name = $input['item_name'] ?? '';
    $thumb = $input['thumb_url'] ?? '';

    if (!$slug || !$name) {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM user_follows WHERE user_email = ? AND item_slug = ? LIMIT 1");
    $stmt->execute([$user['email'], $slug]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("DELETE FROM user_follows WHERE id = ?");
        $stmt->execute([$existing['id']]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_follows (user_email, item_slug, item_type, item_name, thumb_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user['email'], $slug, $type, $name, $thumb]);
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
