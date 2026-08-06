<?php
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$settings = getSettings();
$googleClientId = $settings['googleClientId'] ?? '';
$googleClientSecret = $settings['googleClientSecret'] ?? '';
$msClientId = $settings['msClientId'] ?? '';
$msClientSecret = $settings['msClientSecret'] ?? '';
$msTenantId = $settings['msTenantId'] ?? 'common';
$pdo = getPDO();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$enableGoogleLogin = !isset($settings['enableGoogleLogin']) || $settings['enableGoogleLogin'] == 1;
$enableMicrosoftLogin = !isset($settings['enableMicrosoftLogin']) || $settings['enableMicrosoftLogin'] == 1;

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


// --- Plugin Hooks for OAuth ---
do_action('api_auth', $action);

if ($action === 'logout') {
    unset($_SESSION['user']);
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

// Fallback
header("Location: /");
exit;
