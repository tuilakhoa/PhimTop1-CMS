<?php
$url = "https://sv1.otruyencdn.com/v1/api/chapter/66617db4a4468f0e0dda0a16";
$res = file_get_contents($url);
echo substr($res, 0, 1000) . "\n...\n";
$data = json_decode($res, true);
print_r(array_keys($data['data']['item']));
$chapterData = $data['data']['item'];
print_r($chapterData['chapter_image'][0] ?? null);
echo "\nDomain: " . $data['data']['domain_cdn'] . "\n";
