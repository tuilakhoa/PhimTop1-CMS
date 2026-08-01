<?php
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

require_once __DIR__ . '/../../app/Core/UpdateChecker.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$settings = getSettings();
$checker = new \App\Core\UpdateChecker();

$force = isset($_GET['force']) && $_GET['force'] == '1';
if ($force) {
    $checker->clearCache();
}

$result = $checker->check($force);
echo json_encode($result);
