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
    
    if (isset($settings['allowRegistration']) && $settings['allowRegistration'] == 0) {
        header("Location: /register.php?error=" . urlencode("Đăng ký tài khoản mới hiện đang bị khóa bởi Quản trị viên."));
        exit;
    }

    if (!$name || !$email || !$password) {
        header("Location: /register.php?error=" . urlencode("Vui lòng điền đầy đủ thông tin."));
        exit;
    }
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header("Location: /register.php?error=" . urlencode("Email này đã được sử dụng."));
            exit;
        }
        
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $emailHash = md5(strtolower(trim($email)));
        $avatar = 'https://www.gravatar.com/avatar/' . $emailHash . '?d=robohash&s=200';
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
            header("Location: /register.php?error=" . urlencode("Có lỗi xảy ra khi tạo tài khoản."));
            exit;
        }
    } else {
        $config = getDbConfig();
        if ($config && isset($config['type']) && $config['type'] === 'firestore') {
            require_once __DIR__ . '/../includes/firestore_helper.php';
            $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
            $memberId = md5($email);
            if ($fs->getDocument('members', $memberId)) {
                header("Location: /register.php?error=" . urlencode("Email này đã được sử dụng."));
                exit;
            }
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $emailHash = md5(strtolower(trim($email)));
            $avatar = 'https://www.gravatar.com/avatar/' . $emailHash . '?d=robohash&s=200';
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
    
    if (isset($settings['allowLogin']) && $settings['allowLogin'] == 0) {
        header("Location: /login.php?error=" . urlencode("Đăng nhập tài khoản hiện đang bị khóa bởi Quản trị viên."));
        exit;
    }

    if (!$email || !$password) {
        header("Location: /login.php?error=" . urlencode("Vui lòng nhập email và mật khẩu."));
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
            header("Location: /login.php?error=" . urlencode("Tài khoản này được đăng ký bằng Google. Vui lòng sử dụng Đăng nhập bằng Google."));
            exit;
        } else {
            header("Location: /login.php?error=" . urlencode("Sai email hoặc mật khẩu."));
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
                header("Location: /login.php?error=" . urlencode("Tài khoản này được đăng ký bằng Google. Vui lòng sử dụng Đăng nhập bằng Google."));
                exit;
            } else {
                header("Location: /login.php?error=" . urlencode("Sai email hoặc mật khẩu."));
                exit;
            }
        }
    }
}


// --- Plugin Hooks for OAuth ---
do_action('api_auth', $action);

if ($action === 'generate_avatar' && isset($_SESSION['user'])) {
    $styles = ['identicon', 'monsterid', 'wavatar', 'retro', 'robohash'];
    $randomStyle = $styles[array_rand($styles)];
    $randomHash = md5(uniqid(rand(), true));
    $newAvatarUrl = "https://www.gravatar.com/avatar/{$randomHash}?d={$randomStyle}&s=200";
    
    if ($pdo) {
        $stmt = $pdo->prepare("UPDATE members SET avatar = ? WHERE email = ?");
        if ($stmt->execute([$newAvatarUrl, $_SESSION['user']['email']])) {
            $_SESSION['user']['avatar'] = $newAvatarUrl;
            echo json_encode(['status' => 'success', 'avatar_url' => $newAvatarUrl]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống']);
    exit;
}

if ($action === 'logout') {
    unset($_SESSION['user']);
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

// Fallback
header("Location: /");
exit;
