<?php
$_SERVER['REQUEST_URI'] = '/admin/index.php?page=settings';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'action' => 'update_settings',
    'displayMode' => 'api',
    'dbType' => 'mysql',
    'dbHost' => '127.0.0.1',
    'dbName' => 'testdb',
    'dbUser' => 'root',
    'dbPass' => ''
];
session_start();
$_SESSION['admin'] = true;

ob_start();
require 'admin/index.php';
$output = ob_get_clean();
echo "Output length: " . strlen($output) . "\n";
if (strlen($output) == 0) {
    echo "BLANK SCREEN DETECTED\n";
    $error = error_get_last();
    var_dump($error);
} else {
    echo "NO BLANK SCREEN. Output starts with: \n" . substr($output, 0, 100);
}
