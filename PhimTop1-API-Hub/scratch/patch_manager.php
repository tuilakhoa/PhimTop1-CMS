<?php
$file = "scratch/phimtop1-crawler/template/backend/manager.php";
$content = file_get_contents($file);
$content = str_replace('https://phimtop1.vip/storage/uploads/logo/logo-1766542694.png', 'https://ui-avatars.com/api/?name=P1&background=e94560&color=fff&size=128', $content);
$content = str_replace('v1.0.1', 'v1.0', $content);
$content = str_replace('PhimTop1.vip &mdash; Hệ thống kho dữ liệu phim tập trung, cung cấp đầy đủ metadata
                (tiêu đề, mô tả, poster, diễn viên, thể loại, quốc gia, nguồn phát) cho các nền tảng xem phim
                thông qua API đơn giản và linh hoạt.', 'Hệ thống kho dữ liệu phim tập trung, cung cấp đầy đủ metadata (tiêu đề, mô tả, poster, diễn viên, thể loại, quốc gia, nguồn phát) cho các nền tảng xem phim thông qua hệ thống API đơn giản và linh hoạt.', $content);
$content = str_replace('https://phimtop1.vip/api', 'https://api.phimtop1.asia/api-document', $content);
$content = str_replace('https://phimtop1.vip', 'https://api.phimtop1.asia/cms', $content);
$content = str_replace('https://t.me/hotrokhmovie', 'https://t.me/phimtop1', $content);
$content = str_replace('https://github.com/ofilmcms', 'https://github.com/phimtop1cms', $content);
file_put_contents($file, $content);
