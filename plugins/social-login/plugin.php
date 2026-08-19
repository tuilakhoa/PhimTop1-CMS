<?php
// Tên Plugin: Social Login (OAuth)
// Phiên bản: 1.0.0

add_filter('admin_menu_groups', function($groups) {
    if (!isset($groups['Hệ Thống'])) {
        $groups['Hệ Thống'] = [];
    }
    // Chèn menu Cấu Hình Đăng Nhập MXH
    $groups['Hệ Thống']['plugin_social_login'] = [
        'icon' => 'shield-check',
        'title' => 'Đăng Nhập MXH'
    ];
    return $groups;
});

// Điều hướng trang admin
add_filter('admin_page_file', function($file, $page) {
    if ($page === 'plugin_social_login') {
        return __DIR__ . '/admin_page.php';
    }
    return $file;
});

// Render frontend buttons
add_action('social_login_buttons', function() {
    $settings = getSettings();
    $googleClientId = $settings['googleClientId'] ?? '';
    $msClientId = $settings['msClientId'] ?? '';
    $enableGoogleLogin = isset($settings['enableGoogleLogin']) && $settings['enableGoogleLogin'] == 1;
    $enableMicrosoftLogin = isset($settings['enableMicrosoftLogin']) && $settings['enableMicrosoftLogin'] == 1;

    if (($googleClientId && $enableGoogleLogin) || ($msClientId && $enableMicrosoftLogin)) {
        echo '<div class="mt-6">
                <div class="relative flex items-center mb-6">
                    <div class="flex-grow border-t border-slate-700/50"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">hoặc tiếp tục với</span>
                    <div class="flex-grow border-t border-slate-700/50"></div>
                </div>
                <div class="flex flex-col space-y-3">';
        
        if ($googleClientId && $enableGoogleLogin) {
            echo '<a href="/api/auth.php?action=google_login" class="w-full flex items-center justify-center space-x-2 bg-white hover:bg-gray-100 text-gray-900 font-semibold py-3 px-4 rounded-xl transition-all shadow-md">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Google</span>
                </a>';
        }

        if ($msClientId && $enableMicrosoftLogin) {
            echo '<a href="/api/auth.php?action=microsoft_login" class="w-full flex items-center justify-center space-x-2 bg-[#2F2F2F] hover:bg-[#3F3F3F] text-white font-semibold py-3 px-4 rounded-xl transition-all shadow-md border border-slate-700/50">
                    <svg class="w-5 h-5" viewBox="0 0 21 21">
                        <path fill="#f25022" d="M1 1h9v9H1z"/><path fill="#00a4ef" d="M1 11h9v9H1z"/><path fill="#7fba00" d="M11 1h9v9h-9z"/><path fill="#ffb900" d="M11 11h9v9h-9z"/>
                    </svg>
                    <span>Microsoft</span>
                </a>';
        }
        
        echo '</div></div>';
    }
});

// Render admin login buttons
add_action('admin_social_login_buttons', function() {
    $settings = getSettings();
    $googleClientId = $settings['googleClientId'] ?? '';
    $msClientId = $settings['msClientId'] ?? '';
    $enableGoogleLogin = isset($settings['enableGoogleLogin']) && $settings['enableGoogleLogin'] == 1;
    $enableMicrosoftLogin = isset($settings['enableMicrosoftLogin']) && $settings['enableMicrosoftLogin'] == 1;

    if (($googleClientId && $enableGoogleLogin) || ($msClientId && $enableMicrosoftLogin)) {
        echo '<div class="mt-5">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-900 text-gray-400">Hoặc</span>
                    </div>
                </div>
                <div class="mt-5 space-y-3">';
        
        if ($googleClientId && $enableGoogleLogin) {
            echo '<a href="?action=google_login" class="w-full bg-white text-gray-900 font-medium py-3 rounded-lg hover:bg-gray-100 transition-all flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Đăng nhập với Google</span>
                </a>';
        }

        if ($msClientId && $enableMicrosoftLogin) {
            echo '<a href="?action=microsoft_login" class="w-full bg-[#2F2F2F] text-white font-medium py-3 rounded-lg hover:bg-[#3F3F3F] transition-all flex items-center justify-center border border-gray-700">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 21 21">
                        <path fill="#f25022" d="M1 1h9v9H1z"/><path fill="#00a4ef" d="M1 11h9v9H1z"/><path fill="#7fba00" d="M11 1h9v9h-9z"/><path fill="#ffb900" d="M11 11h9v9h-9z"/>
                    </svg>
                    <span>Đăng nhập với Microsoft</span>
                </a>';
        }
        
        echo '</div></div>';
    }
});

