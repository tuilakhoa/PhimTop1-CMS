<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
ob_start();
require 'index.php';
$html = ob_get_clean();
$lines = explode("\n", $html);
$imgLines = array_filter($lines, function($l) { return strpos($l, '<img') !== false; });
echo implode("\n", array_slice($imgLines, 0, 10));
