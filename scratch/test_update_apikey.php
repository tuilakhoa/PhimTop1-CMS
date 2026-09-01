<?php
require_once 'includes/db.php';
$updates = ['appApiKey' => 'test_12345'];
echo "Updating settings...\n";
updateSettings($updates);
echo "Settings updated.\n";
$settings = getSettings();
echo "New API Key: " . ($settings['appApiKey'] ?? 'NULL') . "\n";
