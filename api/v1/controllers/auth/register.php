<?php
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$name = $input['name'] ?? 'User';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

$fs = getFirestore();
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$userId = null;

if ($fs) {
    $results = $fs->runQuery('members', 'email', 'EQUAL', $email, 1);
    if (!empty($results)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
        exit;
    }
    $userId = uniqid();
    $fs->setDocument('members', $userId, [
        'email' => $email,
        'name' => $name,
        'password' => $hashedPassword,
        'role' => 'user',
        'login_method' => 'email'
    ]);
} else if ($pdo) {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
        exit;
    }
    
    try {
        $pdo->query("SELECT login_method FROM members LIMIT 1");
    } catch (PDOException $e) {
        try { $pdo->exec("ALTER TABLE members ADD COLUMN login_method VARCHAR(50) DEFAULT 'email'"); } catch (PDOException $ex) {}
    }
    
    $stmt = $pdo->prepare("INSERT INTO members (email, name, password, role, login_method) VALUES (?, ?, ?, 'user', 'email')");
    $stmt->execute([$email, $name, $hashedPassword]);
    $userId = $pdo->lastInsertId();
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

$token = generateToken($userId, $email, 'user');
echo json_encode([
    'status' => 'success',
    'token' => $token,
    'user' => [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'avatar' => null,
        'active_frame' => null
    ]
]);
exit;
