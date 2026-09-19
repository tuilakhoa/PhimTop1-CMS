<?php
$content = file_get_contents("scratch/phimtop1-crawler/phimtop1-core.php");
$new_header = <<<EOT
/**
 * Plugin Name: PhimTop1 Crawler
 * Description: Hệ thống kho dữ liệu phim tập trung, cung cấp đầy đủ metadata (tiêu đề, mô tả, poster, diễn viên, thể loại, quốc gia, nguồn phát) thông qua API PhimTop1 đơn giản và linh hoạt.
 * Version: 1.0
 * Author: PhimTop1
 * Author URI: https://api.phimtop1.asia
 */
EOT;
$content = preg_replace('/\/\*\*.*?Auth.*?\*\//s', $new_header, $content);
file_put_contents("scratch/phimtop1-crawler/phimtop1-core.php", $content);
