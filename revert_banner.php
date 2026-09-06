<?php
$current_index = file_get_contents('/home/khoa/Bản tải về/phimtop1cms/themes/phimhayok/index.php');
$old_index = file_get_contents('/tmp/old_index.php');

$old_lines = explode("\n", $old_index);
$current_lines = explode("\n", $current_index);

$old_block = implode("\n", array_slice($old_lines, 66, 101)); // lines 67-167 (101 lines)
$current_before = implode("\n", array_slice($current_lines, 0, 66)); // lines 1-66
$current_after = implode("\n", array_slice($current_lines, 334)); // line 335 to end

$new_content = $current_before . "\n" . $old_block . "\n" . $current_after;
file_put_contents('/home/khoa/Bản tải về/phimtop1cms/themes/phimhayok/index.php', $new_content);
echo "Reverted successfully\n";
