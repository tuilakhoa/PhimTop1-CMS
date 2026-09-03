<?php
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_once __DIR__ . '/Crawler.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$crawler = new KKPhimCrawler();

if ($action === 'get_page_slugs') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $res = $crawler->getLatestMovies($page);
    
    if (!$res || !isset($res['data']['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối API hoặc dữ liệu trống']);
        exit;
    }
    
    $slugs = [];
    foreach ($res['data']['items'] as $item) {
        if (!empty($item['slug'])) {
            $slugs[] = $item['slug'];
        }
    }
    
    echo json_encode(['status' => 'success', 'slugs' => $slugs]);
    exit;
}

if ($action === 'crawl_single') {
    $slug = $_POST['slug'] ?? '';
    $fetchImages = isset($_POST['fetch_images']) && $_POST['fetch_images'] == '1';
    
    if (empty($slug)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing slug']);
        exit;
    }
    
    $res = $crawler->getMovieDetail($slug);
    
    if (!$res || (!isset($res['data']['item']) && !isset($res['movie']))) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy phim hoặc API lỗi']);
        exit;
    }
    
    $movie = $res['data']['item'] ?? $res['movie'];
    $episodesList = $movie['episodes'] ?? [];
    $domainPrefix = $res['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
    
    // Xử lý thông tin phim
    $thumbUrl = $movie['thumb_url'] ?? '';
    if (!preg_match('/^http/', $thumbUrl)) $thumbUrl = rtrim($domainPrefix, '/') . '/' . ltrim($thumbUrl, '/');
    $posterUrl = $movie['poster_url'] ?? '';
    if (!preg_match('/^http/', $posterUrl)) $posterUrl = rtrim($domainPrefix, '/') . '/' . ltrim($posterUrl, '/');

    // Tải ảnh cục bộ nếu yêu cầu
    if ($fetchImages) {
        $uploadDir = __DIR__ . '/../../uploads/crawled/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        if ($thumbUrl) {
            $ext = pathinfo(parse_url($thumbUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $localThumb = "{$slug}_thumb.{$ext}";
            if ($crawler->downloadImage($thumbUrl, $uploadDir . $localThumb)) {
                $thumbUrl = "/uploads/crawled/" . $localThumb;
            }
        }
        if ($posterUrl) {
            $ext = pathinfo(parse_url($posterUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $localPoster = "{$slug}_poster.{$ext}";
            if ($crawler->downloadImage($posterUrl, $uploadDir . $localPoster)) {
                $posterUrl = "/uploads/crawled/" . $localPoster;
            }
        }
    }
    
    // Đảo ngược thumb_url và poster_url theo rule của CMS
    $tempThumb = $thumbUrl;
    $thumbUrl = $posterUrl;
    $posterUrl = $tempThumb;
    
    $actor = isset($movie['actor']) && is_array($movie['actor']) ? implode(', ', $movie['actor']) : '';
    $director = isset($movie['director']) && is_array($movie['director']) ? implode(', ', $movie['director']) : '';
    
    $movieData = [
        'id' => $movie['_id'] ?? uniqid(),
        'name' => $movie['name'] ?? '',
        'origin_name' => $movie['origin_name'] ?? '',
        'slug' => $movie['slug'],
        'thumb_url' => $thumbUrl,
        'poster_url' => $posterUrl,
        'year' => $movie['year'] ?? 0,
        'type' => $movie['type'] ?? '',
        'status' => $movie['status'] ?? '',
        'episode_current' => $movie['episode_current'] ?? '',
        'quality' => $movie['quality'] ?? '',
        'lang' => $movie['lang'] ?? '',
        'chieu_rap' => !empty($movie['chieurap']) ? 1 : 0,
        'content' => $movie['content'] ?? '',
        'actor' => $actor,
        'director' => $director,
        'categories_json' => json_encode($movie['category'] ?? []),
        'countries_json' => json_encode($movie['country'] ?? []),
        'view' => $movie['view'] ?? 0,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Lưu phim
    $repo = getMovieRepository();
    $repo->saveMovie($movieData);
    
    // Lưu thể loại và quốc gia (Cập nhật bảng categories)
    $catRepo = getCategoryRepository();
    if (isset($movie['category']) && is_array($movie['category'])) {
        foreach ($movie['category'] as $c) {
            if (!empty($c['slug']) && !empty($c['name'])) {
                $catRepo->saveCategory($c['slug'], $c['name'], 'genre');
            }
        }
    }
    if (isset($movie['country']) && is_array($movie['country'])) {
        foreach ($movie['country'] as $c) {
            if (!empty($c['slug']) && !empty($c['name'])) {
                $catRepo->saveCategory($c['slug'], $c['name'], 'country');
            }
        }
    }
    
    // Lưu tập phim
    $pdo = getPDO();
    if ($pdo) {
        $stmtDel = $pdo->prepare("DELETE FROM episodes WHERE movie_slug = ?");
        $stmtDel->execute([$movie['slug']]);
        
        $sqlEp = "INSERT INTO episodes (movie_slug, server_name, name, slug, filename, embed_url, m3u8_url) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtIns = $pdo->prepare($sqlEp);
        
        foreach ($episodesList as $server) {
            $serverName = $server['server_name'] ?? 'Server 1';
            $epData = $server['server_data'] ?? [];
            foreach ($epData as $ep) {
                $stmtIns->execute([
                    $movie['slug'],
                    $serverName,
                    $ep['name'] ?? '',
                    $ep['slug'] ?? '',
                    $ep['filename'] ?? '',
                    $ep['link_embed'] ?? '',
                    $ep['link_m3u8'] ?? ''
                ]);
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'movie_name' => $movieData['name'],
        'slug' => $movieData['slug']
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
