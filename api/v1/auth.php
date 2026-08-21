<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-App-API-Key');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

$settings = getSettings();
$apiKey = $settings['appApiKey'] ?? '';

// Verify App API Key if set
$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
$isWebUser = isset($_SESSION['user']) || isset($_SESSION['admin']);

if (!$isWebUser && !empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

$action = $_GET['action'] ?? '';

// Helper to generate a simple token
function generateToken($userId, $email, $role) {
    global $jwtSecret;
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode(['user_id' => $userId, 'email' => $email, 'role' => $role, 'exp' => time() + 86400 * 30]); // 30 days
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $jwtSecret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
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

$pdo = getPDO();

if ($action === 'login') {
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
    
    // Very basic check, should use password_verify in production if passwords are hashed
    if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
        $token = generateToken($user['id'], $user['email'], $user['role']);
        echo json_encode([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'avatar' => $user['avatar'],
                'active_frame' => $user['active_frame_url'] ?? null
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }
    exit;
}

if ($action === 'register') {
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
            'role' => 'user'
        ]);
    } else if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO members (email, name, password, role) VALUES (?, ?, ?, 'user')");
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
}

// Ensure google_id column exists
if ($pdo) {
    try {
        $pdo->query("SELECT google_id FROM members LIMIT 1");
    } catch (PDOException $e) {
        try { $pdo->exec("ALTER TABLE members ADD COLUMN google_id VARCHAR(255) DEFAULT NULL"); } catch (PDOException $ex) {}
        try { $pdo->exec("ALTER TABLE members ADD UNIQUE KEY unique_google_id (google_id)"); } catch (PDOException $ex) {}
    }
}

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
            'avatar' => $avatar,
            'active_frame' => $user['active_frame_url'] ?? null
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

if ($action === 'profile') {
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    $payload = verifyToken($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
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
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    $payload = verifyToken($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
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

if ($action === 'forgot_password') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập email']);
        exit;
    }
    
    $fs = getFirestore();
    if ($fs) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng sử dụng tính năng quên mật khẩu của Firebase']);
        exit;
    }
    
    if ($pdo) {
        try {
            $pdo->exec("ALTER TABLE members ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
            $pdo->exec("ALTER TABLE members ADD COLUMN reset_expires DATETIME DEFAULT NULL");
        } catch (Exception $e) {}

        $stmt = $pdo->prepare("SELECT id, name FROM members WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            
            $stmt = $pdo->prepare("UPDATE members SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);
            
            if (!file_exists(__DIR__ . '/../../vendor/autoload.php')) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Chưa cài đặt PHPMailer. Vui lòng chạy composer install.']);
                exit;
            }
            require_once __DIR__ . '/../../vendor/autoload.php';
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                if (!empty($settings['smtpHost'])) {
                    $mail->isSMTP();
                    $mail->Host       = $settings['smtpHost'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $settings['smtpUser'];
                    $mail->Password   = $settings['smtpPass'];
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $settings['smtpPort'];
                } else {
                    $mail->isMail();
                }
                $mail->CharSet = 'UTF-8';
                $fromEmail = !empty($settings['smtpUser']) ? $settings['smtpUser'] : 'no-reply@' . $_SERVER['HTTP_HOST'];
                $mail->setFrom($fromEmail, $settings['siteName']);
                $mail->addAddress($email, $user['name']);
                
                $mail->isHTML(true);
                $mail->Subject = 'Yêu cầu đặt lại mật khẩu - ' . $settings['siteName'];
                
                $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/reset_password.php?token=" . $token;
                
                $mail->Body    = "Chào {$user['name']},<br><br>Bạn đã yêu cầu đặt lại mật khẩu. Vui lòng click vào link sau để đặt lại mật khẩu (có hiệu lực trong 1 giờ):<br><a href='{$resetLink}'>{$resetLink}</a><br><br>Nếu không phải bạn yêu cầu, vui lòng bỏ qua email này.";
                
                $mail->send();
                echo json_encode(['status' => 'success', 'message' => 'Email khôi phục đã được gửi. Kiểm tra hộp thư của bạn.']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => "Không thể gửi email: {$mail->ErrorInfo}"]);
            }
            exit;
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Nếu email tồn tại, email khôi phục đã được gửi.']);
            exit;
        }
    }
}

if ($action === 'reset_password') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? '';
    $newPassword = $input['password'] ?? '';
    
    if (empty($token) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Token và mật khẩu mới là bắt buộc']);
        exit;
    }
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE members SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            echo json_encode(['status' => 'success', 'message' => 'Đặt lại mật khẩu thành công.']);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Token không hợp lệ hoặc đã hết hạn.']);
        }
        exit;
    }
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
