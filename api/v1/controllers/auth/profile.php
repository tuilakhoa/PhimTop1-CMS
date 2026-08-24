<?php
$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

$payload = verifyToken($token);
if (!$payload) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($action === 'profile') {
    $user = null;
    $fs = getFirestore();
    if ($fs) {
        $user = $fs->getDocument('members', $payload['user_id']);
        if ($user) $user['id'] = $payload['user_id'];
    } else if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT m.id, m.name, m.email, m.avatar, m.role, f.image_url as active_frame_url FROM members m LEFT JOIN avatar_frames f ON m.active_frame_id = f.id WHERE m.id = ?");
            $stmt->execute([$payload['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stmt = $pdo->prepare("SELECT id, name, email, avatar, role FROM members WHERE id = ?");
            $stmt->execute([$payload['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    if ($user) {
        $user['avatar'] = getAbsoluteUrl($user['avatar']);
        $user['active_frame_url'] = getAbsoluteUrl($user['active_frame_url'] ?? null);
        echo json_encode([
            'status' => 'success',
            'user' => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
    exit;
}

if ($action === 'update_avatar') {
    $input = json_decode(file_get_contents('php://input'), true);
    $newAvatarUrl = $input['avatar_url'] ?? '';
    
    if (empty($newAvatarUrl)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing avatar_url']);
        exit;
    }
    
    if ($pdo) {
        $stmt = $pdo->prepare("UPDATE members SET avatar = ? WHERE id = ?");
        if ($stmt->execute([$newAvatarUrl, $payload['user_id']])) {
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}
