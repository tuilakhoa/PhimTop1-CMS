<?php
require_once __DIR__ . '/includes/db.php';
session_start();
checkSetup();

$settings = getSettings();
$adminPath = $settings['adminPath'] ?? '/admin';

if (isset($_SESSION['admin'])) {
    header("Location: " . $adminPath . "/index.php");
    exit;
}

$error = '';
do_action('admin_login_auth', $_GET['action'] ?? '');

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
            header("Location: " . $adminPath . "/index.php");
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
                header("Location: " . $adminPath . "/index.php");
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
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($settings['siteName'] ?? 'PhimTop1') ?>">
    <link rel="manifest" href="/site.webmanifest">
    
    <?php if (!empty($settings['faviconUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['faviconUrl']) ?>">
    <?php elseif (!empty($settings['logoUrl'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($settings['logoUrl']) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <?php endif; ?>

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

            <?php do_action('admin_social_login_buttons'); ?>
            
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
