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
    $bot_dir = __DIR__ . '/../weibo_scanner';
    $json_file = $bot_dir . '/violators_result.json';
    if (file_exists($json_file)) {
        $json = file_get_contents($json_file);
        
        // TỰ ĐỘNG ĐỒNG BỘ: Đưa danh sách vi phạm vào thẳng cơ sở dữ liệu trang chủ
        $pdo = getPDO();
        if ($pdo) {
            $bot_results = json_decode($json, true);
            if (is_array($bot_results)) {
                // Tự động tạo bảng nếu chưa có
                $pdo->exec("CREATE TABLE IF NOT EXISTS actor_reports (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    actor_name VARCHAR(255) NOT NULL,
                    evidence_text TEXT NOT NULL,
                    evidence_url VARCHAR(500),
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    reported_by VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                foreach ($bot_results as $item) {
                    $stmt = $pdo->prepare("SELECT id FROM actor_reports WHERE actor_name = ?");
                    $stmt->execute([$item['name']]);
                    if (!$stmt->fetch()) {
                        $evidence = "Tự động phát hiện vi phạm từ khóa: " . $item['keyword_matched'];
                        $insert = $pdo->prepare("INSERT INTO actor_reports (actor_name, evidence_text, evidence_url, status, reported_by) VALUES (?, ?, ?, 'approved', 'Weibo Scanner Bot')");
                        $insert->execute([$item['name'], $evidence, $item['link']]);
                    }
                }
            }
        }
        
        // Trả về danh sách quét được lấy trực tiếp từ Database để hiển thị trên Admin
        $db_results = [];
        if ($pdo) {
            $stmt = $pdo->query("SELECT * FROM actor_reports WHERE reported_by = 'Weibo Scanner Bot' ORDER BY created_at DESC LIMIT 50");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $db_results[] = [
                    'name' => $row['actor_name'],
                    'user_type' => 'Ghi nhận từ Database',
                    'keyword_matched' => $row['evidence_text'],
                    'link' => $row['evidence_url']
                ];
            }
        }
        
        echo json_encode($db_results);
    } else {
        echo json_encode([]);
    }
elseif ($action == 'investigate') {
    $name = $_GET['name'] ?? '';
    if (empty($name)) {
        echo json_encode(['error' => 'Tên không hợp lệ']);
        exit;
    }
    
    $queue_file = __DIR__ . '/../weibo_scanner/task_queue.json';
    $queue = json_decode(file_get_contents($queue_file), true);
    
    $queue['investigate'] = [
        'status' => 'pending',
        'name' => $name,
        'result' => null,
        'timestamp' => time()
    ];
    
    file_put_contents($queue_file, json_encode($queue, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'queued', 'message' => 'Đã đưa yêu cầu điều tra vào hàng đợi. Cronjob sẽ xử lý ngay!']);
}
elseif ($action == 'check_investigate') {
    $queue_file = __DIR__ . '/../weibo_scanner/task_queue.json';
    $queue = json_decode(file_get_contents($queue_file), true);
    
    if ($queue['investigate']['status'] == 'completed') {
        $result = $queue['investigate']['result'];
        
        // Nếu có vi phạm, tự động lưu vào DB
        if (!isset($result['error']) && !empty($result['violations'])) {
            $pdo = getPDO();
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT id FROM actor_reports WHERE actor_name = ?");
                $stmt->execute([$result['actor']]);
                if (!$stmt->fetch()) {
                    $evidence = "Điều tra nhanh phát hiện vi phạm từ khóa: " . $result['violations'][0]['keyword'];
                    $insert = $pdo->prepare("INSERT INTO actor_reports (actor_name, evidence_text, evidence_url, status, reported_by) VALUES (?, ?, ?, 'approved', 'Weibo Scanner Bot')");
                    $insert->execute([$result['actor'], $evidence, $result['violations'][0]['link']]);
                }
            }
        }
        
        // Trả kết quả cho Web và reset queue
        echo json_encode($result);
        $queue['investigate']['status'] = 'idle';
        file_put_contents($queue_file, json_encode($queue, JSON_PRETTY_PRINT));
    } 
    else if ($queue['investigate']['status'] == 'error') {
        echo json_encode(['error' => $queue['investigate']['result']]);
        $queue['investigate']['status'] = 'idle';
        file_put_contents($queue_file, json_encode($queue, JSON_PRETTY_PRINT));
    }
    else {
        echo json_encode(['status' => 'processing']);
    }
}
elseif ($action == 'start_bot') {
    $queue_file = __DIR__ . '/../weibo_scanner/task_queue.json';
    $queue = json_decode(file_get_contents($queue_file), true);
    
    $queue['scan'] = [
        'status' => 'pending',
        'timestamp' => time()
    ];
    
    file_put_contents($queue_file, json_encode($queue, JSON_PRETTY_PRINT));
    echo json_encode(["status" => "success", "message" => "Đã đưa lệnh quét vào hàng đợi. Cronjob sẽ xử lý!"]);
}
?>
