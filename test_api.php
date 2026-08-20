<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['slug'] = 'squid-game-phan-2';
require 'api/v1/comments.php';
