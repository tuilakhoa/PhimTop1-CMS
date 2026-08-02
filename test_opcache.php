<?php
$configFile = __DIR__ . '/config/update.php';
$configUpdate = require $configFile;
echo "require: " . $configUpdate['current_version'] . "\n";
echo "file_get_contents: " . file_get_contents($configFile);
