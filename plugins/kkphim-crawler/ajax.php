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

if ($action === 'crawl_keyword') {
    $keyword = $_POST['keyword'] ?? '';
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    
    if (empty($keyword)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập từ khóa']);
        exit;
    }
    
    $res = $crawler->searchMovies($keyword, $limit);
    
    if (!$res || !isset($res['data']['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối API hoặc không tìm thấy phim nào']);
        exit;
    }
    
    $slugs = [];
    foreach ($res['data']['items'] as $item) {
        if (!empty($item['slug'])) {
            $slugs[] = $item['slug'];
        }
    }
    
    echo json_encode(['status' => 'success', 'slugs' => $slugs, 'total' => count($slugs)]);
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
    $episodesList = $res['episodes'] ?? ($movie['episodes'] ?? []);
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
    
    $actor = isset($movie['actor']) ? (is_array($movie['actor']) ? implode(', ', $movie['actor']) : $movie['actor']) : '';
    $director = isset($movie['director']) ? (is_array($movie['director']) ? implode(', ', $movie['director']) : $movie['director']) : '';
    
    $peoplesRes = $crawler->getMoviePeoples($movie['slug']);
    $peoplesData = ($peoplesRes && !empty($peoplesRes['data']['peoples'])) ? $peoplesRes['data']['peoples'] : [];
    
    $imagesRes = $crawler->getMovieImages($movie['slug']);
    $imagesData = ($imagesRes && !empty($imagesRes['data'])) ? $imagesRes['data'] : [];
    
    $movieData = [
        'id' => $movie['_id'] ?? uniqid(),
        'name' => $movie['name'] ?? '',
        'origin_name' => $movie['origin_name'] ?? '',
        'slug' => $movie['slug'],
        'thumb_url' => $thumbUrl,
        'poster_url' => $posterUrl,
        'trailer_url' => $movie['trailer_url'] ?? '',
        'tmdb_vote' => (isset($movie['tmdb']) && is_array($movie['tmdb'])) ? ($movie['tmdb']['vote_average'] ?? 0) : 0,
        'imdb_vote' => (isset($movie['imdb']) && is_array($movie['imdb'])) ? ($movie['imdb']['vote_average'] ?? 0) : 0,
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
        'time' => $movie['time'] ?? '',
        'peoples_json' => json_encode($peoplesData ?: []),
        'images_json' => json_encode($imagesData ?: []),
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
    
    // Lưu keywords
    $kwRes = $crawler->getMovieKeywords($movie['slug']);
    if ($kwRes && isset($kwRes['data']['keywords']) && is_array($kwRes['data']['keywords'])) {
        $keywords = [];
        foreach ($kwRes['data']['keywords'] as $kw) {
            if (!empty($kw['name'])) $keywords[] = trim($kw['name']);
        }
        if (!empty($keywords)) {
            $keywordString = implode(', ', $keywords);
            $seoRepo = getSeoRepository();
            $seoData = $seoRepo->getSeoMetadata('movie', $movie['slug']);
            if (!$seoData) {
                $seoData = [
                    'type' => 'movie',
                    'item_id' => $movie['slug'],
                    'seo_title' => $movieData['name'],
                    'seo_desc' => mb_substr(strip_tags($movieData['content']), 0, 160),
                    'seo_keywords' => $keywordString
                ];
            } else {
                $seoData['seo_keywords'] = $keywordString;
            }
            $seoRepo->saveSeoMetadata($seoData);
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

if ($action === 'get_failed_slugs') {
    $failedFile = __DIR__ . '/failed_slugs.log';
    $slugs = [];
    if (file_exists($failedFile)) {
        $lines = file($failedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        // Lọc các slug hợp lệ, loại bỏ trùng lặp
        $slugs = array_values(array_unique(array_filter(array_map('trim', $lines))));
    }
    echo json_encode(['status' => 'success', 'slugs' => $slugs]);
    exit;
}

if ($action === 'remove_failed_slug') {
    $slugToRemove = $_POST['slug'] ?? '';
    $failedFile = __DIR__ . '/failed_slugs.log';
    if (!empty($slugToRemove) && file_exists($failedFile)) {
        $lines = file($failedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newLines = [];
        $removed = false;
        foreach ($lines as $line) {
            if (trim($line) !== $slugToRemove) {
                $newLines[] = trim($line);
            } else {
                $removed = true;
            }
        }
        if ($removed) {
            if (empty($newLines)) {
                unlink($failedFile);
            } else {
                file_put_contents($failedFile, implode("\n", $newLines) . "\n");
            }
        }
    }
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'reset_cron_batch') {
    $progressFile = __DIR__ . '/cron_batch_progress.txt';
    if (file_exists($progressFile)) {
        unlink($progressFile);
    }
    echo json_encode(['status' => 'success', 'message' => 'Reset to page 1']);
    exit;
}

if ($action === 'set_cron_batch_progress') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    if ($page < 1) $page = 1;
    
    $progressFile = __DIR__ . '/cron_batch_progress.txt';
    file_put_contents($progressFile, $page);
    
    echo json_encode(['status' => 'success', 'message' => "Đã cập nhật mốc tiến độ thành trang $page", 'page' => $page]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
