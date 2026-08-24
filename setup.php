<?php
require_once __DIR__ . '/includes/db.php';
$settings = getSettings();
if ($settings['initialized']) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

if ($isPost) {
    $dbType = $_POST['dbType'] ?? 'mysql';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($dbType === 'mysql') {
        $dbHost = $_POST['dbHost'] ?? '';
        $dbName = $_POST['dbName'] ?? '';
        $dbUser = $_POST['dbUser'] ?? '';
        $dbPass = $_POST['dbPass'] ?? '';
        
        if (!$dbHost || !$dbName || !$dbUser || !$username || !$password) {
            $error = "Vui lòng nhập đầy đủ các trường bắt buộc (MySQL).";
        } else {
            if (!is_writable(dirname($dbConfigPath))) {
                $error = "Thư mục gốc không có quyền ghi. Vui lòng CHMOD 777 hoặc cấp quyền chown cho user web server (www/www-data).";
            } else {
                $config = ['type' => 'mysql', 'host' => $dbHost, 'database' => $dbName, 'user' => $dbUser, 'password' => $dbPass];
                if (file_put_contents($dbConfigPath, json_encode($config, JSON_PRETTY_PRINT)) === false) {
                    $error = "Không thể lưu file config.json. Kiểm tra lại quyền thư mục!";
                } else {
                    try {
                        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
                        $pdo = new PDO($dsn, $dbUser, $dbPass);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $pdo->exec("DROP TABLE IF EXISTS users");
                $pdo->exec("DROP TABLE IF EXISTS settings");
                
                $pdo->exec("CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(255) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL
                )");
                $pdo->exec("CREATE TABLE settings (
                    id INT PRIMARY KEY, adminPath VARCHAR(255), displayMode VARCHAR(50) DEFAULT 'api',
                    theme VARCHAR(50) DEFAULT 'dark', cmsVersion VARCHAR(50) DEFAULT '1.0.2',
                    githubRepo VARCHAR(255) DEFAULT 'kkphim/cms-core', githubBranch VARCHAR(255) DEFAULT 'main',
                    githubToken VARCHAR(255), autoCheckUpdates TINYINT(1) DEFAULT 1,
                    updateServerUrl VARCHAR(255) DEFAULT 'tuilakhoa/PhimTop1-CMS',
                    lastUpdateCheck VARCHAR(255), latestRelease TEXT,
                    siteName VARCHAR(255) DEFAULT 'PhimTop1',
                    seoTitle TEXT, seoDesc TEXT, seoKeywords TEXT, logoUrl VARCHAR(255),
                    verifyGoogle VARCHAR(255), verifyBing VARCHAR(255), verifyYandex VARCHAR(255),
                    customHead TEXT, customBody TEXT, db_version INT DEFAULT 18
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS movies (
                    id VARCHAR(100) PRIMARY KEY, name VARCHAR(255) NOT NULL, origin_name VARCHAR(255),
                    slug VARCHAR(255) UNIQUE NOT NULL, thumb_url TEXT, poster_url TEXT, year INT, type VARCHAR(100),
                    status VARCHAR(100), episode_current VARCHAR(100), episode_total INT DEFAULT 0,
                    quality VARCHAR(100), lang VARCHAR(100), time VARCHAR(100),
                    chieu_rap TINYINT(1) DEFAULT 0, is_copyright TINYINT(1) DEFAULT 0, sub_docquyen TINYINT(1) DEFAULT 0,
                    view INT DEFAULT 0, content TEXT, trailer_url TEXT, actor TEXT, director TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
                    slug VARCHAR(255) PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS movie_category (
                    movie_slug VARCHAR(255),
                    category_slug VARCHAR(255),
                    PRIMARY KEY (movie_slug, category_slug)
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS episodes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    movie_slug VARCHAR(255), server_name VARCHAR(100), name VARCHAR(100), slug VARCHAR(255), filename VARCHAR(255),
                    embed_url TEXT, m3u8_url TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_movie_slug (movie_slug)
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS playlists (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_email VARCHAR(255) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS playlist_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    playlist_id INT NOT NULL,
                    movie_slug VARCHAR(255) NOT NULL,
                    movie_name VARCHAR(255) NOT NULL,
                    thumb_url TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
                    UNIQUE KEY playlist_movie (playlist_id, movie_slug)
                )");

                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $pdo->exec("TRUNCATE TABLE users");
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashed]);

                $randomPath = 'admin_' . substr(md5(uniqid(rand(), true)), 0, 6);
                $adminDir = __DIR__ . '/admin';
                $newAdminDir = __DIR__ . '/' . $randomPath;
                $finalAdminPath = 'admin';
                // Chỉ insert settings mặc định nếu bảng settings đang rỗng
                $settingsCount = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
                if ($settingsCount == 0) {
                    // Cài đặt mới: Sinh random admin path và đổi tên thư mục để bảo mật
                    $finalAdminPath = 'admin_' . substr(md5(uniqid()), 0, 6);
                    
                    $sourceAdminDir = __DIR__ . '/admin';
                    if (!is_dir($sourceAdminDir)) {
                        $adminDirs = glob(__DIR__ . '/admin_*', GLOB_ONLYDIR);
                        if (!empty($adminDirs)) $sourceAdminDir = $adminDirs[0];
                    }

                    if (is_dir($sourceAdminDir) && !is_dir(__DIR__ . '/' . $finalAdminPath)) {
                        if (!@rename($sourceAdminDir, __DIR__ . '/' . $finalAdminPath)) {
                            throw new Exception("Không thể đổi tên thư mục admin. Vui lòng cấp quyền ghi cho thư mục gốc!");
                        }
                    }

                    $stmt = $pdo->prepare("INSERT INTO settings (id, adminPath, displayMode, theme, cmsVersion, siteName, seoTitle, seoDesc, seoKeywords, logoUrl, updateServerUrl) VALUES (1, ?, 'api', 'dark', '1.0.2', 'PhimTop1', 'PhimTop1 - Xem Phim Online Chất Lượng Cao', 'Hệ thống xem phim trực tuyến chất lượng cao, cập nhật liên tục mỗi ngày.', 'xem phim, phim online, phim hay, phim vietsub', '', 'tuilakhoa/PhimTop1-CMS')");
                    $stmt->execute(['/' . $finalAdminPath]);
                } else {
                    // Đã cài đặt: lấy adminPath hiện tại từ database
                    $finalAdminPath = ltrim($pdo->query("SELECT adminPath FROM settings WHERE id = 1")->fetchColumn(), '/');
                    if (empty($finalAdminPath)) $finalAdminPath = 'admin';
                    
                    // Đảm bảo thư mục vật lý khớp với đường dẫn trong database cũ
                    $sourceAdminDir = __DIR__ . '/admin';
                    if (!is_dir($sourceAdminDir)) {
                        $adminDirs = glob(__DIR__ . '/admin_*', GLOB_ONLYDIR);
                        if (!empty($adminDirs)) $sourceAdminDir = $adminDirs[0];
                    }
                    
                    if ($finalAdminPath !== 'admin' && is_dir($sourceAdminDir) && basename($sourceAdminDir) !== $finalAdminPath) {
                        @rename($sourceAdminDir, __DIR__ . '/' . $finalAdminPath);
                    }
                }

                if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                    session_start();
                }
                $_SESSION['admin'] = $username;

                $success = "Cài đặt MySQL thành công! Link quản trị mới là: /$finalAdminPath/";
                echo "<script>setTimeout(() => window.location.href='/$finalAdminPath/index.php', 3000);</script>";
            } catch (Exception $e) {
                $error = "Kết nối hoặc cài đặt DB thất bại: " . $e->getMessage();
            }
                }
            }
        }
    } else if ($dbType === 'firestore') {
        $projectId = $_POST['projectId'] ?? '';
        $serviceAccount = $_POST['serviceAccount'] ?? '';
        
        if (!$projectId || !$serviceAccount || !$username || !$password) {
            $error = "Vui lòng nhập đầy đủ các trường bắt buộc (Firestore).";
        } else {
            $saData = json_decode($serviceAccount, true);
            if (!$saData || !isset($saData['project_id'])) {
                $error = "Nội dung Service Account JSON không hợp lệ.";
            } else {
                $config = ['type' => 'firestore', 'projectId' => $projectId, 'serviceAccount' => $saData];
                file_put_contents($dbConfigPath, json_encode($config, JSON_PRETTY_PRINT));
                
                require_once __DIR__ . '/includes/firestore_helper.php';
                $fs = new FirestoreClient($projectId, $saData);
                
                // Khởi tạo tài khoản Admin
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $fs->setDocument('users', md5($username), [
                    'username' => $username,
                    'password' => $hashed,
                    'role' => 'admin'
                ]);
                
                $settingsData = $fs->getDocument('settings', '1');
                if (!$settingsData) {
                    $finalAdminPath = 'admin_' . substr(md5(uniqid()), 0, 6);
                    if (is_dir(__DIR__ . '/admin') && !is_dir(__DIR__ . '/' . $finalAdminPath)) {
                        rename(__DIR__ . '/admin', __DIR__ . '/' . $finalAdminPath);
                    }
                    
                    // Khởi tạo Settings mặc định
                    $fs->setDocument('settings', '1', [
                        'adminPath' => '/' . $finalAdminPath,
                        'displayMode' => 'api',
                        'theme' => 'dark',
                        'cmsVersion' => '1.0.0',
                        'siteName' => 'PhimTop1',
                        'seoTitle' => 'PhimTop1 - Xem Phim Online Chất Lượng Cao',
                        'seoDesc' => 'Hệ thống xem phim trực tuyến chất lượng cao, cập nhật liên tục mỗi ngày.',
                        'seoKeywords' => 'xem phim, phim online, phim hay, phim vietsub',
                        'logoUrl' => '',

                        'updateServerUrl' => 'tuilakhoa/PhimTop1-CMS'
                    ]);
                } else {
                    $finalAdminPath = ltrim($settingsData['adminPath'] ?? 'admin', '/');
                    
                    $sourceAdminDir = __DIR__ . '/admin';
                    if (!is_dir($sourceAdminDir)) {
                        $adminDirs = glob(__DIR__ . '/admin_*', GLOB_ONLYDIR);
                        if (!empty($adminDirs)) $sourceAdminDir = $adminDirs[0];
                    }
                    
                    if ($finalAdminPath !== 'admin' && is_dir($sourceAdminDir) && basename($sourceAdminDir) !== $finalAdminPath) {
                        @rename($sourceAdminDir, __DIR__ . '/' . $finalAdminPath);
                    }
                }

                if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                    session_start();
                }
                $_SESSION['admin'] = $username;

                $success = "Cài đặt Firestore thành công! Link quản trị mới là: /$finalAdminPath/";
                echo "<script>setTimeout(() => window.location.href='/$finalAdminPath/index.php', 3000);</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Cài Đặt Hệ Thống - PhimTop1 CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/setup.css?v=<?= time() ?>">
</head>
<body class="bg-[#0f172a] min-h-screen relative overflow-x-hidden custom-scrollbar">
    <!-- Hiệu ứng Background -->
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/20 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="flex items-center justify-center min-h-screen w-full p-4">
        <div class="max-w-5xl w-full bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-3xl shadow-2xl relative z-10 overflow-hidden flex flex-col md:flex-row min-h-[650px]">
        
        <!-- Sidebar -->
        <div class="w-full md:w-1/3 bg-slate-800 p-10 flex flex-col border-r border-slate-700/50 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-bl-full pointer-events-none"></div>
            
            <div class="flex items-center space-x-3 mb-12">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30">
                    <i data-lucide="monitor-play" class="w-7 h-7 text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white">PhimTop1</h1>
                    <span class="text-blue-400 text-xs font-semibold uppercase tracking-wider">Setup Wizard</span>
                </div>
            </div>
            
            <div class="flex-1">
                <ul class="space-y-8 relative before:content-[''] before:absolute before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-700">
                    <li class="relative flex items-center gap-4 step-indicator transition-all duration-300" id="ind-1">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm z-10 shadow-[0_0_0_4px_#1e293b] ring-2 ring-blue-500/50">1</div>
                        <span class="text-white font-semibold">Điều khoản</span>
                    </li>
                    <li class="relative flex items-center gap-4 step-indicator transition-all duration-300 opacity-50" id="ind-2">
                        <div class="w-8 h-8 rounded-full bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-sm z-10 shadow-[0_0_0_4px_#1e293b]">2</div>
                        <span class="text-slate-300 font-medium">Kiểm tra hệ thống</span>
                    </li>
                    <li class="relative flex items-center gap-4 step-indicator transition-all duration-300 opacity-50" id="ind-3">
                        <div class="w-8 h-8 rounded-full bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-sm z-10 shadow-[0_0_0_4px_#1e293b]">3</div>
                        <span class="text-slate-300 font-medium">Cấu hình kết nối</span>
                    </li>
                </ul>
            </div>
            
            <div class="mt-8 text-xs text-slate-500">
                Phiên bản CMS: v1.1
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="w-full md:w-2/3 p-8 md:p-12 relative flex flex-col justify-center">
            
            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6 flex items-start text-sm">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl mb-6 flex items-start text-sm">
                    <i data-lucide="check-circle" class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <!-- Step 1: Điều khoản -->
            <div id="step-1" class="step-content">
                <h2 class="text-3xl font-bold text-white mb-2">Điều khoản sử dụng</h2>
                <p class="text-slate-400 text-sm mb-6">Vui lòng đọc kỹ trước khi tiến hành cài đặt PhimTop1 CMS.</p>
                
                <div class="bg-[#0b1120] border border-slate-700/80 rounded-xl p-6 h-64 overflow-y-auto mb-6 text-slate-300 text-sm leading-relaxed custom-scrollbar shadow-inner">
                    <p class="mb-4 font-semibold text-white">CHÀO MỪNG BẠN ĐẾN VỚI PHIMTOP1 CMS</p>
                    <p class="mb-3">1. PhimTop1 CMS là một mã nguồn mở quản trị website phim được viết hoàn toàn bằng PHP (Monolithic), cung cấp giải pháp xây dựng website tốc độ cao, thân thiện với SEO.</p>
                    <p class="mb-3">2. Bằng việc cài đặt mã nguồn này, bạn đồng ý chịu hoàn toàn trách nhiệm trước pháp luật về mọi nội dung, hình ảnh, hoặc phương tiện được đăng tải, chia sẻ trên hệ thống của bạn.</p>
                    <p class="mb-3">3. Nhóm phát triển không chịu bất kỳ trách nhiệm liên đới nào nếu bạn sử dụng mã nguồn này vào các mục đích phát tán nội dung độc hại, lừa đảo, hoặc vi phạm bản quyền và pháp luật hiện hành.</p>
                    <p class="mb-3">4. Bạn được quyền chỉnh sửa, tùy biến, và phát triển thêm các tính năng phù hợp với nhu cầu. Tuy nhiên, mong bạn tôn trọng và giữ lại thông tin tác giả trong các ghi chú mã nguồn.</p>
                    <p class="mb-3">Cảm ơn bạn đã tin tưởng và sử dụng PhimTop1 CMS!</p>
                </div>
                
                <label class="flex items-center text-slate-300 cursor-pointer mb-8 w-max select-none">
                    <input type="checkbox" id="agree-terms" class="mr-3 w-5 h-5 rounded accent-blue-600 bg-slate-700 border-slate-600 transition-colors cursor-pointer">
                    <span class="hover:text-white transition-colors">Tôi đã đọc và đồng ý với các điều khoản trên</span>
                </label>
                
                <div class="flex justify-end">
                    <button type="button" onclick="goToStep(2)" id="btn-step1" disabled class="bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-8 py-3 rounded-xl font-medium transition-all flex items-center shadow-lg shadow-blue-600/20">
                        Tiếp tục <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: System Check -->
            <?php 
            $reqs = [
                'Phiên bản PHP >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
                'PDO MySQL (Database)' => extension_loaded('pdo_mysql'),
                'cURL (Tải tài nguyên)' => extension_loaded('curl'),
                'GD (Xử lý hình ảnh WebP)' => extension_loaded('gd'),
                'Mbstring (Chuỗi đa phân)' => extension_loaded('mbstring'),
                'Quyền Ghi thư mục includes/' => is_writable(__DIR__ . '/includes')
            ];
            $allPassed = !in_array(false, $reqs, true);
            ?>
            <div id="step-2" class="step-content hidden">
                <h2 class="text-3xl font-bold text-white mb-2">Kiểm tra hệ thống</h2>
                <p class="text-slate-400 text-sm mb-6">Đảm bảo máy chủ của bạn đáp ứng các yêu cầu tối thiểu.</p>
                
                <div class="space-y-3 mb-8">
                    <?php foreach($reqs as $name => $passed): ?>
                    <div class="flex items-center justify-between bg-slate-800/50 p-4 rounded-xl border border-slate-700/50 hover:border-slate-600 transition-colors">
                        <span class="text-slate-200 text-sm font-medium"><?= $name ?></span>
                        <?php if($passed): ?>
                            <div class="flex items-center text-emerald-400 bg-emerald-400/10 px-3 py-1 rounded-full text-xs font-bold border border-emerald-500/20 shadow-[0_0_10px_rgba(52,211,153,0.1)]">
                                <i data-lucide="check" class="w-3.5 h-3.5 mr-1.5"></i> OK
                            </div>
                        <?php else: ?>
                            <div class="flex items-center text-rose-400 bg-rose-400/10 px-3 py-1 rounded-full text-xs font-bold border border-rose-500/20 shadow-[0_0_10px_rgba(251,113,133,0.1)]">
                                <i data-lucide="x" class="w-3.5 h-3.5 mr-1.5"></i> LỖI
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                    <button type="button" onclick="goToStep(1)" class="text-slate-400 hover:text-white px-4 py-2 font-medium transition-colors flex items-center">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Quay lại
                    </button>
                    
                    <?php if($allPassed): ?>
                        <button type="button" onclick="goToStep(3)" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-medium transition-all flex items-center shadow-lg shadow-blue-600/20">
                            Tiếp tục <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                        </button>
                    <?php else: ?>
                        <button type="button" disabled class="bg-rose-600/50 cursor-not-allowed text-white px-8 py-3 rounded-xl font-medium flex items-center">
                            Lỗi Hệ Thống <i data-lucide="alert-triangle" class="w-5 h-5 ml-2"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 3: Database & Admin Configuration -->
            <div id="step-3" class="step-content hidden">
                <h2 class="text-3xl font-bold text-white mb-2">Cấu hình kết nối</h2>
                <p class="text-slate-400 text-sm mb-6">Thiết lập cơ sở dữ liệu và tài khoản Quản trị viên tối cao.</p>

                <form method="POST" action="" class="space-y-6" id="setupForm">
                    <!-- Database Type Selector -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Loại Database</label>
                        <select id="dbType" name="dbType" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                            <option value="mysql">MySQL</option>
                            <option value="firestore">Firebase Firestore (NoSQL)</option>
                        </select>
                    </div>

                    <!-- MySQL Fields -->
                    <div id="mysqlFields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Host</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i data-lucide="server" class="w-4 h-4"></i></div>
                                <input type="text" name="dbHost" value="127.0.0.1" class="w-full bg-[#0b1120] border border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-white outline-none focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tên Database</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i data-lucide="database" class="w-4 h-4"></i></div>
                                <input type="text" name="dbName" class="w-full bg-[#0b1120] border border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-white outline-none focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Username DB</label>
                            <input type="text" name="dbUser" value="root" class="w-full bg-[#0b1120] border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-blue-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Password DB</label>
                            <input type="password" name="dbPass" class="w-full bg-[#0b1120] border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-blue-500 transition-colors">
                        </div>
                    </div>

                    <!-- Firestore Fields -->
                    <div id="firestoreFields" class="hidden space-y-4">
                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-4">
                            <h4 class="text-blue-400 font-semibold mb-2 flex items-center"><i data-lucide="info" class="w-4 h-4 mr-2"></i>Hướng dẫn lấy cấu hình Firestore:</h4>
                            <ol class="list-decimal list-inside text-sm text-slate-300 space-y-1">
                                <li>Truy cập <a href="https://console.firebase.google.com/" target="_blank" class="text-blue-400 hover:underline">Firebase Console</a> và tạo/chọn Project.</li>
                                <li>Vào <b>Project settings</b> > <b>Service accounts</b>.</li>
                                <li>Bấm <b>Generate new private key</b> để tải file JSON.</li>
                                <li>Copy toàn bộ nội dung file JSON và dán vào ô bên dưới.</li>
                                <li><b>Project ID</b> chính là mục <code class="text-red-400 bg-slate-800 px-1 rounded">project_id</code> trong file JSON.</li>
                                <li class="pt-2">Vào mục <b>Firestore Database</b> > <b>Rules</b> và xuất bản đoạn Rules sau để bảo mật hệ thống:
<pre class="bg-[#0b1120] border border-slate-700 p-3 mt-2 rounded text-xs overflow-x-auto text-blue-300 font-mono">rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /{document=**} {
      allow read, write: if false; // Chỉ PHP Server (dùng JSON) mới có quyền đọc và ghi
    }
  }
}</pre>
                                </li>
                            </ol>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Project ID</label>
                            <input type="text" name="projectId" class="w-full bg-[#0b1120] border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-blue-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Service Account JSON (Nội dung file)</label>
                            <textarea name="serviceAccount" rows="4" class="w-full bg-[#0b1120] border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500 transition-colors text-sm font-mono custom-scrollbar" placeholder='{"type": "service_account", ...}'></textarea>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-800">
                        <h4 class="text-white font-semibold mb-4 flex items-center"><i data-lucide="user-plus" class="w-5 h-5 mr-2 text-purple-400"></i> Tài khoản Quản trị</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Tên đăng nhập (Admin)</label>
                                <input type="text" name="username" required class="w-full bg-[#0b1120] border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-blue-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Mật khẩu</label>
                                <input type="password" name="password" required class="w-full bg-[#0b1120] border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-blue-500 transition-colors">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-4">
                        <button type="button" onclick="goToStep(2)" class="text-slate-400 hover:text-white px-4 py-2 font-medium transition-colors flex items-center">
                            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Quay lại
                        </button>
                        <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg shadow-blue-600/30 flex items-center">
                            <i data-lucide="check" class="w-5 h-5 mr-2"></i> Hoàn Tất Cài Đặt
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
        </div>
    </div>

    <script src="/assets/js/setup.js?v=<?= time() ?>"></script>
    <script>
        <?php if ($isPost && $error): ?>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof window.goToStep === 'function') {
                    window.goToStep(3); // Go directly to step 3 if there was an error submitting
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>
