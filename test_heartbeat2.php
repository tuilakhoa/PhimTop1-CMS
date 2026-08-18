<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'heartbeat';

// Create a test payload
$payload = json_encode([
    'device_id' => 'web-test1234',
    'device_name' => 'Chrome',
    'platform' => 'web',
    'movie_slug' => 'test-movie',
    'movie_name' => 'Test Movie',
    'episode_name' => 'Tập 1',
    'user_name' => 'Admin',
    'is_logged_in' => 1,
    'progress' => 150
]);

// Since we cannot mock php://input easily, let's mock $_POST and modify the script to use $_POST
$_POST = json_decode($payload, true);

ob_start();
require 'api/v1/watching_session.php';
$output = ob_get_clean();
echo "Output: " . $output . "\n";