// Helper function for user upsert
function plugin_social_login_upsert_user($email, $name, $avatar) {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, role, name, avatar FROM members WHERE email = ?");
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
        return $user;
    } else {
        $config = getDbConfig();
        if ($config && isset($config['type']) && $config['type'] === 'firestore') {
            require_once __DIR__ . '/../../includes/firestore_helper.php';
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
            return $user;
        }
    }
    return null;
}

// Handle API Auth (Frontend)
add_action('api_auth', function($action) {
    if (!in_array($action, ['google_login', 'google_callback', 'microsoft_login', 'microsoft_callback'])) return;
    
    $settings = getSettings();
    $googleClientId = $settings['googleClientId'] ?? '';
    $googleClientSecret = $settings['googleClientSecret'] ?? '';
    $msClientId = $settings['msClientId'] ?? '';
    $msClientSecret = $settings['msClientSecret'] ?? '';
    $msTenantId = $settings['msTenantId'] ?? 'common';
    $enableGoogleLogin = isset($settings['enableGoogleLogin']) && $settings['enableGoogleLogin'] == 1;
    $enableMicrosoftLogin = isset($settings['enableMicrosoftLogin']) && $settings['enableMicrosoftLogin'] == 1;

    $googleRedirectUri = ($_SERVER['HTTP_HOST'] === 'localhost' ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/api/auth.php?action=google_callback';
    $msRedirectUri = ($_SERVER['HTTP_HOST'] === 'localhost' ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/api/auth.php?action=microsoft_callback';

    // Google Start
    if ($action === 'google_login') {
        if (!$enableGoogleLogin) die('Tính năng đăng nhập Google đã bị tắt.');
        if (!$googleClientId || !$googleClientSecret) die('Google OAuth chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        
        $_SESSION['auth_referer'] = $_SERVER['HTTP_REFERER'] ?? '/';
        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'response_type' => 'code',
            'client_id' => $googleClientId,
            'redirect_uri' => $googleRedirectUri,
            'scope' => 'email profile',
            'prompt' => 'select_account'
        ]);
        header("Location: " . $authUrl);
        exit;
    }

    // Google Callback
    if ($action === 'google_callback' && isset($_GET['code'])) {
        if (!$enableGoogleLogin) die('Tính năng đăng nhập Google đã bị tắt.');
        if (!$googleClientId || !$googleClientSecret) die('Google OAuth chưa được cấu hình.');
        
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code' => $_GET['code'],
            'client_id' => $googleClientId,
            'client_secret' => $googleClientSecret,
            'redirect_uri' => $googleRedirectUri,
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
                $name = $userInfo['name'] ?? 'User';
                $avatar = $userInfo['picture'] ?? '';
                
                $user = plugin_social_login_upsert_user($email, $name, $avatar);
                
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

    // Microsoft Start
    if ($action === 'microsoft_login') {
        if (!$enableMicrosoftLogin) die('Tính năng đăng nhập Microsoft đã bị tắt.');
        if (!$msClientId || !$msClientSecret) die('Microsoft OAuth chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        
        $_SESSION['auth_referer'] = $_SERVER['HTTP_REFERER'] ?? '/';
        $authUrl = "https://login.microsoftonline.com/{$msTenantId}/oauth2/v2.0/authorize?" . http_build_query([
            'client_id' => $msClientId,
            'response_type' => 'code',
            'redirect_uri' => $msRedirectUri,
            'response_mode' => 'query',
            'scope' => 'User.Read openid profile email',
            'prompt' => 'select_account'
        ]);
        header("Location: " . $authUrl);
        exit;
    }

    // Microsoft Callback
    if ($action === 'microsoft_callback' && isset($_GET['code'])) {
        if (!$enableMicrosoftLogin) die('Tính năng đăng nhập Microsoft đã bị tắt.');
        if (!$msClientId || !$msClientSecret) die('Microsoft OAuth chưa được cấu hình.');
        
        $ch = curl_init("https://login.microsoftonline.com/{$msTenantId}/oauth2/v2.0/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $msClientId,
            'client_secret' => $msClientSecret,
            'code' => $_GET['code'],
            'redirect_uri' => $msRedirectUri,
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
                $name = $userInfo['displayName'] ?? 'User';
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
                
                $user = plugin_social_login_upsert_user($email, $name, $avatar);
                
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
                die('Không thể lấy thông tin email từ Microsoft.');
            }
        } else {
            die('Lỗi xác thực Microsoft OAuth (Token Error).');
        }
    }
});

// Handle Admin Login Auth
add_action('admin_login_auth', function($action) {
    if (!in_array($action, ['google_login', 'google_callback', 'microsoft_login', 'microsoft_callback'])) return;
    
    $settings = getSettings();
    $googleClientId = $settings['googleClientId'] ?? '';
    $googleClientSecret = $settings['googleClientSecret'] ?? '';
    $msClientId = $settings['msClientId'] ?? '';
    $msClientSecret = $settings['msClientSecret'] ?? '';
    $msTenantId = $settings['msTenantId'] ?? 'common';
    $enableGoogleLogin = isset($settings['enableGoogleLogin']) && $settings['enableGoogleLogin'] == 1;
    $enableMicrosoftLogin = isset($settings['enableMicrosoftLogin']) && $settings['enableMicrosoftLogin'] == 1;

    $googleRedirectUri = ($_SERVER['HTTP_HOST'] === 'localhost' ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=google_callback';
    $msRedirectUri = ($_SERVER['HTTP_HOST'] === 'localhost' ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/login.php?action=microsoft_callback';
    $adminPath = $settings['adminPath'] ?? '/admin';

    // Google Start
    if ($action === 'google_login' && $googleClientId && $enableGoogleLogin) {
        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?response_type=code&client_id={$googleClientId}&redirect_uri=" . urlencode($googleRedirectUri) . "&scope=email%20profile";
        header("Location: " . $authUrl);
        exit;
    }

    // Google Callback
    if ($action === 'google_callback' && isset($_GET['code']) && $enableGoogleLogin) {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code' => $_GET['code'],
            'client_id' => $googleClientId,
            'client_secret' => $googleClientSecret,
            'redirect_uri' => $googleRedirectUri,
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
                $user = plugin_social_login_upsert_user($email, $userInfo['name'] ?? 'Admin', $userInfo['picture'] ?? '');

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
                    die("Tài khoản ({$email}) không có quyền truy cập trang quản trị!");
                }
            } else {
                die("Không thể lấy thông tin email từ Google.");
            }
        } else {
            die("Lỗi xác thực Google OAuth.");
        }
    }

    // Microsoft Start
    if ($action === 'microsoft_login' && $msClientId && $enableMicrosoftLogin) {
        $authUrl = "https://login.microsoftonline.com/{$msTenantId}/oauth2/v2.0/authorize?" . http_build_query([
            'client_id' => $msClientId,
            'response_type' => 'code',
            'redirect_uri' => $msRedirectUri,
            'response_mode' => 'query',
            'scope' => 'User.Read openid profile email',
            'prompt' => 'select_account'
        ]);
        header("Location: " . $authUrl);
        exit;
    }

    // Microsoft Callback
    if ($action === 'microsoft_callback' && isset($_GET['code']) && $enableMicrosoftLogin) {
        $ch = curl_init("https://login.microsoftonline.com/{$msTenantId}/oauth2/v2.0/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $msClientId,
            'client_secret' => $msClientSecret,
            'code' => $_GET['code'],
            'redirect_uri' => $msRedirectUri,
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
                $user = plugin_social_login_upsert_user($email, $userInfo['displayName'] ?? 'Admin', 'https://ui-avatars.com/api/?name=Admin');

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
                    die("Tài khoản ({$email}) không có quyền truy cập trang quản trị!");
                }
            } else {
                die("Không thể lấy thông tin email từ Microsoft.");
            }
        } else {
            die("Lỗi xác thực Microsoft OAuth.");
        }
    }
});
