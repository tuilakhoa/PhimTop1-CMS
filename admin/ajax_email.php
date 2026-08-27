<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'get_total') {
    $pdo = getPDO();
    if (!$pdo) {
        echo json_encode(['error' => 'No database connection']);
        exit;
    }
    $stmt = $pdo->query("SELECT COUNT(*) FROM members WHERE email IS NOT NULL AND email != ''");
    $total = $stmt->fetchColumn();
    echo json_encode(['total' => $total]);
    exit;
}

if ($action === 'test_email') {
    $testEmail = $input['test_email'] ?? '';
    if (empty($testEmail)) {
        echo json_encode(['error' => 'Vui lòng nhập email người nhận.']);
        exit;
    }

    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        echo json_encode(['error' => 'Chưa cài đặt PHPMailer. Vui lòng chạy composer install trong terminal.']);
        exit;
    }
    require_once __DIR__ . '/../vendor/autoload.php';

    $settings = getSettings();
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        if (!empty($settings['smtpHost'])) {
            $mail->isSMTP();
            $mail->Host       = $settings['smtpHost'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['smtpUser'] ?? '';
            $mail->Password   = $settings['smtpPass'] ?? '';
            $mail->SMTPSecure = (((int)($settings['smtpPort'] ?? 587)) === 465) ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $settings['smtpPort'] ?? 587;
        } else {
            $mail->isMail();
        }
        $mail->CharSet = 'UTF-8';
        $fromEmail = !empty($settings['smtpUser']) ? $settings['smtpUser'] : 'no-reply@' . $_SERVER['HTTP_HOST'];
        
        $mail->setFrom($fromEmail, $settings['siteName'] ?? 'PhimTop1');
        $mail->addAddress($testEmail);
        
        $mail->isHTML(true);
        $mail->Subject = "Email thử nghiệm từ PhimTop1-CMS";
        $mail->Body = "Chào bạn,<br><br>Đây là email thử nghiệm để kiểm tra cấu hình SMTP của hệ thống PhimTop1-CMS.<br>Nếu bạn nhận được email này, nghĩa là tính năng gửi email đã hoạt động tốt.<br><br>Trân trọng,<br>Ban Quản Trị.";
        
        if ($mail->send()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Không thể gửi email.']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => "Lỗi gửi mail: {$mail->ErrorInfo}"]);
    }
    exit;
}

