<?php
// Cơ chế cron cho kkphimcrawler
set_time_limit(0);
if (php_sapi_name() !== "cli" && (!isset($_GET["key"]) || $_GET["key"] !== "kkphim_cron")) { die("Unauthorized"); }

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_once __DIR__ . '/Crawler.php';

// Log chức năng
function log_cron($msg) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
}

log_cron("Bắt đầu chạy cron cập nhật phim");

$ch = curl_init('https://phimapi.com/v1/api/home');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

if (!$data) {
    log_cron("Lỗi: Không thể lấy dữ liệu từ API phimapi.com/v1/api/home");
    exit(1);
}

// Tìm danh sách phim từ API
$items = [];
// Cấu trúc /v1/api/home thường có data.items hoặc danh sách các collection như phim le, phim bo
if (isset($data['data']['items'])) {
    $items = $data['data']['items'];
} elseif (isset($data['items'])) {
    $items = $data['items'];
} else {
    // Tìm các mảng phim trong data
    $searchArray = isset($data['data']) ? $data['data'] : $data;
    foreach ($searchArray as $key => $val) {
        if (is_array($val)) {
            // Kiểm tra xem có cấu trúc item phim không
            if (isset($val['items']) && is_array($val['items'])) {
                $items = array_merge($items, $val['items']);
            } elseif (isset($val[0]) && is_array($val[0]) && isset($val[0]['slug'])) {
                $items = array_merge($items, $val);
            }
        }
    }
}

if (empty($items)) {
    log_cron("Lỗi: Không tìm thấy item phim nào trong API");
    exit(1);
}

log_cron("Tìm thấy " . count($items) . " phim cần kiểm tra.");

$repo = getMovieRepository();
$crawler = new KKPhimCrawler();

$updated = 0;
$skipped = 0;

foreach ($items as $item) {
    if (empty($item['slug'])) continue;
    $slug = $item['slug'];
    
    // API có thể trả về 'episode_current', 'status', ...
    $api_episode_current = $item['episode_current'] ?? '';
    $api_status = $item['status'] ?? ''; // completed, ongoing, vv...

    $dbMovie = $repo->getMovieBySlug($slug);
    $needsUpdate = false;
    
    if (!$dbMovie) {
        // Phim mới hoàn toàn
        $needsUpdate = true;
        log_cron("Phát hiện phim mới: $slug");
    } else {
        $db_episode_current = $dbMovie['episode_current'] ?? '';
        $db_status = $dbMovie['status'] ?? '';
        
        // Kiểm tra cập nhật trạng thái (vd: từ ongoing -> completed / full)
        if ($api_status && strtolower($api_status) !== strtolower($db_status)) {
            $needsUpdate = true;
            log_cron("Cập nhật trạng thái ($db_status -> $api_status): $slug");
        } 
        // Kiểm tra cập nhật số tập (vd: Tập 1 -> Tập 2)
        elseif ($api_episode_current && $api_episode_current !== $db_episode_current) {
            $needsUpdate = true;
            log_cron("Cập nhật tập mới ($db_episode_current -> $api_episode_current): $slug");
        }
    }

    if ($needsUpdate) {
        // Tiến hành crawl chi tiết phim này
        $res = $crawler->getMovieDetail($slug);
        
        if ($res && (isset($res['data']['item']) || isset($res['movie']))) {
            $movie = $res['data']['item'] ?? $res['movie'];
            $episodesList = $movie['episodes'] ?? [];
            $domainPrefix = $res['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
            
            // Xử lý thông tin phim như ajax.php
            $thumbUrl = $movie['thumb_url'] ?? '';
            if (!preg_match('/^http/', $thumbUrl)) $thumbUrl = rtrim($domainPrefix, '/') . '/' . ltrim($thumbUrl, '/');
            $posterUrl = $movie['poster_url'] ?? '';
            if (!preg_match('/^http/', $posterUrl)) $posterUrl = rtrim($domainPrefix, '/') . '/' . ltrim($posterUrl, '/');
            
            // Đảo ngược thumb_url và poster_url theo rule của CMS
            $tempThumb = $thumbUrl;
            $thumbUrl = $posterUrl;
            $posterUrl = $tempThumb;
            
            $actor = isset($movie['actor']) && is_array($movie['actor']) ? implode(', ', $movie['actor']) : '';
            $director = isset($movie['director']) && is_array($movie['director']) ? implode(', ', $movie['director']) : '';
            
            $movieId = $dbMovie ? $dbMovie['id'] : ($movie['_id'] ?? uniqid());

            $peoplesRes = $crawler->getMoviePeoples($movie['slug']);
            $peoplesData = ($peoplesRes && !empty($peoplesRes['data']['peoples'])) ? $peoplesRes['data']['peoples'] : [];

            $movieData = [
                'id' => $movieId,
                'name' => $movie['name'] ?? '',
                'origin_name' => $movie['origin_name'] ?? '',
                'slug' => $movie['slug'],
                'thumb_url' => $thumbUrl,
                'poster_url' => $posterUrl,
                'trailer_url' => $movie['trailer_url'] ?? '',
                'tmdb_vote' => $movie['tmdb']['vote_average'] ?? 0,
                'imdb_vote' => $movie['imdb']['vote_average'] ?? 0,
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
                'images_json' => json_encode([]),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Lấy danh sách hình ảnh (gallery/backdrops)
            $imagesRes = $crawler->getMovieImages($slug);
            if ($imagesRes && isset($imagesRes['data'])) {
                $movieData['images_json'] = json_encode($imagesRes['data']);
            }
            
            // Lưu phim
            $repo->saveMovie($movieData);
            
            // Lưu thể loại và quốc gia
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
            log_cron("-> Đã lưu phim thành công: $slug");
            $updated++;
        } else {
            log_cron("-> Lỗi không lấy được chi tiết phim: $slug");
        }
    } else {
        $skipped++;
    }
}

log_cron("Cron hoàn thành. Cập nhật: $updated phim, Bỏ qua: $skipped phim.");
?>
