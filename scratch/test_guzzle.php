<?php
require_once('vendor/autoload.php');
$client = new \GuzzleHttp\Client();
try {
$response = $client->request('GET', 'https://api-kp.devwithai.net/v1/api/v2/movie/nguoi-phu-nu-duoi-day-ho', [
 'headers' => [
  'accept' => 'application/json'
 ]
]);
echo $response->getBody();
} catch (Exception $e) { echo $e->getMessage(); }
