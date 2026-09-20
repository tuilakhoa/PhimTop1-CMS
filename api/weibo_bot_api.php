<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Trả về trạng thái hoạt động của Bot
$action = isset($_GET['action']) ? $_GET['action'] : 'status';

if ($action == 'status') {
    $status_file = __DIR__ . '/../weibo_scanner/status.json';
    if (file_exists($status_file)) {
        echo file_get_contents($status_file);
    } else {
        echo json_encode(["state" => "chưa khởi động", "message" => "Chưa có thông tin tiến trình"]);
    }
} 
elseif ($action == 'results') {
    $result_file = __DIR__ . '/../weibo_scanner/violators_result.json';
    if (file_exists($result_file)) {
        echo file_get_contents($result_file);
    } else {
        echo json_encode([]);
    }
}
elseif ($action == 'start_bot') {
    // API gọi để khởi chạy bot chạy ngầm bằng command (dành cho Linux)
    $cmd = "cd " . escapeshellarg(__DIR__ . '/../weibo_scanner') . " && source venv/bin/activate && python3 weibo_scanner_bot.py > bot_log.txt 2>&1 &";
    exec($cmd);
    echo json_encode(["status" => "success", "message" => "Đã gửi lệnh chạy bot nền."]);
}
?>
