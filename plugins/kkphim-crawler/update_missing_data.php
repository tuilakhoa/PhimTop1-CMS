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

$options = getopt('', ['limit:', 'force']);
$limit = isset($options['limit']) ? (int)$options['limit'] : 1000;
$force = isset($options['force']);

// Mốc thời gian Crawler được nâng cấp để lấy full dữ liệu (bạn có thể đổi nếu cần)
$upgradeTime = '2026-09-07 12:00:00';

// Quét toàn bộ các phim chưa được update sau thời điểm nâng cấp crawler
// Hoặc nếu dùng tham số --force thì quét tất cả
$sql = "SELECT slug, name, content FROM movies";
if (!$force) {
    $sql .= " WHERE updated_at < '$upgradeTime' OR updated_at IS NULL";
}
$sql .= " ORDER BY id DESC";

if ($limit > 0) {
    $sql .= " LIMIT $limit";
}

$stmt = $pdo->query($sql);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Tìm thấy " . count($movies) . " phim cần quét lại dữ liệu.\n";

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
            } elseif ($httpCode == 404 || $httpCode == 500 || strpos($failed_urls[$key], '/peoples') !== false || strpos($failed_urls[$key], '/images') !== false || strpos($failed_urls[$key], '/keywords') !== false) {
                // Ignore optional endpoint errors
                $results[$key] = [];
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
        
        $fullData = KKPhimCrawler::fetchMovieFromAllSources($slug);
        
        if ($fullData['movie']) {
            $movie = $fullData['movie'];
            $episodesList = $fullData['episodes'];
            $peoplesData = $fullData['peoples'];
            $imagesData = $fullData['images'];
            $keywordsData = $fullData['keywords'];
            
            $thumbUrl = $movie['thumb_url'] ?? '';
            $posterUrl = $movie['poster_url'] ?? '';
            if (empty($thumbUrl)) {
                $thumbUrl = $posterUrl;
            } elseif (empty($posterUrl)) {
                $posterUrl = $thumbUrl;
            }
            if (strpos($thumbUrl, 'http') !== 0 && !empty($movie['APP_DOMAIN_CDN_IMAGE'])) {
                $thumbUrl = rtrim($movie['APP_DOMAIN_CDN_IMAGE'], '/') . '/' . ltrim($thumbUrl, '/');
            }
            if (strpos($posterUrl, 'http') !== 0 && !empty($movie['APP_DOMAIN_CDN_IMAGE'])) {
                $posterUrl = rtrim($movie['APP_DOMAIN_CDN_IMAGE'], '/') . '/' . ltrim($posterUrl, '/');
            }
            
            $actor = isset($movie['actor']) ? (is_array($movie['actor']) ? implode(', ', $movie['actor']) : $movie['actor']) : '';
            $director = isset($movie['director']) ? (is_array($movie['director']) ? implode(', ', $movie['director']) : $movie['director']) : '';
            
            // Đảo ngược thumb_url và poster_url theo rule của CMS
            $tempThumb = $thumbUrl;
            $thumbUrl = $posterUrl;
            $posterUrl = $tempThumb;
            
            $repo = getMovieRepository();
            $catRepo = getCategoryRepository();
            $dbMovie = $repo->getMovieBySlug($slug);
            $movieId = $dbMovie ? $dbMovie['id'] : ($movie['_id'] ?? uniqid());

            $movieData = [
                'id' => $movieId,
                'name' => $movie['name'] ?? '',
                'origin_name' => $movie['origin_name'] ?? '',
                'slug' => $movie['slug'] ?? $slug,
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
                'chieu_rap' => (isset($movie['chieurap']) && $movie['chieurap']) ? 1 : 0,
                'content' => $movie['content'] ?? '',
                'actor' => $actor,
                'director' => $director,
                'categories_json' => json_encode($movie['category'] ?? []),
                'countries_json' => json_encode($movie['country'] ?? []),
                'view' => $dbMovie ? ($dbMovie['view'] ?? 0) : ($movie['view'] ?? 0),
                'time' => $movie['time'] ?? '',
                'peoples_json' => json_encode($peoplesData ?: []),
                'images_json' => json_encode($imagesData ?: []),
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
            
            // Cập nhật keywords
            if (!empty($keywordsData)) {
                $keywords = [];
                foreach ($keywordsData as $kw) {
                    if (!empty($kw['name'])) $keywords[] = trim($kw['name']);
                }
                if (!empty($keywords)) {
                    $keywordString = implode(', ', $keywords);
                    $seoData = $seoRepo->getSeoMetadata('movie', $slug);
                    if (!$seoData) {
                        $seoData = [
                            'type' => 'movie',
                            'item_id' => $slug,
                            'seo_title' => $m['name'],
                            'seo_desc' => mb_substr(strip_tags($m['content']), 0, 160),
                            'seo_keywords' => $keywordString
                        ];
                    } else {
                        $seoData['seo_keywords'] = $keywordString;
                    }
                    $seoRepo->saveSeoMetadata($seoData);
                }
            }
            
            // Cập nhật Episodes (Làm mới toàn bộ nguồn cho phim này)
            if (!empty($episodesList)) {
                $stmtDel = $pdo->prepare("DELETE FROM episodes WHERE movie_slug = ?");
                $stmtDel->execute([$slug]);
                
                $sqlEp = "INSERT INTO episodes (movie_slug, server_name, name, slug, filename, embed_url, m3u8_url) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtIns = $pdo->prepare($sqlEp);
                
                foreach ($episodesList as $server) {
                    $serverName = $server['server_name'] ?? 'Server 1';
                    $epData = $server['server_data'] ?? [];
                    foreach ($epData as $ep) {
                        $stmtIns->execute([
                            $slug,
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
        }
    }
    
    $processed += count($batch);
    echo "Đã xử lý: $processed / $total phim...\n";
    
    sleep(1); // Tránh bị block
}

echo "\nHOÀN TẤT CẬP NHẬT!\n";
