<?php
/**
 * Helper để lấy dữ liệu Google Analytics Data API v1beta
 */

function getGaAccessToken($serviceAccount) {
    if (empty($serviceAccount) || empty($serviceAccount['private_key']) || empty($serviceAccount['client_email'])) {
        return false;
    }

    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $payload = json_encode([
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;

    $signature = '';
    $success = openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'sha256WithRSAEncryption');
    
    if (!$success) {
        return false;
    }

    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $signatureInput . "." . $base64UrlSignature;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return false;

    $tokenData = json_decode($response, true);
    return $tokenData['access_token'] ?? false;
}

function getGaRealtimeUsers($propertyId, $accessToken) {
    if (empty($propertyId) || empty($accessToken)) {
        return false;
    }

    $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runRealtimeReport";
    
    $payload = json_encode([
        'metrics' => [
            ['name' => 'activeUsers']
        ]
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return false; // Error fetching API
    }

    $data = json_decode($response, true);
    
    if (isset($data['rows'][0]['metricValues'][0]['value'])) {
        return $data['rows'][0]['metricValues'][0]['value'];
    }
    
    // API successful but no active users (empty rows)
    return '0';
}
