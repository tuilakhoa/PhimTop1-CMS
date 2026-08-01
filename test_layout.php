<?php
$settings = ['displayMode' => 'list', 'theme' => 'netflix'];
$currentPage = 'dashboard';
include __DIR__ . '/admin/includes/header.php';
include __DIR__ . '/admin/includes/sidebar.php';
include __DIR__ . '/admin/pages/dashboard.php';
echo "</div></div></body></html>";
