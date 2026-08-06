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

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_email = ? OR user_email IS NULL ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$user['email']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $unreadCount = 0;
    foreach ($items as $item) {
        if ($item['is_read'] == 0) $unreadCount++;
    }
    
    echo json_encode(['status' => 'success', 'data' => $items, 'unread_count' => $unreadCount]);
    exit;
}

if ($action === 'mark_read') {
    $notifId = $input['notification_id'] ?? 0;
    
    if ($notifId > 0) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_email = ? OR user_email IS NULL)");
        $stmt->execute([$notifId, $user['email']]);
    } else {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_email = ?");
        $stmt->execute([$user['email']]);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Marked as read']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
