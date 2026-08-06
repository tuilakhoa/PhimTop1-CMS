<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/db.php';

$settings = getSettings();
$apiKey = $settings['appApiKey'] ?? '';

// Verify API Key
$headers = getallheaders();
$clientApiKey = $headers['X-App-API-Key'] ?? ($_GET['key'] ?? '');

if (!empty($apiKey) && $clientApiKey !== $apiKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'data' => [
        'siteName' => $settings['siteName'] ?? 'PhimTop1',
        'logoUrl' => $settings['logoUrl'] ?? '',
        'maintenance' => false,
        'appBannerEnabled' => $settings['appBannerEnabled'] ?? 0,
        'appDownloadUrl' => $settings['appDownloadUrl'] ?? '',
        'appDownloadUrlTv' => $settings['appDownloadUrlTv'] ?? '',
        'enableComics' => (int)($settings['enableComics'] ?? 0),
        'isComicPluginActive' => in_array('comics', getActivePlugins()),
        'version' => '1.0'
    ]
]);
