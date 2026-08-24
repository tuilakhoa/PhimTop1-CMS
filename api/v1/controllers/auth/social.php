<?php
if ($action === 'firebase_login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $name = $input['name'] ?? 'User';
    $avatar = $input['avatar'] ?? '';
    $uid = $input['uid'] ?? '';
    
    if (empty($uid)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Firebase UID is required']);
        exit;
    }
    
    $fs = getFirestore();
    $userId = null;
    $userRole = 'user';
    $user = null;
    
    if ($fs) {
        $results = $fs->runQuery('members', 'google_id', 'EQUAL', $uid, 1);
        if (empty($results) && !empty($email)) {
            $results = $fs->runQuery('members', 'email', 'EQUAL', $email, 1);
        }
        
        if (!empty($results)) {
            $user = $results[0];
            $userId = $user['_id'];
            $userRole = $user['role'] ?? 'user';
            
            // Update avatar/name/google_id
            $fs->setDocument('members', $userId, array_merge($user, ['name' => $name, 'avatar' => $avatar, 'google_id' => $uid]));
        } else {
            // Register new user
            $userId = uniqid();
            $fs->setDocument('members', $userId, [
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar,
                'google_id' => $uid,
                'firebase_uid' => $uid,
                'role' => 'user'
            ]);
        }
    } else if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT m.id, m.role, f.image_url as active_frame_url FROM members m LEFT JOIN avatar_frames f ON m.active_frame_id = f.id WHERE m.google_id = ? OR (m.email = ? AND m.email != '') LIMIT 1");
            $stmt->execute([$uid, $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stmt = $pdo->prepare("SELECT id, role FROM members WHERE google_id = ? OR (email = ? AND email != '') LIMIT 1");
            $stmt->execute([$uid, $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($user) {
            $userId = $user['id'];
            $userRole = $user['role'] ?? 'user';
            $updateStmt = $pdo->prepare("UPDATE members SET name = ?, avatar = ?, google_id = ? WHERE id = ?");
            $updateStmt->execute([$name, $avatar, $uid, $userId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO members (email, name, avatar, role, google_id) VALUES (?, ?, ?, 'user', ?)");
            $stmt->execute([$email, $name, $avatar, $uid]);
            $userId = $pdo->lastInsertId();
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit;
    }
    
    $token = generateToken($userId, $email, $userRole);
    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'avatar' => getAbsoluteUrl($avatar),
            'active_frame' => getAbsoluteUrl($user['active_frame_url'] ?? null)
        ]
    ]);
    exit;
}

if ($action === 'link_google') {
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    $payload = verifyToken($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $uid = $input['uid'] ?? '';
    
    if (empty($uid)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Firebase UID is required']);
        exit;
    }
    
    $fs = getFirestore();
    if ($fs) {
        $user = $fs->getDocument('members', $payload['user_id']);
        if ($user) {
            $user['google_id'] = $uid;
            $fs->setDocument('members', $payload['user_id'], $user);
            echo json_encode(['status' => 'success', 'message' => 'Linked successfully']);
            exit;
        }
    } else if ($pdo) {
        // First check if this google_id is already linked to another account
        $stmt = $pdo->prepare("SELECT id FROM members WHERE google_id = ? AND id != ?");
        $stmt->execute([$uid, $payload['user_id']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Tài khoản Google này đã được liên kết với một tài khoản khác.']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE members SET google_id = ? WHERE id = ?");
        if ($stmt->execute([$uid, $payload['user_id']])) {
            echo json_encode(['status' => 'success', 'message' => 'Linked successfully']);
            exit;
        }
    }
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}
