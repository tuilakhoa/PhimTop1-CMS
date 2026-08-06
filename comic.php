<?php
require_once __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header("Location: /");
    exit;
}

$handled = apply_filters('render_comic_page', false, $slug);

if (!$handled) {
    // Plugin bị tắt hoặc không tồn tại
    header("Location: /");
    exit;
}
