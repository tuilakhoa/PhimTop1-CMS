<?php
$slug = 'xuyen-thanh-tieu-muoi-cua-dai-lao-giau-mat';
$urlDetail = "https://otruyenapi.com/v1/api/truyen-tranh/$slug";
$resDetail = file_get_contents($urlDetail);
$detailData = json_decode($resDetail, true);
echo "KEYS of item:\n";
print_r(array_keys($detailData['data']['item']));
echo "\nchapters key exists?\n";
if (isset($detailData['data']['item']['chapters'])) {
    echo "Yes, chapters found.\n";
    $firstChapter = $detailData['data']['item']['chapters'][0]['server_data'][0] ?? null;
    print_r($firstChapter);
} else {
    // maybe chapters are outside item?
    echo "KEYS of data:\n";
    print_r(array_keys($detailData['data']));
    
    // what if chapters are in 'item' but named differently?
    foreach ($detailData['data']['item'] as $key => $val) {
        if (is_array($val) && count($val) > 0) {
            echo "Key $key is array with " . count($val) . " elements\n";
        }
    }
}
