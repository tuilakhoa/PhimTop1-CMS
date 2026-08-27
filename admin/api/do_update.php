<?php
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

header('Content-Type: application/json');
@set_time_limit(0);
@ignore_user_abort(true);

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$logs = [];
function addLog($msg, $type = 'info') {
    global $logs;
    $logs[] = ['message' => $msg, 'type' => $type];
}

$targetTag = $_GET['download_url'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $targetTag = $data['download_url'] ?? '';
}

if (empty($targetTag)) {
    echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy phiên bản mục tiêu.', 'logs' => $logs]);
    exit;
}

$settings = getSettings();
if (isset($settings['allowAutoUpdate']) && $settings['allowAutoUpdate'] == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Tính năng cập nhật tự động đã bị TẮT.', 'logs' => $logs]);
    exit;
}
$repo = 'tuilakhoa/PhimTop1-CMS';

$configFile = __DIR__ . '/../../config/update.php';
$configUpdate = file_exists($configFile) ? require $configFile : ['current_version' => '1.0.0'];
$currentVersion = $configUpdate['current_version'] ?? '1.0.0';

addLog("Khởi tạo cập nhật (Chế độ ZIP Download)...");

if (!class_exists('ZipArchive')) {
    echo json_encode(['status' => 'error', 'message' => 'Server không hỗ trợ ZipArchive. Vui lòng bật extension zip trong PHP.', 'logs' => $logs]);
    exit;
}

$zipUrl = "https://github.com/$repo/archive/$targetTag.zip";
$tempZipFile = sys_get_temp_dir() . '/phimtop1_update_' . md5(time()) . '.zip';

addLog("Đang tải xuống mã nguồn từ Github...");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $zipUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_USERAGENT, 'PhimTop1-CMS-Updater');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$zipData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($zipData === false || $httpCode !== 200) {
    echo json_encode(['status' => 'error', 'message' => "Không thể tải file cập nhật. (HTTP $httpCode)", 'logs' => $logs]);
    exit;
}

if (@file_put_contents($tempZipFile, $zipData) === false) {
    echo json_encode(['status' => 'error', 'message' => 'Không thể ghi file cập nhật tạm thời.', 'logs' => $logs]);
    exit;
}

addLog("Tải xuống hoàn tất. Bắt đầu giải nén và cập nhật...");

$zip = new ZipArchive();
if ($zip->open($tempZipFile) !== true) {
    @unlink($tempZipFile);
    echo json_encode(['status' => 'error', 'message' => 'File tải về bị lỗi, không thể mở.', 'logs' => $logs]);
    exit;
}

$rootDir = realpath(__DIR__ . '/../../');
$adminFolderName = ltrim($settings['adminPath'] ?? '/admin', '/');

$successCount = 0;
$failCount = 0;

for ($i = 0; $i < $zip->numFiles; $i++) {
    $filename = $zip->getNameIndex($i);
    
    // Bỏ qua thư mục gốc của repo trong zip (VD: PhimTop1-CMS-main/)
    $parts = explode('/', $filename, 2);
    if (count($parts) < 2 || empty($parts[1])) continue;
    $relativePath = $parts[1];
    
    if (empty($relativePath)) continue;
    if (substr($relativePath, -1) === '/') {
        // Là thư mục
        continue;
    }
    
    // Map thư mục admin
    $actualFilename = $relativePath;
    if ($adminFolderName !== 'admin' && strpos($relativePath, 'admin/') === 0) {
        $actualFilename = $adminFolderName . '/' . substr($relativePath, 6);
    }
    
    // Các file/thư mục không được ghi đè
    if ($actualFilename === 'config.json' || strpos($actualFilename, 'cache/') === 0 || strpos($actualFilename, 'config/') === 0) {
        continue;
    }
    
    $targetPath = $rootDir . '/' . $actualFilename;
    $targetDir = dirname($targetPath);
    
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }
    
    $content = $zip->getFromIndex($i);
    if (@file_put_contents($targetPath, $content) !== false) {
        $successCount++;
    } else {
        $failCount++;
        addLog("Lỗi ghi $actualFilename", 'error');
    }
}

$zip->close();
@unlink($tempZipFile);

$cleanLatest = ltrim($targetTag, 'v');
$newConfigContent = "<?php\nreturn [\n    'current_version' => '$cleanLatest'\n];\n";
@file_put_contents($configFile, $newConfigContent);
addLog("Đã cập nhật hệ thống lên v$cleanLatest.", 'success');

require_once __DIR__ . '/../../app/Core/UpdateChecker.php';
$checker = new \App\Core\UpdateChecker();
$checker->clearCache();

if (function_exists('opcache_reset')) @opcache_reset();
if (function_exists('apcu_clear_cache')) @apcu_clear_cache();

$status = $failCount === 0 ? 'success' : 'warning';
$message = $failCount === 0 ? 'Cập nhật hệ thống thành công!' : 'Cập nhật hoàn tất nhưng có một số tệp tin bị lỗi ghi đè.';

echo json_encode([
    'status' => $status,
    'message' => $message,
    'successCount' => $successCount,
    'failCount' => $failCount,
    'logs' => $logs
]);
