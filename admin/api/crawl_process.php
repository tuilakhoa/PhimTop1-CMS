<?php
require_once __DIR__ . '/../../includes/db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

set_time_limit(0);
header('Content-Type: application/json');

$source = $_GET['source'] ?? 'phimapi';
$page = (int)($_GET['page'] ?? 1);
$dl = (int)($_GET['dl'] ?? 0);
$tw = (int)($_GET['tw'] ?? 0);
$pw = (int)($_GET['pw'] ?? 0);

$urls = [];

if ($source === 'kkphim') $urls = ["https://phimapi.com/danh-sach/phim-moi-cap-nhat?page=$page"];
else if ($source === 'ophim') $urls = ["https://ophim1.com/danh-sach/phim-moi-cap-nhat?page=$page"];
else if ($source === 'nguonc') $urls = ["https://phim.nguonc.com/api/films/phim-moi-cap-nhat?page=$page"];
else $urls = ["https://phimapi.com/danh-sach/phim-moi-cap-nhat?page=$page"];

$items = [];
foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    curl_close($ch);
    if ($res) {
        $data = json_decode($res, true);
        if (isset($data['items'])) $items = array_merge($items, $data['items']);
        else if (isset($data['data']['items'])) $items = array_merge($items, $data['data']['items']);
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
$pdo = getPDO();
if ($pdo && !empty($items)) {
    $stmt = $pdo->prepare("INSERT INTO movies (id, name, origin_name, slug, thumb_url, poster_url, year, type, status, episode_current, quality, lang, chieu_rap)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name=VALUES(name), origin_name=VALUES(origin_name), thumb_url=VALUES(thumb_url), poster_url=VALUES(poster_url), year=VALUES(year), type=VALUES(type), status=VALUES(status), episode_current=VALUES(episode_current), quality=VALUES(quality), lang=VALUES(lang), chieu_rap=VALUES(chieu_rap)");

    $uploadDir = __DIR__ . '/../../uploads/movies/';
    if ($dl === 1 && !is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($items as $item) {
        $m = [
            'id' => $item['_id'] ?? $item['id'] ?? uniqid(),
            'name' => $item['name'] ?? '',
            'origin_name' => $item['origin_name'] ?? '',
            'slug' => $item['slug'] ?? '',
            'thumb_url' => $item['thumb_url'] ?? $item['poster_url'] ?? '',
            'poster_url' => $item['poster_url'] ?? $item['thumb_url'] ?? '',
            'year' => (int)($item['year'] ?? date('Y')),
            'type' => $item['type'] ?? '',
            'status' => $item['status'] ?? '',
            'episode_current' => $item['episode_current'] ?? '',
            'quality' => $item['quality'] ?? '',
            'lang' => $item['lang'] ?? '',
            'chieu_rap' => !empty($item['chieu_rap']) ? 1 : 0
        ];
        if (!$m['slug']) continue;
        
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
        
        $stmt->execute([$m['id'], $m['name'], $m['origin_name'], $m['slug'], $m['thumb_url'], $m['poster_url'], $m['year'], $m['type'], $m['status'], $m['episode_current'], $m['quality'], $m['lang'], $m['chieu_rap']]);
        if ($stmt->rowCount() == 1) $added++;
        else $updated++;
    }
}
echo json_encode(['status' => 'success', 'added' => $added, 'updated' => $updated]);
