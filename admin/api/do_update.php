<?php
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

// Set headers for Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable Nginx buffering

// Ensure implicit flush is on
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
while (@ob_end_flush());
ob_implicit_flush(1);

function emitLog($message, $type = 'info', $progress = null, $complete = false) {
    $data = [
        'message' => $message,
        'type' => $type
    ];
    if ($progress !== null) $data['progress'] = $progress;
    if ($complete) $data['complete'] = true;
    
    echo "data: " . json_encode($data) . "\n\n";
    @ob_flush();
    flush();
    
    if ($complete) {
        exit;
    }
}

$targetTag = $_GET['download_url'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $targetTag = $data['download_url'] ?? '';
}

if (empty($targetTag)) {
    emitLog('Không tìm thấy phiên bản mục tiêu.', 'error', null, true);
}

// 1. Get Repo Configuration
$settings = getSettings();
if (isset($settings['allowAutoUpdate']) && $settings['allowAutoUpdate'] == 0) {
    emitLog('Tính năng cập nhật tự động đã bị TẮT bởi Quản trị viên trong phần Cài đặt.', 'error', null, true);
}
$repo = 'tuilakhoa/PhimTop1-CMS';

// Need to read config/update.php to get current version
$configFile = __DIR__ . '/../../config/update.php';
$configUpdate = file_exists($configFile) ? require $configFile : ['current_version' => '1.0.0'];
$currentVersion = $configUpdate['current_version'] ?? '1.0.0';

emitLog("Khởi tạo quá trình cập nhật (Github API)...", 'info', 5);
emitLog("Đang kết nối Github Repository: $repo", 'info', 10);

// 2. Fetch Diff from Github
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

// Try with 'v' prefix first, then without
$baseTag = 'v' . ltrim($currentVersion, 'v');
$compareUrl = "https://api.github.com/repos/$repo/compare/$baseTag...$targetTag";
$diff = callGithub($compareUrl);

if (!$diff) {
    // Try without 'v'
    $baseTag = ltrim($currentVersion, 'v');
    $compareUrl = "https://api.github.com/repos/$repo/compare/$baseTag...$targetTag";
    $diff = callGithub($compareUrl);
}

if (!$diff) {
    emitLog("Không thể so sánh phiên bản ($baseTag...$targetTag). Github API có thể bị giới hạn hoặc tag không tồn tại.", 'error', null, true);
}

if (empty($diff['files'])) {
    emitLog('Không tìm thấy tệp tin nào thay đổi giữa 2 phiên bản.', 'warning', 100, true);
}

$changedFiles = [];
foreach ($diff['files'] as $file) {
    if ($file['status'] === 'removed') continue;
    $changedFiles[] = $file['filename'];
}

$totalFiles = count($changedFiles);
emitLog('Phát hiện ' . $totalFiles . ' tệp tin thay đổi qua Github Diff.', 'success', 20);

// 3. Download Files
$rootDir = realpath(__DIR__ . '/../../');
$successCount = 0;
$failCount = 0;

$progressStep = 70 / ($totalFiles > 0 ? $totalFiles : 1);
$currentProgress = 20;

foreach ($changedFiles as $filename) {
    // Raw Github URL format: https://raw.githubusercontent.com/user/repo/tag/filename
    $fileUrl = "https://raw.githubusercontent.com/$repo/$targetTag/" . str_replace(' ', '%20', $filename);
    $targetPath = $rootDir . '/' . $filename;
    
    emitLog("Đang tải: $filename", 'info', $currentProgress);
    
    // Download raw file
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $fileContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($fileContent === false || $httpCode !== 200) {
        emitLog("Lỗi tải $filename (HTTP $httpCode)", 'error', clone $currentProgress);
        $failCount++;
    } else {
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        
        if (@file_put_contents($targetPath, $fileContent) !== false) {
            $successCount++;
        } else {
            emitLog("Lỗi ghi đè $filename (Kiểm tra quyền CHMOD)", 'error', clone $currentProgress);
            $failCount++;
        }
    }
    
    $currentProgress += $progressStep;
}

emitLog("Đã xử lý xong: Thành công ($successCount), Thất bại ($failCount).", $failCount > 0 ? 'warning' : 'success', 90);

// 4. Post-Update: Save new version & Clear Cache
if ($successCount > 0) {
    $cleanLatest = ltrim($targetTag, 'v');
    $newConfigContent = "<?php\nreturn [\n    'current_version' => '$cleanLatest'\n];\n";
    @file_put_contents($configFile, $newConfigContent);
    emitLog("Đã cập nhật cấu hình hệ thống lên phiên bản $cleanLatest.", 'info', 95);
    
    require_once __DIR__ . '/../../app/Core/UpdateChecker.php';
    $checker = new \App\Core\UpdateChecker();
    $checker->clearCache();
}

if ($failCount === 0) {
    emitLog('Cập nhật hệ thống thành công! Trình duyệt sẽ tải lại sau giây lát.', 'success', 100, true);
} else {
    emitLog('Cập nhật hoàn tất nhưng có một số tệp tin bị lỗi.', 'warning', 100, true);
}
