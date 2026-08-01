<?php
$data = [
    "status" => true,
    "msg" => "success",
    "rss" => [
        "channel" => [
            "title" => "Movies",
            "item" => [
                ["title" => "Movie 1", "link" => "https://example.com/movie-1", "image" => "thumb1.jpg", "description" => "desc 1", "pubDate" => "2024"],
                ["title" => "Movie 2", "link" => "https://example.com/movie-2.html", "poster" => "poster2.jpg", "description" => "desc 2", "year" => "2023"]
            ]
        ]
    ]
];

function findLargestArray($array, &$largestArray) {
    if (!is_array($array)) return;
    $isAssoc = array_keys($array) !== range(0, count($array) - 1);
    if (!$isAssoc && count($array) > 0) {
        if (is_array($array[0]) && count($array) > count($largestArray)) {
            $largestArray = $array;
        }
    }
    foreach ($array as $value) {
        if (is_array($value)) {
            findLargestArray($value, $largestArray);
        }
    }
}

function extractField($item, $keys) {
    foreach ($keys as $k) {
        if (!empty($item[$k]) && !is_array($item[$k])) return $item[$k];
    }
    foreach ($item as $key => $val) {
        if (is_array($val)) continue;
        foreach ($keys as $k) {
            if (strtolower($key) === strtolower($k) && !empty($val)) return $val;
        }
    }
    return '';
}

$largestArray = [];
findLargestArray($data, $largestArray);
print_r($largestArray);

foreach ($largestArray as $item) {
    $rawSlug = extractField($item, ['slug', 'link', 'url']);
    if (filter_var($rawSlug, FILTER_VALIDATE_URL)) {
        $parts = explode('/', rtrim($rawSlug, '/'));
        $rawSlug = end($parts);
    }
    $rawSlug = str_replace('.html', '', $rawSlug);
    
    echo "Title: " . extractField($item, ['name', 'title', 'title_vi']) . "\n";
    echo "Slug: " . $rawSlug . "\n";
    echo "Thumb: " . extractField($item, ['thumb_url', 'image', 'thumbnail', 'poster_url']) . "\n";
    echo "Year: " . extractField($item, ['year', 'release_date', 'pubDate']) . "\n\n";
}
