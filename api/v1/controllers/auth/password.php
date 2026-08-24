<?php
if ($action === 'forgot_password') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập email']);
        exit;
    }
    
    $fs = getFirestore();
    if ($fs) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng sử dụng tính năng quên mật khẩu của Firebase']);
        exit;
    }
    
    if ($pdo) {
        try {
            $pdo->exec("ALTER TABLE members ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
            $pdo->exec("ALTER TABLE members ADD COLUMN reset_expires DATETIME DEFAULT NULL");
        } catch (Exception $e) {}

        $stmt = $pdo->prepare("SELECT id, name FROM members WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            
            $stmt = $pdo->prepare("UPDATE members SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);
            
            if (!file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Chưa cài đặt PHPMailer. Vui lòng chạy composer install.']);
                exit;
            }
            require_once __DIR__ . '/../../../../vendor/autoload.php';
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                if (!empty($settings['smtpHost'])) {
                    $mail->isSMTP();
                    $mail->Host       = $settings['smtpHost'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $settings['smtpUser'];
                    $mail->Password   = $settings['smtpPass'];
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $settings['smtpPort'];
                } else {
                    $mail->isMail();
                }
                $mail->CharSet = 'UTF-8';
                $fromEmail = !empty($settings['smtpUser']) ? $settings['smtpUser'] : 'no-reply@' . $_SERVER['HTTP_HOST'];
                $mail->setFrom($fromEmail, $settings['siteName']);
                $mail->addAddress($email, $user['name']);
                
                $mail->isHTML(true);
                $mail->Subject = 'Yêu cầu đặt lại mật khẩu - ' . $settings['siteName'];
                
                $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/reset_password.php?token=" . $token;
                
                $mail->Body    = "Chào {$user['name']},<br><br>Bạn đã yêu cầu đặt lại mật khẩu. Vui lòng click vào link sau để đặt lại mật khẩu (có hiệu lực trong 1 giờ):<br><a href='{$resetLink}'>{$resetLink}</a><br><br>Nếu không phải bạn yêu cầu, vui lòng bỏ qua email này.";
                
                $mail->send();
                echo json_encode(['status' => 'success', 'message' => 'Email khôi phục đã được gửi. Kiểm tra hộp thư của bạn.']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => "Không thể gửi email: {$mail->ErrorInfo}"]);
            }
            exit;
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Nếu email tồn tại, email khôi phục đã được gửi.']);
            exit;
        }
    }
}

if ($action === 'reset_password') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? '';
    $newPassword = $input['password'] ?? '';
    
    if (empty($token) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Token và mật khẩu mới là bắt buộc']);
        exit;
    }
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE members SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            echo json_encode(['status' => 'success', 'message' => 'Đặt lại mật khẩu thành công.']);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Token không hợp lệ hoặc đã hết hạn.']);
        }
        exit;
    }
}
