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

addLog("Khởi tạo cập nhật (Github API)...");

function callGithub($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PhimTop1-CMS-Updater');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) return json_decode($response, true);
    return false;
}

$baseTag = 'v' . ltrim($currentVersion, 'v');
$compareUrl = "https://api.github.com/repos/$repo/compare/$baseTag...$targetTag";
$diff = callGithub($compareUrl);

if (!$diff) {
    $baseTag = ltrim($currentVersion, 'v');
    $compareUrl = "https://api.github.com/repos/$repo/compare/$baseTag...$targetTag";
    $diff = callGithub($compareUrl);
}

if (!$diff) {
    echo json_encode(['status' => 'error', 'message' => "Không thể kết nối hoặc so sánh phiên bản ($baseTag...$targetTag).", 'logs' => $logs]);
    exit;
}

$changedFiles = [];
$removedFiles = [];
foreach ($diff['files'] as $file) {
    if ($file['status'] === 'removed') {
        $removedFiles[] = $file['filename'];
    } else {
        $changedFiles[] = $file['filename'];
    }
}

$totalFiles = count($changedFiles) + count($removedFiles);
addLog("Phát hiện $totalFiles tệp tin thay đổi.");

$rootDir = realpath(__DIR__ . '/../../');
$successCount = 0;
$failCount = 0;

foreach ($changedFiles as $filename) {
    $fileUrl = "https://raw.githubusercontent.com/$repo/$targetTag/" . str_replace(' ', '%20', $filename);
    
    $actualFilename = $filename;
    $adminFolderName = ltrim($settings['adminPath'] ?? '/admin', '/');
    if ($adminFolderName !== 'admin' && strpos($filename, 'admin/') === 0) {
        $actualFilename = $adminFolderName . '/' . substr($filename, 6);
    }
    
    $targetPath = $rootDir . '/' . $actualFilename;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $fileContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($fileContent === false || $httpCode !== 200) {
        $failCount++;
        addLog("Lỗi tải $filename", 'error');
    } else {
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
        
        if (@file_put_contents($targetPath, $fileContent) !== false) {
            $successCount++;
        } else {
            $failCount++;
            addLog("Lỗi ghi $filename", 'error');
        }
    }
}

foreach ($removedFiles as $filename) {
    $actualFilename = $filename;
    $adminFolderName = ltrim($settings['adminPath'] ?? '/admin', '/');
    if ($adminFolderName !== 'admin' && strpos($filename, 'admin/') === 0) {
        $actualFilename = $adminFolderName . '/' . substr($filename, 6);
    }
    
    $targetPath = $rootDir . '/' . $actualFilename;
    if (file_exists($targetPath)) {
        if (@unlink($targetPath)) {
            $successCount++;
            $parentDir = dirname($targetPath);
            if (is_dir($parentDir) && count(scandir($parentDir)) == 2) @rmdir($parentDir);
        } else {
            addLog("Lỗi xoá $filename", 'warning');
        }
    } else {
        $successCount++;
    }
}

if ($successCount > 0 || $totalFiles == 0) {
    $cleanLatest = ltrim($targetTag, 'v');
    $newConfigContent = "<?php\nreturn [\n    'current_version' => '$cleanLatest'\n];\n";
    @file_put_contents($configFile, $newConfigContent);
    addLog("Đã cập nhật hệ thống lên v$cleanLatest.", 'success');
    
    require_once __DIR__ . '/../../app/Core/UpdateChecker.php';
    $checker = new \App\Core\UpdateChecker();
    $checker->clearCache();
    
    if (function_exists('opcache_reset')) @opcache_reset();
    if (function_exists('apcu_clear_cache')) @apcu_clear_cache();
}

$status = $failCount === 0 ? 'success' : 'warning';
$message = $failCount === 0 ? 'Cập nhật hệ thống thành công!' : 'Cập nhật hoàn tất nhưng có một số tệp tin bị lỗi.';

echo json_encode([
    'status' => $status,
    'message' => $message,
    'successCount' => $successCount,
    'failCount' => $failCount,
    'logs' => $logs
]);
