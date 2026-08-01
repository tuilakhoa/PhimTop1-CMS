<?php
$dbConfigPath = __DIR__ . '/config/db_config.json';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = [
        'host' => $_POST['host'] ?? '127.0.0.1',
        'database' => $_POST['database'] ?? '',
        'user' => $_POST['user'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
    
    try {
        $dsn = "mysql:host={$config['host']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$config['database']}`");
        
        // Create tables
        $pdo->exec("CREATE TABLE IF NOT EXISTS `releases` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `version` VARCHAR(50) NOT NULL UNIQUE,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `changelog` VARCHAR(255),
            `download_url` VARCHAR(255),
            `is_force` TINYINT(1) DEFAULT 0,
            `status` ENUM('draft', 'published') DEFAULT 'draft',
            `release_date` DATE NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admin` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL
        )");
        
        // Check if admin exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM `admin`");
        if ($stmt->fetchColumn() == 0) {
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO `admin` (`username`, `password`) VALUES ('admin', '$defaultPassword')");
        }

        if (!is_dir(__DIR__ . '/config')) {
            mkdir(__DIR__ . '/config', 0755, true);
        }
        file_put_contents($dbConfigPath, json_encode($config));
        
        $success = 'Cài đặt thành công! Vui lòng xóa file setup.php và truy cập <a href="/admin" class="underline font-bold text-blue-400">/admin</a> để đăng nhập (admin/admin123).';
    } catch (PDOException $e) {
        $error = "Lỗi kết nối CSDL: " . $e->getMessage();
    }
}

if (file_exists($dbConfigPath) && !$success) {
    header("Location: /");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài Đặt Update Server</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md border border-gray-700">
        <h1 class="text-2xl font-bold mb-6 text-center text-blue-500">Cài Đặt CSDL Update Server</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500 text-red-500 p-3 rounded mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500 text-green-400 p-4 rounded mb-4 text-center">
                <?= $success ?>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Máy chủ (Host)</label>
                    <input type="text" name="host" value="127.0.0.1" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Tên Database</label>
                    <input type="text" name="database" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Username</label>
                    <input type="text" name="user" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Mật khẩu</label>
                    <input type="password" name="password" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors mt-4">
                    Cài Đặt Hệ Thống
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
