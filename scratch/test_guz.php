<?php
require_once "vendor/autoload.php";
$client = new \GuzzleHttp\Client(['timeout' => 5, 'verify' => false]);
try {
    $response = $client->request('GET', 'https://api-kp.devwithai.net/v1/api/v2/movie/nguoi-phu-nu-duoi-day-ho', [
        'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0'
        ]
    ]);
    echo $response->getBody();
} catch (Exception $e) {
    echo $e->getMessage();
}
