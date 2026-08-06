<?php
require_once __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$chap = $_GET['chap'] ?? '';

if (!$slug || !$chap) {
    header("Location: /");
    exit;
}

$handled = apply_filters('render_read_page', false, $slug, $chap);

if (!$handled) {
    header("Location: /");
    exit;
}
