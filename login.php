<?php
require_once __DIR__ . '/includes/db.php';
session_start();
checkSetup();

$settings = getSettings();
$adminPath = $settings['adminPath'] ?? '/admin';

if (isset($_SESSION['admin'])) {
    header("Location: " . $adminPath);
    exit;
}

$error = '';
$googleClientId = $settings['googleClientId'] ?? '';
$googleClientSecret = $settings['googleClientSecret'] ?? '';
$msClientId = $settings['msClientId'] ?? '';
$msClientSecret = $settings['msClientSecret'] ?? '';
$msTenantId = $settings['msTenantId'] ?? 'common';

if (isset($_GET['action']) && $_GET['action'] === 'google_login' && $googleClientId) {
    $redirectUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=google_callback';
    $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?response_type=code&client_id={$googleClientId}&redirect_uri=" . urlencode($redirectUri) . "&scope=email%20profile";
    header("Location: " . $authUrl);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'google_callback' && isset($_GET['code'])) {
    $code = $_GET['code'];
    $redirectUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=google_callback';
    
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
        $ch2 = curl_init("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $tokenData['access_token']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $userResponse = curl_exec($ch2);
        curl_close($ch2);
        
        $userInfo = json_decode($userResponse, true);
        if (isset($userInfo['email'])) {
            $email = $userInfo['email'];
            $pdo = getPDO();
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT role, name, avatar FROM members WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
            } else {
                $config = getDbConfig();
                if ($config && isset($config['type']) && $config['type'] === 'firestore') {
                    require_once __DIR__ . '/includes/firestore_helper.php';
                    $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
                    $user = $fs->getDocument('members', md5($email));
                } else {
                    $user = null;
                }
            }

            if ($user && ($user['role'] ?? 'user') === 'admin') {
                $_SESSION['admin'] = $email;
                $_SESSION['user'] = [
                    'email' => $email,
                    'name' => $user['name'] ?: ($userInfo['name'] ?? 'Admin'),
                    'avatar' => $user['avatar'] ?: ($userInfo['picture'] ?? '')
                ];
                header("Location: " . $adminPath);
                exit;
            } else {
                $error = "Tài khoản ({$email}) không có quyền truy cập trang quản trị!";
            }
        } else {
            $error = "Không thể lấy thông tin email từ Google.";
        }
    } else {
        $error = "Lỗi xác thực Google OAuth.";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'microsoft_login' && $msClientId) {
    $redirectUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=microsoft_callback';
    $authUrl = "https://login.microsoftonline.com/{$msTenantId}/oauth2/v2.0/authorize?" . http_build_query([
        'client_id' => $msClientId,
        'response_type' => 'code',
        'redirect_uri' => $redirectUri,
        'response_mode' => 'query',
        'scope' => 'User.Read openid profile email',
        'prompt' => 'select_account'
    ]);
    header("Location: " . $authUrl);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'microsoft_callback' && isset($_GET['code'])) {
    $code = $_GET['code'];
    $redirectUri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=microsoft_callback';
    
    $ch = curl_init("https://login.microsoftonline.com/{$msTenantId}/oauth2/v2.0/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $msClientId,
        'client_secret' => $msClientSecret,
        'code' => $code,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ]));
    $tokenResponse = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($tokenResponse, true);
    if (isset($tokenData['access_token'])) {
        $ch2 = curl_init("https://graph.microsoft.com/v1.0/me");
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $tokenData['access_token'],
            'Accept: application/json'
        ]);
        $userResponse = curl_exec($ch2);
        curl_close($ch2);
        
        $userInfo = json_decode($userResponse, true);
        $email = $userInfo['mail'] ?? $userInfo['userPrincipalName'] ?? '';
        
        if ($email) {
            $pdo = getPDO();
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT role, name, avatar FROM members WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
            } else {
                $config = getDbConfig();
                if ($config && isset($config['type']) && $config['type'] === 'firestore') {
                    require_once __DIR__ . '/includes/firestore_helper.php';
                    $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
                    $user = $fs->getDocument('members', md5($email));
                } else {
                    $user = null;
                }
            }

            if ($user && ($user['role'] ?? 'user') === 'admin') {
                $_SESSION['admin'] = $email;
                $_SESSION['user'] = [
                    'email' => $email,
                    'name' => $user['name'] ?: ($userInfo['displayName'] ?? 'Admin'),
                    'avatar' => $user['avatar'] ?: 'https://ui-avatars.com/api/?name=Admin'
                ];
                header("Location: " . $adminPath);
                exit;
            } else {
                $error = "Tài khoản ({$email}) không có quyền truy cập trang quản trị!";
            }
        } else {
            $error = "Không thể lấy thông tin email từ Microsoft.";
        }
    } else {
        $error = "Lỗi xác thực Microsoft OAuth.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin'] = $username;
            header("Location: " . $adminPath);
            exit;
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu';
        }
    } else {
        $config = getDbConfig();
        if ($config && isset($config['type']) && $config['type'] === 'firestore') {
            require_once __DIR__ . '/includes/firestore_helper.php';
            $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
            $user = $fs->getDocument('users', md5($username));
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin'] = $username;
                header("Location: " . $adminPath);
                exit;
            } else {
                $error = 'Sai tên đăng nhập hoặc mật khẩu';
            }
        } else {
            $error = 'Lỗi kết nối cơ sở dữ liệu';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Admin - <?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-purple-500/10 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center shadow-lg shadow-red-500/30">
                    <i data-lucide="play" class="w-8 h-8 text-white fill-current"></i>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2 text-center">Quản Trị Hệ Thống</h1>
            <p class="text-gray-400 text-center mb-8">Đăng nhập để quản lý website phim của bạn</p>
            
            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-lg mb-6 text-sm text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Tên đăng nhập</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-3 top-3 w-5 h-5 text-gray-500"></i>
                        <input type="text" name="username" required 
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-10 pr-4 py-2.5 text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Mật khẩu</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-3 w-5 h-5 text-gray-500"></i>
                        <input type="password" name="password" required 
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-10 pr-4 py-2.5 text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-all">
                    </div>
                </div>
                <button type="submit" class="w-full bg-red-600 text-white font-medium py-3 rounded-lg hover:bg-red-700 transition-all shadow-lg shadow-red-600/25 flex items-center justify-center">
                    <span>Đăng Nhập</span>
                    <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                </button>
            </form>

            <?php if (!empty($googleClientId) || !empty($msClientId)): ?>
                <div class="mt-5">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-gray-900 text-gray-400">Hoặc</span>
                        </div>
                    </div>
                    <div class="mt-5 space-y-3">
                        <?php if (!empty($googleClientId)): ?>
                        <a href="?action=google_login" class="w-full bg-white text-gray-900 font-medium py-3 rounded-lg hover:bg-gray-100 transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            <span>Đăng nhập với Google</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($msClientId)): ?>
                        <a href="?action=microsoft_login" class="w-full bg-[#2F2F2F] text-white font-medium py-3 rounded-lg hover:bg-[#3F3F3F] transition-all flex items-center justify-center border border-gray-700">
                            <svg class="w-5 h-5 mr-2" viewBox="0 0 21 21">
                                <path fill="#f25022" d="M1 1h9v9H1z"/><path fill="#00a4ef" d="M1 11h9v9H1z"/><path fill="#7fba00" d="M11 1h9v9h-9z"/><path fill="#ffb900" d="M11 11h9v9h-9z"/>
                            </svg>
                            <span>Đăng nhập với Microsoft</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="mt-6 text-center">
                <a href="/" class="text-gray-500 hover:text-white transition-colors text-sm flex items-center justify-center">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