if ($action === 'send_batch') {
    $page = (int)($input['page'] ?? 1);
    $limit = (int)($input['limit'] ?? 10);
    $subject = $input['subject'] ?? '';
    $message = $input['message'] ?? '';
    $movieSource = $input['movieSource'] ?? 'manual';
    $movieSlugsStr = $input['movieSlugs'] ?? '';
    
    $movieSlugs = [];
    if ($movieSource === 'manual' && !empty(trim($movieSlugsStr))) {
        $parts = explode(',', $movieSlugsStr);
        foreach ($parts as $p) {
            $val = trim($p);
            if ($val) $movieSlugs[] = $val;
        }
    }

    $pdo = getPDO();
    $offset = ($page - 1) * $limit;

    // Fetch members
    $stmt = $pdo->prepare("SELECT email, name FROM members WHERE email IS NOT NULL AND email != '' LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($members)) {
        echo json_encode(['success' => true, 'count' => 0, 'finished' => true]);
        exit;
    }

    // Fetch movies info
    $moviesHtml = '';
    $movies = [];
    $settings = getSettings();
    $displayMode = $settings['displayMode'] ?? 'api';
    
    if ($displayMode === 'api') {
        require_once __DIR__ . '/../includes/api_client.php';
        if ($movieSource === 'newest' || $movieSource === 'trending') {
            // API mode usually only supports "newest" via home endpoint
            $apiData = fetchApiFilms('home', '', 1);
            if ($apiData && !empty($apiData['items'])) {
                $movies = array_slice($apiData['items'], 0, 6);
            }
        } elseif ($movieSource === 'manual' && !empty($movieSlugs)) {
            foreach ($movieSlugs as $slug) {
                $detail = fetchApiMovieDetail($slug);
                if ($detail && !empty($detail['movie'])) {
                    $m = $detail['movie'];
                    $m['thumb_url'] = $m['thumb_url'] ?? $m['poster_url'] ?? '';
                    $movies[] = $m;
                }
            }
        }
        
        // Normalize URLs for API mode
        if (!empty($movies)) {
            $apiSource = $settings['apiSource'] ?? 'kkphim';
            $domain = $apiSource === 'nguonc' ? '' : 'https://phimimg.com/';
            foreach ($movies as &$m) {
                $thumb = $m['thumb_url'] ?? $m['poster_url'] ?? '';
                if (!preg_match('/^http/', $thumb) && $thumb) {
                    $m['thumb_url'] = rtrim($domain, '/') . '/' . ltrim($thumb, '/');
                }
            }
        }
    } else {
        // Crawl Mode (DB)
        if ($movieSource === 'newest') {
            $stmt = $pdo->query("SELECT name, slug, thumb_url FROM movies ORDER BY updated_at DESC LIMIT 6");
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($movieSource === 'trending') {
            $stmt = $pdo->query("SELECT name, slug, thumb_url FROM movies ORDER BY view DESC LIMIT 6");
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($movieSource === 'manual' && !empty($movieSlugs)) {
            $inQuery = implode(',', array_fill(0, count($movieSlugs), '?'));
            $stmt = $pdo->prepare("SELECT name, slug, thumb_url FROM movies WHERE slug IN ($inQuery)");
            $stmt->execute($movieSlugs);
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    if (!empty($movies)) {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $slugWatch = $settings['slugWatch'] ?? 'xem-phim';

        $moviesHtml .= '<div style="display:flex; flex-wrap:wrap; gap:15px; margin-top:20px;">';
        foreach ($movies as $movie) {
            $link = $baseUrl . '/' . $slugWatch . '/' . $movie['slug'] . '/tap-1';
            $moviesHtml .= '<div style="width:150px; text-align:center;">';
            $moviesHtml .= '<a href="' . $link . '"><img src="' . htmlspecialchars($movie['thumb_url']) . '" style="width:100%; border-radius:8px;" /></a>';
            $moviesHtml .= '<p style="margin-top:5px; font-weight:bold;">' . htmlspecialchars($movie['name']) . '</p>';
            $moviesHtml .= '</div>';
        }
        $moviesHtml .= '</div>';
    }

    // Setup PHPMailer
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        echo json_encode(['error' => 'Chưa cài đặt PHPMailer. Vui lòng chạy composer install trong terminal.']);
        exit;
    }
    require_once __DIR__ . '/../vendor/autoload.php';

    $settings = getSettings();
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    $sentCount = 0;
    try {
        if (!empty($settings['smtpHost'])) {
            $mail->isSMTP();
            $mail->Host       = $settings['smtpHost'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['smtpUser'] ?? '';
            $mail->Password   = $settings['smtpPass'] ?? '';
            $mail->SMTPSecure = (((int)($settings['smtpPort'] ?? 587)) === 465) ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $settings['smtpPort'] ?? 587;
        } else {
            $mail->isMail();
        }
        $mail->CharSet = 'UTF-8';
        $fromEmail = !empty($settings['smtpUser']) ? $settings['smtpUser'] : 'no-reply@' . $_SERVER['HTTP_HOST'];
        
        foreach ($members as $member) {
            $mail->clearAddresses();
            $mail->setFrom($fromEmail, $settings['siteName'] ?? 'PhimTop1');
            $mail->addAddress($member['email'], $member['name']);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            $body = "Chào {$member['name']},<br><br>" . nl2br($message);
            if ($moviesHtml) {
                $body .= "<br><br><strong>Các phim nổi bật gửi đến bạn:</strong><br>" . $moviesHtml;
            }
            $body .= "<br><br><hr><small>Email này được gửi tự động. Vui lòng không trả lời.</small>";
            
            $mail->Body = $body;
            
            if ($mail->send()) {
                $sentCount++;
            }
        }
        
        echo json_encode(['success' => true, 'count' => $sentCount, 'finished' => false]);
    } catch (Exception $e) {
        echo json_encode(['error' => "Lỗi gửi mail: {$mail->ErrorInfo}"]);
    }
    exit;
}
