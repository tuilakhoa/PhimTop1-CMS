<?php
require_once __DIR__ . '/includes/api_client.php';
require_once __DIR__ . '/includes/db.php';
$res = fetchApiFilms('danh-sach', 'phim-moi-cap-nhat', 1);
print_r(array_keys($res));
print_r($res['pagination'] ?? 'no pagination');
