<?php
$start = microtime(true);
require 'index.php';
$end = microtime(true);
file_put_contents('speed.log', 'Load time: ' . ($end - $start) . ' seconds');
