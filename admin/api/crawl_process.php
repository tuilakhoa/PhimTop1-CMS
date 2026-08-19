<?php
require_once __DIR__ . '/../../includes/db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

set_time_limit(0);
header('Content-Type: application/json');

$source = $_GET['source'] ?? 'kkphim';
$type = $_GET['type'] ?? 'phim-moi-cap-nhat';
$page = (int)($_GET['page'] ?? 1);
$dl = (int)($_GET['dl'] ?? 0);
$tw = (int)($_GET['tw'] ?? 0);
$pw = (int)($_GET['pw'] ?? 0);

$urls = [];

$urls = [];

// Load custom sources
$sourcesFile = __DIR__ . '/../../config/crawl_sources.json';
$customSources = file_exists($sourcesFile) ? json_decode(file_get_contents($sourcesFile), true) : [];
$customUrl = '';
foreach ($customSources as $cs) {
    if ($cs['id'] === $source) {
        $customUrl = str_replace('{page}', $page, $cs['url']);
        break;
    }
}

if ($customUrl) {
    $urls = [$customUrl];
} else if ($source === 'nguonc') {
    if ($type === 'phim-moi-cap-nhat') {
        $urls = ["https://phim.nguonc.com/api/films/phim-moi-cap-nhat?page=$page"];
    } else {
        $urls = ["https://phim.nguonc.com/api/films/danh-sach/$type?page=$page"];
    }
} else {
    // Ophim & KKPhim
    $domain = $source === 'ophim' ? 'https://ophim1.com' : 'https://phimapi.com';
    if ($type === 'phim-moi-cap-nhat') {
        $urls = ["$domain/danh-sach/phim-moi-cap-nhat?page=$page"];
    } else {
        $urls = ["$domain/v1/api/danh-sach/$type?page=$page"];
    }
}

$items = [];

// Smart Extractor Helpers
function findLargestArray($array, &$largestArray) {
    if (!is_array($array)) return;
    
    // Check if it's a sequential array of objects/arrays
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
    // 1. Direct match
    foreach ($keys as $k) {
        if (!empty($item[$k]) && !is_array($item[$k])) return $item[$k];
    }
    // 2. Case-insensitive match
    foreach ($item as $key => $val) {
        if (is_array($val)) continue;
        foreach ($keys as $k) {
            if (strtolower($key) === strtolower($k) && !empty($val)) return $val;
        }
    }
    return '';
}

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $res = curl_exec($ch);
    curl_close($ch);
    
    if ($res) {
        $data = null;
        if (strpos(trim($res), '<?xml') === 0 || strpos(trim($res), '<rss') === 0) {
            $xml = @simplexml_load_string($res, "SimpleXMLElement", LIBXML_NOCDATA);
            if ($xml) $data = json_decode(json_encode($xml), true);
        } else {
            $data = json_decode($res, true);
        }
        
        if ($data && is_array($data)) {
            $largestArray = [];
            findLargestArray($data, $largestArray);
            if (!empty($largestArray)) {
                $items = array_merge($items, $largestArray);
            }
        }
    }
}

function downloadAndProcessImage($url, $savePath, $targetWidth = 0) {
    if (empty($url)) return false;
    
    // Some urls might be missing https:
    if (strpos($url, '//') === 0) {
        $url = 'https:' . $url;
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $data = curl_exec($ch);
    curl_close($ch);
    
    if (!$data) return false;
    
    $img = @imagecreatefromstring($data);
    if (!$img) return false;
    
    $origWidth = imagesx($img);
    $origHeight = imagesy($img);
    
    if ($targetWidth > 0 && $origWidth > $targetWidth) {
        $targetHeight = floor($origHeight * ($targetWidth / $origWidth));
        $newImg = imagecreatetruecolor($targetWidth, $targetHeight);
        
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $targetWidth, $targetHeight, $transparent);
        
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);
        imagedestroy($img);
        $img = $newImg;
    }
    
    $success = imagewebp($img, $savePath, 80);
    imagedestroy($img);
    return $success;
}

