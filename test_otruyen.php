<?php
$url = "https://otruyenapi.com/v1/api/home";
$res = file_get_contents($url);
echo "HOME API:\n";
echo substr($res, 0, 500) . "\n\n";

$data = json_decode($res, true);
$slug = $data['data']['items'][0]['slug'] ?? 'khong-co';

echo "SLUG: $slug\n\n";

$urlDetail = "https://otruyenapi.com/v1/api/truyen-tranh/$slug";
$resDetail = file_get_contents($urlDetail);
echo "DETAIL API:\n";
$detailData = json_decode($resDetail, true);
// Just print structure of episodes/chapters
if (isset($detailData['data']['item']['episodes'])) {
    echo "Chapters found: " . count($detailData['data']['item']['episodes'][0]['server_data'] ?? []) . "\n";
    print_r($detailData['data']['item']['episodes'][0]['server_data'][0] ?? null);
} else {
    echo "No episodes found.\n";
}

// Let's also check a chapter api if there is one? Or does it provide image urls in server_data?
