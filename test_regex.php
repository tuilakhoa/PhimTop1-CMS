<?php
$str = "có phim trinh thám nào hay không";
$sw = "nào hay không";
echo preg_replace('/\b' . preg_quote($sw, '/') . '\b/iu', ' ', $str) . "\n";
