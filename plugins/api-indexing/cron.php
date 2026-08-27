<?php
require_once __DIR__ . '/../../includes/db.php';
$settings = getSettings();

$displayMode = $settings['displayMode'] ?? 'api';
$movies = [];

if ($displayMode === 'api') {
    $apiUrl = "https://phimapi.com/danh-sach/phim-moi-cap-nhat?page=1";
    $json = @file_get_contents($apiUrl);
    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $movies[] = $item['slug'] ?? '';
            }
        }
    }
} else {
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->query("SELECT slug FROM movies ORDER BY updated_at DESC LIMIT 20");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $movies[] = $row['slug'];
        }
    }
}

$movies = array_filter($movies);
if (empty($movies)) {
    die("Không có phim nào.");
}

$pushedFile = __DIR__ . '/pushed_history.json';
$pushed = file_exists($pushedFile) ? json_decode(file_get_contents($pushedFile), true) : [];
if (!is_array($pushed)) $pushed = [];

$toPush = [];
foreach ($movies as $slug) {
    if (!in_array($slug, $pushed)) {
        $toPush[] = $slug;
    }
}

if (empty($toPush)) {
    die("Không có phim mới cần push.");
}

$slugMovie = $settings['slugMovie'] ?? 'phim';
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if ($host === 'localhost' && isset($settings['siteUrl'])) {
    $parsed = parse_url($settings['siteUrl']);
    if (isset($parsed['host'])) {
        $host = $parsed['host'];
        $protocol = $parsed['scheme'] ?? 'https';
    }
}
$baseUrl = rtrim($protocol . "://" . $host, '/');

$pushedCount = 0;
foreach ($toPush as $slug) {
    $url = $baseUrl . "/{$slugMovie}/" . $slug;
    
    // GOOGLE PUSH
    if (!empty($settings['googleIndexJson'])) {
        $json = json_decode($settings['googleIndexJson'], true);
        if (json_last_error() === JSON_ERROR_NONE && isset($json['client_email']) && isset($json['private_key'])) {
            if (!function_exists('base64url_encode')) {
                function base64url_encode($data) {
                    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
                }
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
            $signatureInput = base64url_encode($header) . "." . base64url_encode($payload);
            openssl_sign($signatureInput, $signature, $json['private_key'], 'sha256');
            $jwt = $signatureInput . "." . base64url_encode($signature);
            
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
                curl_exec($ch);
                curl_close($ch);
            }
        }
    }
    
    // INDEXNOW PUSH
    if (!empty($settings['indexNowKey'])) {
        $parsedHost = parse_url($url, PHP_URL_HOST) ?? $host;
        $key = trim($settings['indexNowKey']);
        $data = [
            "host" => $parsedHost,
            "key" => $key,
            "keyLocation" => "https://{$parsedHost}/{$key}.txt",
            "urlList" => [$url]
        ];
        $ch = curl_init("https://api.indexnow.org/indexnow");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_exec($ch);
        curl_close($ch);
    }
    
    $pushed[] = $slug;
    $pushedCount++;
    if ($pushedCount >= 10) break;
}

if (count($pushed) > 500) {
    $pushed = array_slice($pushed, -500);
}
file_put_contents($pushedFile, json_encode($pushed));

echo "Đã push tự động thành công $pushedCount URL mới.";