$added = 0;
$updated = 0;
$repo = getMovieRepository();

if (!empty($items)) {
    $uploadDir = __DIR__ . '/../../uploads/movies/';
    if ($dl === 1 && !is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($items as $item) {
        $rawSlug = extractField($item, ['slug', 'link', 'url']);
        if (filter_var($rawSlug, FILTER_VALIDATE_URL)) {
            $parts = explode('/', rtrim($rawSlug, '/'));
            $rawSlug = end($parts); // Extract slug from end of link
        }
        $rawSlug = str_replace('.html', '', $rawSlug);
        
        $m = [
            'id' => extractField($item, ['_id', 'id', 'guid']) ?: uniqid(),
            'name' => extractField($item, ['name', 'title', 'title_vi']),
            'origin_name' => extractField($item, ['origin_name', 'original_title', 'title_en']),
            'slug' => $rawSlug,
            'thumb_url' => extractField($item, ['thumb_url', 'image', 'thumbnail', 'poster_url']),
            'poster_url' => extractField($item, ['poster_url', 'poster', 'cover', 'thumb_url', 'image']),
            'year' => (int)(extractField($item, ['year', 'release_date', 'pubDate']) ?: date('Y')),
            'type' => extractField($item, ['type', 'category']),
            'status' => extractField($item, ['status', 'episode_status']),
            'episode_current' => extractField($item, ['episode_current', 'current_episode', 'episodes']),
            'quality' => extractField($item, ['quality', 'hd']),
            'lang' => extractField($item, ['lang', 'language']),
            'chieu_rap' => !empty(extractField($item, ['chieu_rap', 'cinema'])) ? 1 : 0
        ];
        if (empty($m['slug'])) continue;
        
        if ($source === 'kkphim' || $source === 'ophim') {
            $temp = $m['thumb_url'] ?? '';
            $m['thumb_url'] = $m['poster_url'] ?? '';
            $m['poster_url'] = $temp;
        }
        
        if ($dl === 1) {
            $thumbLocal = '/uploads/movies/' . $m['slug'] . '-thumb.webp';
            $posterLocal = '/uploads/movies/' . $m['slug'] . '-poster.webp';
            
            // Note: If domain CDN missing, PhimAPI specific handling:
            if (strpos($m['thumb_url'], 'http') !== 0) $m['thumb_url'] = 'https://phimimg.com/' . $m['thumb_url'];
            if (strpos($m['poster_url'], 'http') !== 0) $m['poster_url'] = 'https://phimimg.com/' . $m['poster_url'];
            
            if (downloadAndProcessImage($m['thumb_url'], __DIR__ . '/../..' . $thumbLocal, $tw)) {
                $m['thumb_url'] = $thumbLocal;
            }
            if (downloadAndProcessImage($m['poster_url'], __DIR__ . '/../..' . $posterLocal, $pw)) {
                $m['poster_url'] = $posterLocal;
            }
        } else {
            // Fix relative paths for direct URLs just in case
            if (strpos($m['thumb_url'], 'http') !== 0 && !empty($m['thumb_url'])) $m['thumb_url'] = 'https://phimimg.com/' . $m['thumb_url'];
            if (strpos($m['poster_url'], 'http') !== 0 && !empty($m['poster_url'])) $m['poster_url'] = 'https://phimimg.com/' . $m['poster_url'];
        }
        
        // Lấy chi tiết phim để lấy episodes nếu chế độ là crawl
        // Gọi thẳng hàm fetchApiMovieDetail đã có sẵn trong db.php
        $detailRes = fetchApiMovieDetail($m['slug']);
        if ($detailRes && !empty($detailRes['episodes'])) {
            $m['episodes_json'] = json_encode($detailRes['episodes'], JSON_UNESCAPED_UNICODE);
        } else {
            $m['episodes_json'] = null;
        }
        
        // Save via repository
        $existing = clone (object) $m; // just a dummy object
        if ($repo->saveMovie($m)) {
            $added++;
        }
    }
}
echo json_encode(['status' => 'success', 'added' => $added, 'updated' => $updated]);
