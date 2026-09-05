<?php
// Script này chạy qua Cron (Web hoặc CLI), crawl 20 trang mỗi lần
set_time_limit(0);
ini_set('memory_limit', '1024M');

if (php_sapi_name() !== "cli" && (!isset($_GET["key"]) || $_GET["key"] !== "kkphim_cron")) { 
    die("Unauthorized"); 
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_once __DIR__ . '/Crawler.php';

$progress_file = __DIR__ . '/cron_batch_progress.txt';

if (file_exists($progress_file)) {
    $from_page = (int)trim(file_get_contents($progress_file));
    if ($from_page <= 0) $from_page = 1;
    echo "=> Tìm thấy file lưu tiến độ. Tự động tiếp tục từ trang: $from_page\n";
} else {
    $from_page = 1;
}

$to_page = $from_page + 19; // Crawl đúng 20 trang mỗi lần

echo "=================================================\n";
echo "BẮT ĐẦU CRAWL BATCH TỪ TRANG $from_page ĐẾN $to_page\n";
echo "=================================================\n\n";

$repo = getMovieRepository();
$catRepo = getCategoryRepository();
$pdo = getPDO();
$crawler = new KKPhimCrawler();

// Hàm tải nhiều URL có cơ chế RETRY
function multiRequestWithRetry($urls, $max_retries = 5) {
    $results = [];
    $failed_urls = $urls;
    $attempt = 1;

    while (!empty($failed_urls) && $attempt <= $max_retries) {
        if ($attempt > 1) {
            echo "     -> [Retry Lần $attempt] Đang thử lại " . count($failed_urls) . " request thất bại...\n";
            sleep(2); // Nghỉ một chút trước khi thử lại
        }

        $multi = curl_multi_init();
        $channels = [];
        
        foreach ($failed_urls as $key => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
            curl_multi_add_handle($multi, $ch);
            $channels[$key] = $ch;
        }
        
        $active = null;
        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) {
                curl_multi_select($multi);
            }
        } while ($active && $status == CURLM_OK);
        
        $new_failed = [];
        
        foreach ($channels as $key => $ch) {
            $res = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $parsed = json_decode($res, true);
            
            // Thành công nếu HTTP 200 và parse JSON có data
            if ($httpCode >= 200 && $httpCode < 300 && $parsed) {
                $results[$key] = $parsed;
            } else {
                // Thất bại, đưa vào mảng để thử lại
                $new_failed[$key] = $failed_urls[$key];
            }
            
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }
        curl_multi_close($multi);
        
        $failed_urls = $new_failed;
        $attempt++;
    }
    
    // Nếu vẫn còn failed sau max retries
    if (!empty($failed_urls)) {
        foreach ($failed_urls as $key => $url) {
            file_put_contents(__DIR__ . '/failed_slugs.log', $key . "\n", FILE_APPEND);
            echo "     [Lỗi Cứng] Đã thử $max_retries lần vẫn lỗi URL: $url\n";
        }
    }
    
    return $results;
}

