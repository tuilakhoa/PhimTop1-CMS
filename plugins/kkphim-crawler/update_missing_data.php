<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');

if (php_sapi_name() !== 'cli') {
    die("Script này chỉ được phép chạy qua CLI để tránh timeout. Lệnh: php update_missing_data.php\n");
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_once __DIR__ . '/Crawler.php';

$pdo = getPDO();
if (!$pdo) {
    die("Không thể kết nối Database.\n");
}

echo "=================================================\n";
echo "BẮT ĐẦU CẬP NHẬT THỜI LƯỢNG VÀ KEYWORDS CHO CÁC PHIM CŨ\n";
echo "=================================================\n\n";

$crawler = new KKPhimCrawler();
$seoRepo = getSeoRepository();

// Lấy toàn bộ slug trong DB
$stmt = $pdo->query("SELECT slug, name, content FROM movies");
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Tìm thấy " . count($movies) . " phim trong database.\n";

function multiRequestWithRetry($urls, $max_retries = 3) {
    $results = [];
    $failed_urls = $urls;
    $attempt = 1;

    while (!empty($failed_urls) && $attempt <= $max_retries) {
        $multi = curl_multi_init();
        $channels = [];
        
        foreach ($failed_urls as $key => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
            curl_multi_add_handle($multi, $ch);
            $channels[$key] = $ch;
        }
        
        $active = null;
        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) {
                curl_multi_select($multi, 0.5);
            }
        } while ($active && $status == CURLM_OK);
        
        $new_failed = [];
        
        foreach ($channels as $key => $ch) {
            $res = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $parsed = json_decode($res, true);
            
            if ($httpCode >= 200 && $httpCode < 300 && $parsed) {
                $results[$key] = $parsed;
            } else {
                $new_failed[$key] = $failed_urls[$key];
            }
            
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }
        curl_multi_close($multi);
        
        $failed_urls = $new_failed;
        if (!empty($failed_urls)) sleep(2);
        $attempt++;
    }
    return $results;
}

// Xử lý theo batch
$batchSize = 10;
$total = count($movies);
$processed = 0;

for ($i = 0; $i < $total; $i += $batchSize) {
    $batch = array_slice($movies, $i, $batchSize);
    
    $detailUrls = [];
    $keywordUrls = [];
    $peoplesUrls = [];
    $imagesUrls = [];
    
    foreach ($batch as $m) {
        $slug = $m['slug'];
        $detailUrls["detail_$slug"] = "https://phimapi.com/v1/api/phim/" . urlencode($slug);
        $keywordUrls["kw_$slug"] = "https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/keywords";
        $peoplesUrls["peop_$slug"] = "https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/peoples";
        $imagesUrls["img_$slug"] = "https://phimapi.com/v1/api/phim/" . urlencode($slug) . "/images";
    }
    
    // Chạy song song 4 mảng API (40 request cùng lúc)
    $allUrls = array_merge($detailUrls, $keywordUrls, $peoplesUrls, $imagesUrls);
    $multiResults = multiRequestWithRetry($allUrls, 3);
    
    foreach ($batch as $m) {
        $slug = $m['slug'];
        $name = $m['name'];
        $content = $m['content'];
        
        // Cập nhật time, trailer_url, tmdb_vote, imdb_vote
        $detailRes = $multiResults["detail_$slug"] ?? null;
        if ($detailRes && (isset($detailRes['data']['item']) || isset($detailRes['movie']))) {
            $movieApi = $detailRes['data']['item'] ?? $detailRes['movie'];
            $time = $movieApi['time'] ?? '';
            $trailerUrl = $movieApi['trailer_url'] ?? '';
            $tmdbVote = $movieApi['tmdb']['vote_average'] ?? 0;
            $imdbVote = $movieApi['imdb']['vote_average'] ?? 0;
            
            if (!empty($time) || !empty($trailerUrl) || $tmdbVote > 0 || $imdbVote > 0) {
                $updateStmt = $pdo->prepare("UPDATE movies SET time = COALESCE(NULLIF(?, ''), time), trailer_url = COALESCE(NULLIF(?, ''), trailer_url), tmdb_vote = IF(? > 0, ?, tmdb_vote), imdb_vote = IF(? > 0, ?, imdb_vote) WHERE slug = ?");
                $updateStmt->execute([$time, $trailerUrl, $tmdbVote, $tmdbVote, $imdbVote, $imdbVote, $slug]);
            }
        }
        
        // Cập nhật keywords
        $kwRes = $multiResults["kw_$slug"] ?? null;
        if ($kwRes && isset($kwRes['data']['keywords']) && is_array($kwRes['data']['keywords'])) {
            $keywords = [];
            foreach ($kwRes['data']['keywords'] as $kw) {
                if (!empty($kw['name'])) $keywords[] = trim($kw['name']);
            }
            if (!empty($keywords)) {
                $keywordString = implode(', ', $keywords);
                $seoData = $seoRepo->getSeoMetadata('movie', $slug);
                if (!$seoData) {
                    $seoData = [
                        'type' => 'movie',
                        'item_id' => $slug,
                        'seo_title' => $name,
                        'seo_desc' => mb_substr(strip_tags($content), 0, 160),
                        'seo_keywords' => $keywordString
                    ];
                } else {
                    $seoData['seo_keywords'] = $keywordString;
                }
                $seoRepo->saveSeoMetadata($seoData);
            }
        }
        
        // Cập nhật peoples_json
        $peoplesRes = $multiResults["peop_$slug"] ?? null;
        if ($peoplesRes && !empty($peoplesRes['data']['peoples'])) {
            $peoplesData = $peoplesRes['data']['peoples'];
            $peoplesJson = json_encode($peoplesData);
            $updateStmt = $pdo->prepare("UPDATE movies SET peoples_json = ? WHERE slug = ?");
            $updateStmt->execute([$peoplesJson, $slug]);
        }
        
        // Cập nhật images_json
        $imagesRes = $multiResults["img_$slug"] ?? null;
        if ($imagesRes && isset($imagesRes['data'])) {
            $imagesData = $imagesRes['data'];
            $imagesJson = json_encode($imagesData);
            $updateStmt = $pdo->prepare("UPDATE movies SET images_json = ? WHERE slug = ?");
            $updateStmt->execute([$imagesJson, $slug]);
        }
    }
    
    $processed += count($batch);
    echo "Đã xử lý: $processed / $total phim...\n";
    
    sleep(3); // Tránh bị block bởi Cloudflare (Rate limit)
}

echo "\nHOÀN TẤT CẬP NHẬT!\n";
