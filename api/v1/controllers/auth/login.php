<?php
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

$user = null;
$fs = getFirestore();
if ($fs) {
    $results = $fs->runQuery('members', 'email', 'EQUAL', $email, 1);
    if (!empty($results)) {
        $user = $results[0];
        $user['id'] = $user['_id'];
    }
} else if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT m.*, f.image_url as active_frame_url FROM members m LEFT JOIN avatar_frames f ON m.active_frame_id = f.id WHERE m.email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

$login_method = $input['login_method'] ?? 'email';

if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
    // Update login_method
    if ($fs) {
        $user['login_method'] = $login_method;
        $fs->setDocument('members', $user['id'], $user);
    } else if ($pdo) {
        try {
            $pdo->query("SELECT login_method FROM members LIMIT 1");
        } catch (PDOException $e) {
            try { $pdo->exec("ALTER TABLE members ADD COLUMN login_method VARCHAR(50) DEFAULT 'email'"); } catch (PDOException $ex) {}
        }
        try {
            $stmt = $pdo->prepare("UPDATE members SET login_method = ? WHERE id = ?");
            $stmt->execute([$login_method, $user['id']]);
        } catch (Throwable $e) {}
    }

    $token = generateToken($user['id'], $user['email'], $user['role']);
    echo json_encode([
        'status' => 'success',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => getAbsoluteUrl($user['avatar']),
            'active_frame' => getAbsoluteUrl($user['active_frame_url'] ?? null)
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
}
exit;
