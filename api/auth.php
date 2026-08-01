<?php
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$settings = getSettings();
$googleClientId = $settings['googleClientId'] ?? '';
$googleClientSecret = $settings['googleClientSecret'] ?? '';
$pdo = getPDO();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- Native Login & Register ---
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$name || !$email || !$password) {
        header("Location: /member.php?mode=register&error=" . urlencode("Vui lòng điền đầy đủ thông tin."));
        exit;
    }
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header("Location: /member.php?mode=register&error=" . urlencode("Email này đã được sử dụng."));
            exit;
        }
        
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
        $insertStmt = $pdo->prepare("INSERT INTO members (email, name, password, avatar) VALUES (?, ?, ?, ?)");
        if ($insertStmt->execute([$email, $name, $hashed, $avatar])) {
            $_SESSION['user'] = [
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar
            ];
            header("Location: /");
            exit;
        } else {
            header("Location: /member.php?mode=register&error=" . urlencode("Có lỗi xảy ra khi tạo tài khoản."));
            exit;
        }
    } else {
        $config = getDbConfig();
        if ($config && isset($config['type']) && $config['type'] === 'firestore') {
            require_once __DIR__ . '/../includes/firestore_helper.php';
            $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
            $memberId = md5($email);
            if ($fs->getDocument('members', $memberId)) {
                header("Location: /member.php?mode=register&error=" . urlencode("Email này đã được sử dụng."));
                exit;
            }
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
            $fs->setDocument('members', $memberId, ['email' => $email, 'name' => $name, 'password' => $hashed, 'avatar' => $avatar, 'role' => 'user']);
            $_SESSION['user'] = ['email' => $email, 'name' => $name, 'avatar' => $avatar];
            header("Location: /");
            exit;
        }
    }
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$email || !$password) {
        header("Location: /member.php?mode=login&error=" . urlencode("Vui lòng nhập email và mật khẩu."));
        exit;
    }
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'email' => $user['email'],
                'name' => $user['name'],
                'avatar' => $user['avatar']
            ];
            if (($user['role'] ?? 'user') === 'admin') {
                $_SESSION['admin'] = $user['email'];
            }
            header("Location: /");
            exit;
        } else if ($user && empty($user['password'])) {
            header("Location: /member.php?mode=login&error=" . urlencode("Tài khoản này được đăng ký bằng Google. Vui lòng sử dụng Đăng nhập bằng Google."));
            exit;
        } else {
            header("Location: /member.php?mode=login&error=" . urlencode("Sai email hoặc mật khẩu."));
            exit;
        }
    } else {
        $config = getDbConfig();
        if ($config && isset($config['type']) && $config['type'] === 'firestore') {
            require_once __DIR__ . '/../includes/firestore_helper.php';
            $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
            $user = $fs->getDocument('members', md5($email));
            if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
                $_SESSION['user'] = ['email' => $user['email'], 'name' => $user['name'], 'avatar' => $user['avatar'] ?? ''];
                if (($user['role'] ?? 'user') === 'admin') $_SESSION['admin'] = $user['email'];
                header("Location: /");
                exit;
            } else if ($user && empty($user['password'])) {
                header("Location: /member.php?mode=login&error=" . urlencode("Tài khoản này được đăng ký bằng Google. Vui lòng sử dụng Đăng nhập bằng Google."));
                exit;
            } else {
                header("Location: /member.php?mode=login&error=" . urlencode("Sai email hoặc mật khẩu."));
                exit;
            }
        }
    }
}


// --- Google OAuth ---
$redirectUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/api/auth.php?action=google_callback';

if ($action === 'google_login') {
    if (!$googleClientId || !$googleClientSecret) {
        die('Google OAuth chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
    }
    $_SESSION['auth_referer'] = $_SERVER['HTTP_REFERER'] ?? '/';
    
    $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
        'response_type' => 'code',
        'client_id' => $googleClientId,
        'redirect_uri' => $redirectUri,
        'scope' => 'email profile',
        'prompt' => 'select_account'
    ]);
    header("Location: " . $authUrl);
    exit;
}

if ($action === 'google_callback' && isset($_GET['code'])) {
    if (!$googleClientId || !$googleClientSecret) {
        die('Google OAuth chưa được cấu hình.');
    }
    $code = $_GET['code'];
    
    // Exchange code for token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code' => $code,
        'client_id' => $googleClientId,
        'client_secret' => $googleClientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ]));
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($tokenResponse, true);
    if (isset($tokenData['access_token'])) {
        // Fetch user profile
        $ch2 = curl_init("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $tokenData['access_token']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $userResponse = curl_exec($ch2);
        curl_close($ch2);
        
        $userInfo = json_decode($userResponse, true);
        if (isset($userInfo['email'])) {
            $email = $userInfo['email'];
            $name = $userInfo['name'] ?? 'User';
            $avatar = $userInfo['picture'] ?? '';
            
            if ($pdo) {
                // Upsert into members table
                $stmt = $pdo->prepare("SELECT id, role FROM members WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user) {
                    $updateStmt = $pdo->prepare("UPDATE members SET name = ?, avatar = ? WHERE email = ?");
                    $updateStmt->execute([$name, $avatar, $email]);
                } else {
                    $insertStmt = $pdo->prepare("INSERT INTO members (email, name, avatar) VALUES (?, ?, ?)");
                    $insertStmt->execute([$email, $name, $avatar]);
                    $user = ['role' => 'user'];
                }
            } else {
                $config = getDbConfig();
                if ($config && isset($config['type']) && $config['type'] === 'firestore') {
                    require_once __DIR__ . '/../includes/firestore_helper.php';
                    $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
                    $memberId = md5($email);
                    $user = $fs->getDocument('members', $memberId);
                    if ($user) {
                        $user['name'] = $name; 
                        $user['avatar'] = $avatar;
                        $fs->setDocument('members', $memberId, $user);
                    } else {
                        $user = ['email' => $email, 'name' => $name, 'avatar' => $avatar, 'role' => 'user'];
                        $fs->setDocument('members', $memberId, $user);
                    }
                }
            }
            
            // Set session
            $_SESSION['user'] = [
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar
            ];
            if (!empty($user) && ($user['role'] ?? 'user') === 'admin') {
                $_SESSION['admin'] = $email;
            }
            
            $referer = $_SESSION['auth_referer'] ?? '/';
            unset($_SESSION['auth_referer']);
            header("Location: " . $referer);
            exit;
        } else {
            die('Không thể lấy thông tin email từ Google.');
        }
    } else {
        die('Lỗi xác thực Google OAuth (Token Error).');
    }
}

if ($action === 'logout') {
    unset($_SESSION['user']);
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

// Fallback
header("Location: /");
exit;
