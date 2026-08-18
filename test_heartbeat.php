<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'heartbeat';
$payload = json_encode([
    'device_id' => 'web-12345',
    'device_name' => 'Test Web',
    'platform' => 'web',
    'movie_slug' => 'test-movie',
    'movie_name' => 'Test Movie',
    'episode_name' => 'Ep 1',
    'user_name' => 'Guest',
    'is_logged_in' => 0,
    'progress' => 123
]);
file_put_contents('php://memory', $payload);
// Mock php://input
// Well, we can't easily mock php://input for file_get_contents in a simple script unless we do something hacky.
