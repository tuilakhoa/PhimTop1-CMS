<?php
// Tự động load các class (Đơn giản hóa autoload)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/config/database.php';

$pdo = getPDO();
if (!$pdo && !strpos($_SERVER['REQUEST_URI'], 'setup.php')) {
    die("Database chưa được cài đặt hoặc cấu hình sai. Vui lòng chạy <a href='/setup.php'>/setup.php</a>");
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');
if ($requestUri === '') {
    $requestUri = '/';
}

$apiController = new \App\Controllers\ApiController($pdo);
$adminController = new \App\Controllers\AdminController($pdo);

switch ($requestUri) {
    case '/check':
        $apiController->checkUpdate();
        break;
        
    case '/admin':
    case '/admin/login':
        $adminController->login();
        break;
        
    case '/admin/logout':
        $adminController->logout();
        break;
        
    case '/admin/dashboard':
        $adminController->dashboard();
        break;
        
    case '/admin/release/create':
        $adminController->createRelease();
        break;
        
    case '/admin/release/delete':
        $adminController->deleteRelease();
        break;

    case '/':
        header("Location: /admin");
        break;
        
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
