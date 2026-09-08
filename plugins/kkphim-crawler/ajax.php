<?php
set_time_limit(0);
ini_set('max_execution_time', 0);
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/repositories.php';
require_once __DIR__ . '/Crawler.php';

function getCrawlerSyncDB() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . __DIR__ . '/sync.sqlite');
        $db->exec("CREATE TABLE IF NOT EXISTS sync_meta (slug TEXT, source TEXT, modified TEXT, PRIMARY KEY(slug, source))");
    }
    return $db;
}

header('Content-Type: application/json');

$configFile = __DIR__ . '/config.json';
$source = 'kkphim';
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    if (isset($config['source'])) {
        $source = $config['source'];
    }
}
$crawler = new KKPhimCrawler($source);

$action = $_POST['action'] ?? '';

if ($action === 'save_source') {
    $newSource = $_POST['source'] ?? 'kkphim';
    file_put_contents($configFile, json_encode(['source' => $newSource]));
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'get_page_slugs') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    
    $crawlers = [
        new KKPhimCrawler('kkphim'),
        new KKPhimCrawler('nguonc'),
        new KKPhimCrawler('vsmov')
    ];
    
    $slugs = [];
    $seenSlugs = [];
    
    foreach ($crawlers as $c) {
        $res = $c->getLatestMovies($page);
        if ($res) {
            $items = $res['data']['items'] ?? $res['items'] ?? [];
            foreach ($items as $item) {
                if (!empty($item['slug']) && !isset($seenSlugs[$item['slug']])) {
                    $slugs[] = $item['slug'];
                    $seenSlugs[$item['slug']] = true;
                }
            }
        }
    }
    
    if (empty($slugs)) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối API hoặc dữ liệu trống trên cả 3 nguồn']);
        exit;
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
    
    $crawlers = [
        new KKPhimCrawler('kkphim'),
        new KKPhimCrawler('nguonc'),
        new KKPhimCrawler('vsmov')
    ];
    
    $slugs = [];
    $seenSlugs = [];
    
    foreach ($crawlers as $c) {
        $res = $c->searchMovies($keyword, $limit);
        if ($res) {
            $items = $res['data']['items'] ?? $res['items'] ?? [];
            foreach ($items as $item) {
                if (!empty($item['slug']) && !isset($seenSlugs[$item['slug']])) {
                    $slugs[] = $item['slug'];
                    $seenSlugs[$item['slug']] = true;
                    if (count($slugs) >= $limit) break 2; // Dừng nếu đã đủ limit
                }
            }
        }
    }
    
    if (empty($slugs)) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối API hoặc không tìm thấy phim nào']);
        exit;
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
    
    $fullData = KKPhimCrawler::fetchMovieFromAllSources($slug);
    $pdo = getPDO();
    if ($fullData && !empty($fullData['movie'])) {
        if (KKPhimCrawler::isMovieBlockedByCategoriesOrCountries($fullData['movie'], $pdo)) {
            echo json_encode(['status' => 'error', 'message' => 'Phim bị chặn theo thể loại/quốc gia']);
            exit;
        }
    }
    
    if (!$fullData['movie']) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy phim hoặc API lỗi']);
        exit;
    }
    
    $movie = $fullData['movie'];
    $episodesList = $fullData['episodes'];
    $peoplesData = $fullData['peoples'];
    $imagesData = $fullData['images'];
    $keywordsData = $fullData['keywords'];
    
    $domainPrefix = $movie['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
    
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

if ($action === 'check_new_movies') {
    $crawlers = [
        new KKPhimCrawler('kkphim'),
        new KKPhimCrawler('nguonc'),
        new KKPhimCrawler('vsmov')
    ];
    $repo = getMovieRepository();
    $stats = [];
    $total_new = 0;
    
    foreach ($crawlers as $index => $crawler) {
        $sourceNames = ['KKPhim', 'Nguồn C', 'VsMov'];
        $sourceName = $sourceNames[$index];
        $data = $crawler->getLatestMovies(1);
        $new_count = 0;
        
        if ($data) {
            $sourceItems = $data['data']['items'] ?? $data['items'] ?? [];
            foreach ($sourceItems as $item) {
                if (empty($item['slug'])) continue;
                $slug = $item['slug'];
                $api_episode_current = $item['episode_current'] ?? '';
                $api_status = $item['status'] ?? '';
                
                $api_modified = (string)($item['modified']['time'] ?? $item['modified'] ?? $item['updated_time'] ?? $item['time'] ?? '');
                
                $dbMovie = $repo->getMovieBySlug($slug);
                if (!$dbMovie) {
                    $new_count++;
                } else {
                    $db_episode_current = $dbMovie['episode_current'] ?? '';
                    $db_status = $dbMovie['status'] ?? '';
                    
                    $syncDb = getCrawlerSyncDB();
                    $stmt = $syncDb->prepare("SELECT modified FROM sync_meta WHERE slug = ? AND source = ?");
                    $stmt->execute([$slug, $sourceName]);
                    $saved_modified = $stmt->fetchColumn();
                    
                    if (!$saved_modified) {
                        $new_count++;
                    } elseif ($api_status && strtolower($api_status) !== strtolower($db_status)) {
                        $new_count++;
                    } elseif ($api_episode_current && $api_episode_current !== $db_episode_current) {
                        $new_count++;
                    } elseif ($api_modified && $api_modified !== $saved_modified) {
                        $new_count++;
                    }
                }
            }
        }
        $stats[] = "$sourceName: $new_count cập nhật";
        $total_new += $new_count;
    }
    
    echo json_encode([
        'status' => 'success', 
        'total' => $total_new,
        'message' => 'Tìm thấy ' . $total_new . ' phim/tập phim mới ở Trang 1. (' . implode(', ', $stats) . ')'
    ]);
    exit;
}

if ($action === 'smart_sync_source') {
    $sourceName = $_POST['source'] ?? 'kkphim';
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $consecutive = isset($_POST['consecutive']) ? (int)$_POST['consecutive'] : 0;
    $max_consecutive = 30;
    
    $crawler = new KKPhimCrawler($sourceName);
    $repo = getMovieRepository();
    
    $data = $crawler->getLatestMovies($page);
    if (!$data) {
        echo json_encode(['status' => 'success', 'logs' => ["Lỗi kết nối hoặc không có dữ liệu ở trang $page."], 'should_stop' => true, 'updated' => 0, 'consecutive' => $consecutive]);
        exit;
    }
    
    $sourceItems = $data['data']['items'] ?? $data['items'] ?? [];
    if (empty($sourceItems)) {
        echo json_encode(['status' => 'success', 'logs' => ["Không còn phim nào ở trang $page."], 'should_stop' => true, 'updated' => 0, 'consecutive' => $consecutive]);
        exit;
    }
    
    $logs = [];
    $updated = 0;
    $should_stop = false;
    
    foreach ($sourceItems as $item) {
        if (empty($item['slug'])) continue;
        $slug = $item['slug'];
        
        $api_episode_current = $item['episode_current'] ?? '';
        $api_status = $item['status'] ?? '';
        $api_modified = (string)($item['modified']['time'] ?? $item['modified'] ?? $item['updated_time'] ?? $item['time'] ?? '');
        
        $dbMovie = $repo->getMovieBySlug($slug);
        $needsUpdate = false;
        
        $syncDb = getCrawlerSyncDB();
        $stmt = $syncDb->prepare("SELECT modified FROM sync_meta WHERE slug = ? AND source = ?");
        $stmt->execute([$slug, $sourceName]);
        $saved_modified = $stmt->fetchColumn();
        
        if (!$dbMovie) {
            $needsUpdate = true;
            $logs[] = "Phát hiện phim mới: $slug";
        } else {
            $db_episode_current = $dbMovie['episode_current'] ?? '';
            $db_status = $dbMovie['status'] ?? '';
            
            if ($api_status && strtolower($api_status) !== strtolower($db_status)) {
                $needsUpdate = true;
                $logs[] = "Cập nhật trạng thái ($db_status -> $api_status): $slug";
            } elseif ($api_episode_current && $api_episode_current !== $db_episode_current) {
                $needsUpdate = true;
                $logs[] = "Cập nhật tập mới ($db_episode_current -> $api_episode_current): $slug";
            } elseif ($api_modified && $saved_modified && $api_modified !== $saved_modified) {
                $needsUpdate = true;
                $logs[] = "Cập nhật link mới ($sourceName vừa ra thêm tập): $slug";
            }
        }
        
        if ($needsUpdate) {
            $consecutive = 0;
            // Fetch and save
            $fullData = KKPhimCrawler::fetchMovieFromAllSources($slug);
            $pdo = getPDO();
    if ($fullData && !empty($fullData['movie'])) {
        if (KKPhimCrawler::isMovieBlockedByCategoriesOrCountries($fullData['movie'], $pdo)) {
            $logs[] = "-> Bỏ qua phim vì bị chặn: $slug";
            continue; // Skip this movie because it belongs to a blocked category or country
        }
    }
            if ($fullData['movie']) {
                $movie = $fullData['movie'];
                $episodesList = $fullData['episodes'];
                $peoplesData = $fullData['peoples'];
                $imagesData = $fullData['images'];
                $keywordsData = $fullData['keywords'];
                
                $domainPrefix = $movie['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
                $thumbUrl = $movie['thumb_url'] ?? '';
                if (!preg_match('/^http/', $thumbUrl)) $thumbUrl = rtrim($domainPrefix, '/') . '/' . ltrim($thumbUrl, '/');
                $posterUrl = $movie['poster_url'] ?? '';
                if (!preg_match('/^http/', $posterUrl)) $posterUrl = rtrim($domainPrefix, '/') . '/' . ltrim($posterUrl, '/');
                
                $tempThumb = $thumbUrl;
                $thumbUrl = $posterUrl;
                $posterUrl = $tempThumb;
                
                $actor = isset($movie['actor']) && is_array($movie['actor']) ? implode(', ', $movie['actor']) : '';
                $director = isset($movie['director']) && is_array($movie['director']) ? implode(', ', $movie['director']) : '';
                
                $movieId = $dbMovie ? $dbMovie['id'] : ($movie['_id'] ?? uniqid());

                $movieData = [
                    'id' => $movieId,
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
                    'view' => $dbMovie ? ($dbMovie['view'] ?? 0) : ($movie['view'] ?? 0),
                    'time' => $movie['time'] ?? '',
                    'peoples_json' => json_encode($peoplesData ?: []),
                    'images_json' => json_encode($imagesData ?: []),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $repo->saveMovie($movieData);
                
                $catRepo = getCategoryRepository();
                if (isset($movie['category']) && is_array($movie['category'])) {
                    foreach ($movie['category'] as $c) {
                        if (!empty($c['slug']) && !empty($c['name'])) $catRepo->saveCategory($c['slug'], $c['name'], 'genre');
                    }
                }
                if (isset($movie['country']) && is_array($movie['country'])) {
                    foreach ($movie['country'] as $c) {
                        if (!empty($c['slug']) && !empty($c['name'])) $catRepo->saveCategory($c['slug'], $c['name'], 'country');
                    }
                }
                
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
                                'type' => 'movie', 'item_id' => $movie['slug'], 'seo_title' => $movieData['name'],
                                'seo_desc' => mb_substr(strip_tags($movieData['content']), 0, 160), 'seo_keywords' => $keywordString
                            ];
                        } else {
                            $seoData['seo_keywords'] = $keywordString;
                        }
                        $seoRepo->saveSeoMetadata($seoData);
                    }
                }

                $pdo = getPDO();
                if ($pdo) {
                    $stmtDel = $pdo->prepare("DELETE FROM episodes WHERE movie_slug = ?");
                    $stmtDel->execute([$movie['slug']]);
                    
                    $sqlEp = "INSERT INTO episodes (movie_slug, server_name, name, slug, filename, embed_url, m3u8_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmtIns = $pdo->prepare($sqlEp);
                    
                    foreach ($episodesList as $server) {
                        $sName = $server['server_name'] ?? 'Server 1';
                        $epData = $server['server_data'] ?? [];
                        foreach ($epData as $ep) {
                            $stmtIns->execute([$movie['slug'], $sName, $ep['name'] ?? '', $ep['slug'] ?? '', $ep['filename'] ?? '', $ep['link_embed'] ?? '', $ep['link_m3u8'] ?? '']);
                        }
                    }
                }
                $logs[] = "-> Đã lưu phim: $slug";
                $updated++;
                if ($api_modified) {
                    $stmtSync = $syncDb->prepare("INSERT INTO sync_meta (slug, source, modified) VALUES (?, ?, ?) ON CONFLICT(slug, source) DO UPDATE SET modified = excluded.modified");
                    $stmtSync->execute([$slug, $sourceName, $api_modified]);
                }
            } else {
                $logs[] = "-> Lỗi không lấy được chi tiết phim: $slug";
            }
        } else {
            $consecutive++;
            if ($consecutive >= $max_consecutive) {
                $logs[] = "Đã chạm ngưỡng $max_consecutive phim liên tiếp không có cập nhật mới. Dừng quét nguồn này.";
                $should_stop = true;
                break;
            }
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'logs' => $logs,
        'updated' => $updated,
        'consecutive' => $consecutive,
        'should_stop' => $should_stop
    ]);
    exit;
}
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
