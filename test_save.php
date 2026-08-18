<?php
$_SERVER['REQUEST_URI'] = '/admin/index.php';
require 'includes/db.php';
$_POST['action'] = 'update_settings';
// Mock $_POST with basic settings
$updates = ['siteName' => 'Test'];
updateSettings($updates);
echo "DONE\n";
