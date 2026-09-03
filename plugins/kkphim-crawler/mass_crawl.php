<?php
// Script này để chạy qua CLI, giúp crawl hàng loạt 30.000 phim cực nhanh và đảm bảo full
set_time_limit(0);
ini_set('memory_limit', '1024M');

if (php_sapi_name() !== 'cli') {
    die("Script này chỉ được phép chạy qua CLI để tránh timeout. Lệnh: php mass_crawl.php [từ_trang] [đến_trang]\n");
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';

$from_page = isset($argv[1]) ? (int)$argv[1] : 1;
$to_page = isset($argv[2]) ? (int)$argv[2] : 1000;

echo "=================================================\n";
echo "BẮT ĐẦU CRAWL HÀNG LOẠT (CHẾ ĐỘ ĐẢM BẢO) TỪ TRANG $from_page ĐẾN $to_page\n";
echo "=================================================\n\n";

$repo = getMovieRepository();
$catRepo = getCategoryRepository();
$pdo = getPDO();

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
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
            if ($httpCode >= 200 && $httpCode < 300 && $parsed && (isset($parsed['data']['item']) || isset($parsed['movie']))) {
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
function saveMovieData($res, $slug, $repo, $catRepo, $pdo) {
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
    foreach ($slugs as $slug) {
        $detailUrls[$slug] = "https://phimapi.com/v1/api/phim/" . urlencode($slug);
    }
    
    // Bắn multi curl với cơ chế RETRY (tối đa 5 lần cho mỗi URL lỗi)
    $multiResults = multiRequestWithRetry($detailUrls, 5);
    
    $successCount = 0;
    foreach ($multiResults as $slug => $detailRes) {
        if (saveMovieData($detailRes, $slug, $repo, $catRepo, $pdo)) {
            $successCount++;
        }
    }
    
    echo "   -> Đã lưu thành công $successCount/" . count($slugs) . " phim ở trang $page.\n";
}

echo "\n=================================================\n";
echo "HOÀN TẤT QUÁ TRÌNH CRAWL.\n";
if (file_exists(__DIR__ . '/failed_slugs.log')) {
    echo "Lưu ý: Có một số phim bị lỗi mạng không thể lấy được sau nhiều lần thử lại.\n";
    echo "Xem danh sách tại: failed_slugs.log\n";
}
