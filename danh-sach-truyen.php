<?php
require_once __DIR__ . '/includes/db.php';

$handled = apply_filters('render_comic_list', false);

if (!$handled) {
    header("Location: /");
    exit;
}
