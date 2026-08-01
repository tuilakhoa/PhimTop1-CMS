<?php
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

require_once __DIR__ . '/../../app/Core/UpdateChecker.php';

header('Content-Type: application/json');

$settings = getSettings();
$checker = new \App\Core\UpdateChecker($settings['updateServerUrl'] ?? null);

$force = isset($_GET['force']) && $_GET['force'] == '1';
if ($force) {
    $checker->clearCache();
}

$result = $checker->check($force);
echo json_encode($result);