// Hàm lưu dữ liệu
function saveMovieData($res, $slug, $repo, $catRepo, $pdo, $peoplesData, $imagesData, $keywordsData) {
    if (!$res || (!isset($res['data']['item']) && !isset($res['movie']))) {
        return false;
    }
    
    $movie = $res['data']['item'] ?? $res['movie'];
    $episodesList = $movie['episodes'] ?? [];
    $domainPrefix = $res['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
    
    $thumbUrl = $movie['thumb_url'] ?? '';
    if (!preg_match('/^http/', $thumbUrl)) $thumbUrl = rtrim($domainPrefix, '/') . '/' . ltrim($thumbUrl, '/');
    $posterUrl = $movie['poster_url'] ?? '';
    if (!preg_match('/^http/', $posterUrl)) $posterUrl = rtrim($domainPrefix, '/') . '/' . ltrim($posterUrl, '/');
    
    $tempThumb = $thumbUrl;
    $thumbUrl = $posterUrl;
    $posterUrl = $tempThumb;
    
    $actor = isset($movie['actor']) && is_array($movie['actor']) ? implode(', ', $movie['actor']) : '';
    $director = isset($movie['director']) && is_array($movie['director']) ? implode(', ', $movie['director']) : '';
    
    $dbMovie = $repo->getMovieBySlug($slug);
    $movieId = $dbMovie ? $dbMovie['id'] : ($movie['_id'] ?? uniqid());

    $movieData = [
        'id' => $movieId,
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
        'view' => $dbMovie ? ($dbMovie['view'] ?? 0) : ($movie['view'] ?? 0),
        'time' => $movie['time'] ?? '',
        'peoples_json' => json_encode($peoplesData),
        'images_json' => json_encode($imagesData),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $repo->saveMovie($movieData);
    
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
    if (!empty($keywordsData)) {
        $keywords = [];
        foreach ($keywordsData as $kw) {
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
    return true;
}

// Bắt đầu loop qua các trang
for ($page = $from_page; $page <= $to_page; $page++) {
    echo "\n=> Đang lấy danh sách trang $page...\n";
    $listUrl = "https://phimapi.com/v1/api/danh-sach?page={$page}";
    
    $page_attempt = 1;
    $data = null;
    
    // Retry cho trang danh sách (Tối đa 5 lần)
    while ($page_attempt <= 5) {
        $ch = curl_init($listUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200 && $res) {
            $data = json_decode($res, true);
            if (isset($data['data']['items'])) {
                break; // Thành công
            }
        }
        
        echo "   [Lỗi Trang $page] Thử lại lần $page_attempt...\n";
        $page_attempt++;
        sleep(2);
    }
    
    if (!$data || !isset($data['data']['items'])) {
        echo "[BỎ QUA] Không thể tải trang $page sau 5 lần thử.\n";
        continue;
    }
    
    if (empty($data['data']['items'])) {
        echo "=> Trang $page không có dữ liệu hoặc đã đến trang cuối.\n";
        break; // Thoát nếu trang rỗng
    }
    
    $slugs = [];
    foreach ($data['data']['items'] as $item) {
        if (!empty($item['slug'])) {
            $slugs[] = $item['slug'];
        }
    }
    
    echo "   - Tìm thấy " . count($slugs) . " phim. Đang tải chi tiết ĐỒNG THỜI...\n";
    
    $detailUrls = [];
    $peoplesUrls = [];
    $imagesUrls = [];
    $keywordsUrls = [];
    
    foreach ($slugs as $slug) {
        $detailUrls[$slug] = "https://phimapi.com/v1/api/phim/" . urlencode($slug);
        $peoplesUrls[$slug] = "https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/peoples";
        $imagesUrls[$slug] = "https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/images";
        $keywordsUrls[$slug] = "https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/keywords";
    }
    
    // Bắn multi curl với cơ chế RETRY (tối đa 3 lần cho mỗi URL lỗi)
    $multiResults = multiRequestWithRetry($detailUrls, 3);
    
    // Nếu có quá nhiều lỗi, có thể do rate limit, sleep 1 chút
    usleep(500000); 
    
    // Fetch peoples, images, keywords concurrently in batches to avoid overwhelming the API
    $peoplesResults = multiRequestWithRetry($peoplesUrls, 3);
    usleep(500000);
    
    $imagesResults = multiRequestWithRetry($imagesUrls, 3);
    usleep(500000);
    
    $keywordsResults = multiRequestWithRetry($keywordsUrls, 3);
    
    $successCount = 0;
    $savedMovies = [];
    foreach ($multiResults as $slug => $detailRes) {
        $peoplesData = isset($peoplesResults[$slug]['data']['peoples']) ? $peoplesResults[$slug]['data']['peoples'] : [];
        $imagesData = isset($imagesResults[$slug]['data']) ? $imagesResults[$slug]['data'] : [];
        $keywordsData = isset($keywordsResults[$slug]['data']['keywords']) ? $keywordsResults[$slug]['data']['keywords'] : [];
        
        if (saveMovieData($detailRes, $slug, $repo, $catRepo, $pdo, $peoplesData, $imagesData, $keywordsData)) {
            $successCount++;
            $movieName = isset($detailRes['data']['item']['name']) ? $detailRes['data']['item']['name'] : (isset($detailRes['movie']['name']) ? $detailRes['movie']['name'] : $slug);
            $savedMovies[] = $movieName;
        }
    }
    
    echo "   -> Đã lưu thành công $successCount/" . count($slugs) . " phim ở trang $page.\n";
    if ($successCount > 0) {
        $logLine = "[" . date('Y-m-d H:i:s') . "] Trang $page: " . implode(", ", $savedMovies) . "\n";
        file_put_contents(__DIR__ . '/cron_batch_history.log', $logLine, FILE_APPEND);
    }
    
    // Lưu lại tiến trình (đánh dấu trang tiếp theo sẽ chạy)
    file_put_contents($progress_file, $page + 1);

    echo "   [Nghỉ 30 giây để tránh Rate Limit API...]\n";
    sleep(30);
}

echo "\n=================================================\n";
echo "HOÀN TẤT QUÁ TRÌNH CRAWL.\n";
if (file_exists(__DIR__ . '/failed_slugs.log')) {
    echo "Lưu ý: Có một số phim bị lỗi mạng không thể lấy được sau nhiều lần thử lại.\n";
    echo "Xem danh sách tại: failed_slugs.log\n";
}
