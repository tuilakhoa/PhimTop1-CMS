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

// Allow GET for EventSource, fallback to POST JSON if needed, but SSE uses GET.
$downloadUrl = $_GET['download_url'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $downloadUrl = $data['download_url'] ?? '';
}

// Fallback to settings if not provided
if (empty($downloadUrl)) {
    $settings = getSettings();
    $downloadUrl = $settings['updateServerUrl'] ?? '';
}

if (empty($downloadUrl)) {
    emitLog('Không tìm thấy đường dẫn tải xuống.', 'error', null, true);
}

emitLog('Khởi tạo quá trình cập nhật (File Sync)...', 'info', 5);

// 1. Download the JSON config
emitLog('Đang tải thông tin tệp tin từ máy chủ...', 'info', 15);
$jsonContent = @file_get_contents($downloadUrl);
if ($jsonContent === false) {
    emitLog('Không thể tải thông tin cập nhật từ máy chủ.', 'error', null, true);
}

$updateData = json_decode($jsonContent, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    emitLog('Định dạng dữ liệu từ máy chủ không hợp lệ.', 'error', null, true);
}

if (empty($updateData['changed_files']) || !is_array($updateData['changed_files'])) {
    emitLog('Không tìm thấy danh sách tệp tin cần cập nhật.', 'error', null, true);
}

$changedFiles = $updateData['changed_files'];
$totalFiles = count($changedFiles);
emitLog('Phát hiện ' . $totalFiles . ' tệp tin cần cập nhật.', 'success', 25);

$rootDir = realpath(__DIR__ . '/../../');
$baseUrl = rtrim(dirname($downloadUrl), '/');
$successCount = 0;
$failCount = 0;

$progressStep = 60 / ($totalFiles > 0 ? $totalFiles : 1);
$currentProgress = 30;

foreach ($changedFiles as $file) {
    $file = ltrim($file, '/');
    $fileUrl = $baseUrl . '/' . str_replace(' ', '%20', $file);
    $targetPath = $rootDir . '/' . $file;
    
    emitLog("Đang tải: $file", 'info', $currentProgress);
    
    $fileContent = @file_get_contents($fileUrl);
    if ($fileContent === false) {
        emitLog("Lỗi khi tải $file", 'error', clone $currentProgress);
        $failCount++;
    } else {
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        
        if (@file_put_contents($targetPath, $fileContent) !== false) {
            $successCount++;
        } else {
            emitLog("Lỗi khi ghi đè $file (Kiểm tra quyền)", 'error', clone $currentProgress);
            $failCount++;
        }
    }
    
    $currentProgress += $progressStep;
}

emitLog("Đã xử lý xong: Thành công ($successCount), Thất bại ($failCount).", $failCount > 0 ? 'warning' : 'success', 90);

// Clear UpdateChecker cache
require_once __DIR__ . '/../../app/Core/UpdateChecker.php';
$settings = getSettings();
$checker = new \App\Core\UpdateChecker($settings['updateServerUrl'] ?? null);
$checker->clearCache();

if ($failCount === 0) {
    emitLog('Cập nhật hệ thống thành công! Trình duyệt sẽ tải lại sau giây lát.', 'success', 100, true);
} else {
    emitLog('Cập nhật hoàn tất nhưng có một số tệp tin bị lỗi.', 'warning', 100, true);
}
