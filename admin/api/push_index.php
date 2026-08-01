<?php
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

$settings = getSettings();
$url = $_POST['url'] ?? '';
$pingGoogle = isset($_POST['pingGoogle']);
$pingIndexNow = isset($_POST['pingIndexNow']);

if (empty($url)) {
    die("Lỗi: URL không được để trống.");
}

$output = "Đang ping URL: $url\n\n";

// BING INDEX NOW
if ($pingIndexNow && !empty($settings['indexNowKey'])) {
    $output .= ">>> PING INDEXNOW (BING)...\n";
    $host = parse_url($url, PHP_URL_HOST);
    $key = trim($settings['indexNowKey']);
    $data = [
        "host" => $host,
        "key" => $key,
        "keyLocation" => "https://{$host}/{$key}.txt",
        "urlList" => [$url]
    ];
    
    $ch = curl_init("https://api.indexnow.org/indexnow");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 || $httpCode == 202) {
        $output .= "[OK] IndexNow: Thành công (HTTP $httpCode)\n";
    } else {
        $output .= "[LỖI] IndexNow: HTTP $httpCode - " . htmlspecialchars($response) . "\n";
    }
    $output .= "\n";
} else if ($pingIndexNow) {
    $output .= ">>> PING INDEXNOW (BING): Bỏ qua do chưa cấu hình Key.\n\n";
}

// GOOGLE WEB INDEXING API
if ($pingGoogle && !empty($settings['googleIndexJson'])) {
    $output .= ">>> PING GOOGLE WEB INDEXING...\n";
    $json = json_decode($settings['googleIndexJson'], true);
    
    if (json_last_error() === JSON_ERROR_NONE && isset($json['client_email']) && isset($json['private_key'])) {
        // Hàm hỗ trợ tạo Base64Url
        function base64url_encode($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = base64url_encode($header);
        $base64UrlPayload = base64url_encode($payload);
        $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;
        
        openssl_sign($signatureInput, $signature, $json['private_key'], 'sha256');
        $jwt = $signatureInput . "." . base64url_encode($signature);
        
        // Lấy Access Token
        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        
        $tokenData = json_decode($response, true);
        
        if (isset($tokenData['access_token'])) {
            $accessToken = $tokenData['access_token'];
            
            // Publish URL
            $ch = curl_init("https://indexing.googleapis.com/v3/urlNotifications:publish");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'url' => $url,
                'type' => 'URL_UPDATED'
            ]));
            
            $publishRes = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $output .= "[OK] Google Indexing: Đã yêu cầu lập chỉ mục thành công!\n";
            } else {
                $output .= "[LỖI] Google Indexing: Bị từ chối (HTTP $httpCode)\nChi tiết: $publishRes\n";
            }
        } else {
            $output .= "[LỖI] Google Auth: Không lấy được Access Token.\nChi tiết: $response\n";
        }
        
    } else {
        $output .= "[LỖI] Cấu hình JSON của Google không hợp lệ hoặc thiếu thông tin client_email/private_key.\n";
    }
} else if ($pingGoogle) {
    $output .= ">>> PING GOOGLE WEB INDEXING: Bỏ qua do chưa dán file JSON cấu hình.\n\n";
}

echo '<body style="color:#00ff00;font-family:monospace;font-size:12px;margin:0;background:#000;">' . nl2br(htmlspecialchars($output)) . '</body>';
?>
