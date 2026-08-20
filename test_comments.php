<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require 'includes/db.php';
$repo = getCommentRepository();
$slug = 'test-slug';
$repo->addComment($slug, 'TestUser', 'This is a test', 'approved');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['slug'] = $slug;
ob_start();
require 'api/v1/comments.php';
$out = ob_get_clean();
echo "OUTPUT: \n$out\n";
