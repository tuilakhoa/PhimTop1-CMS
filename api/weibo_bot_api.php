<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';

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
                        $evidence = "Phát hiện từ khóa: " . $item['keyword_matched'];
                        if (isset($item['ai_reason'])) {
                            $evidence .= " | Lời bình AI: " . $item['ai_reason'];
                        }
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
}
elseif ($action == 'investigate') {
    $name = $_GET['name'] ?? '';
    if (empty($name)) {
        echo json_encode(['error' => 'Tên không hợp lệ']);
        exit;
    }
    
    $bot_dir = __DIR__ . '/../weibo_scanner';
    $python_path = $bot_dir . '/venv/bin/python3';
    $script_path = $bot_dir . '/investigate_actor.py';
    
    // Đưa API Key từ hệ thống vào config.json của Bot
    $settings = getSettings();
    $config_path = $bot_dir . '/config.json';
    if (file_exists($config_path)) {
        $config = json_decode(file_get_contents($config_path), true);
        if (isset($settings['geminiApiKey']) && !empty($settings['geminiApiKey'])) {
            $config['gemini_api_key'] = $settings['geminiApiKey'];
        }
        if (isset($settings['openaiApiKey']) && !empty($settings['openaiApiKey'])) {
            $config['openai_api_key'] = $settings['openaiApiKey'];
        }
        $config['ai_provider'] = $settings['aiProvider'] ?? 'gemini';
        
        file_put_contents($config_path, json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    
    // Mã hóa base64 để tránh lỗi escapeshellarg làm mất ký tự tiếng Trung trên một số máy chủ
    $base64_name = base64_encode($name);
    
    // Thêm cd để Python có thể đọc đúng các file config.json nằm cùng thư mục
    $cmd = "cd " . escapeshellarg($bot_dir) . " && " . escapeshellcmd($python_path) . " " . escapeshellarg($script_path) . " " . escapeshellarg($base64_name) . " 2>&1";
    $output = shell_exec($cmd);
    
    if (!$output) {
        echo json_encode(['error' => 'Lỗi thực thi Bot']);
        exit;
    }
    
    $result = json_decode($output, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Trả về RAW output để JS hiển thị thẳng lên màn hình
        echo "RAW_ERROR: " . $output;
        exit;
    }
    
    // Nếu có vi phạm, tự động lưu vào DB
    if (!isset($result['error']) && !empty($result['violations'])) {
        $pdo = getPDO();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT id FROM actor_reports WHERE actor_name = ?");
            $stmt->execute([$result['actor']]);
            if (!$stmt->fetch()) {
                $evidence = "Điều tra nhanh phát hiện từ khóa: " . $result['violations'][0]['keyword'];
                if (isset($result['violations'][0]['ai_reason'])) {
                    $evidence .= " | Lời bình AI: " . $result['violations'][0]['ai_reason'];
                }
                $insert = $pdo->prepare("INSERT INTO actor_reports (actor_name, evidence_text, evidence_url, status, reported_by) VALUES (?, ?, ?, 'approved', 'Weibo Scanner Bot')");
                $insert->execute([$result['actor'], $evidence, $result['violations'][0]['link']]);
            }
        }
    }
    
    echo $output;
}
elseif ($action == 'start_bot') {
    // Đưa API Key từ hệ thống vào config.json của Bot
    $settings = getSettings();
    $bot_dir = __DIR__ . '/../weibo_scanner';
    $config_path = $bot_dir . '/config.json';
    if (file_exists($config_path)) {
        $config = json_decode(file_get_contents($config_path), true);
        if (isset($settings['geminiApiKey']) && !empty($settings['geminiApiKey'])) {
            $config['gemini_api_key'] = $settings['geminiApiKey'];
        }
        if (isset($settings['openaiApiKey']) && !empty($settings['openaiApiKey'])) {
            $config['openai_api_key'] = $settings['openaiApiKey'];
        }
        $config['ai_provider'] = $settings['aiProvider'] ?? 'gemini';
        
        file_put_contents($config_path, json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    
    // Gọi thẳng python3 bên trong thư mục venv thay vì dùng lệnh source (bị lỗi trên một số Web Server)
    // Dùng nohup để tránh lỗi treo PHP-FPM trên aaPanel khi gọi lệnh chạy ngầm
    $cmd = "cd " . escapeshellarg($bot_dir) . " && nohup ./venv/bin/python3 weibo_scanner_bot.py > bot_log.txt 2>&1 &";
    exec($cmd);
    echo json_encode(["status" => "success", "message" => "Đã gửi lệnh chạy bot nền."]);
}
?>
