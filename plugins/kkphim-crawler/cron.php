<?php
// Cơ chế cron cho kkphimcrawler
set_time_limit(0);
if (php_sapi_name() !== "cli" && (!isset($_GET["key"]) || $_GET["key"] !== "kkphim_cron")) { die("Unauthorized"); }

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

// Log chức năng
function log_cron($msg) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n";
}

log_cron("Bắt đầu chạy cron cập nhật phim (Smart Crawl - Max 10 Pages)");


$reqSource = isset($_GET['source']) ? $_GET['source'] : (isset($argv[1]) ? $argv[1] : 'all');
$crawlers = [];
$sourceNames = [];
if ($reqSource === 'kkphim' || $reqSource === 'all') { $crawlers[] = new KKPhimCrawler('kkphim'); $sourceNames[] = 'KKPhim'; }
if ($reqSource === 'nguonc' || $reqSource === 'all') { $crawlers[] = new KKPhimCrawler('nguonc'); $sourceNames[] = 'Nguồn C'; }
if ($reqSource === 'vsmov' || $reqSource === 'all') { $crawlers[] = new KKPhimCrawler('vsmov'); $sourceNames[] = 'VsMov'; }
foreach ($crawlers as $index => $crawler) {
    $currentSource = $sourceNames[$index] ?? "Nguồn $index";
    log_cron("--- Bắt đầu quét nguồn: $currentSource ---");
    
    $consecutive_no_updates = 0;
    $shouldStopSource = false;
    
    for ($page = 1; $page <= $max_pages; $page++) {
        log_cron("[$currentSource] Đang lấy dữ liệu Trang $page...");
        $data = $crawler->getLatestMovies($page);
        
        if (!$data) {
            log_cron("[$currentSource] Lỗi kết nối hoặc không có dữ liệu ở trang $page. Dừng quét nguồn này.");
            break;
        }
        
        $sourceItems = $data['data']['items'] ?? $data['items'] ?? [];
        if (empty($sourceItems)) {
            log_cron("[$currentSource] Không còn phim nào ở trang $page. Dừng quét.");
            break; // Hết phim
        }
        
        foreach ($sourceItems as $item) {
            if (empty($item['slug'])) continue;
            
            $slug = $item['slug'];
            
            
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
                $db_updated_at = $dbMovie['updated_at'] ?? '';
                
                $api_modified = $item['modified']['time'] ?? $item['modified'] ?? $item['updated_time'] ?? $item['time'] ?? '';
                if (is_array($api_modified)) $api_modified = '';
                
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
                // NẾU TỔNG SỐ TẬP KHÔNG ĐỔI (DO NGUỒN KHÁC ĐÃ CẬP NHẬT TRƯỚC), NHƯNG NGUỒN NÀY VỪA CẬP NHẬT SAU
                elseif ($api_modified && $db_updated_at && strtotime($api_modified) > strtotime($db_updated_at)) {
                    $needsUpdate = true;
                    log_cron("Cập nhật link mới từ $currentSource (vừa ra thêm tập): $slug");
                }
            }

            if ($needsUpdate) {
                $consecutive_no_updates = 0; // Reset đếm khi có phim mới/cập nhật
                
                // Tiến hành crawl chi tiết phim này từ cả 3 nguồn (Logic cũ)
                $fullData = KKPhimCrawler::fetchMovieFromAllSources($slug);
    if ($fullData && !empty($fullData['movie'])) {
        if (KKPhimCrawler::isMovieBlockedByCategoriesOrCountries($fullData['movie'], $pdo)) {
            return false; // Skip this movie because it belongs to a blocked category or country
        }
    }
                
                if ($fullData['movie']) {
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
                    
                    // Đảo ngược thumb_url và poster_url theo rule của CMS
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
                        'peoples_json' => json_encode($peoplesData ?: []),
                        'images_json' => json_encode($imagesData ?: []),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
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
                    log_cron("-> Đã lưu phim thành công: $slug");
                    $updated++;
                } else {
                    log_cron("-> Lỗi không lấy được chi tiết phim: $slug");
                }
            } else {
                $skipped++;
                $consecutive_no_updates++;
                log_cron("Skipped $slug, consecutive: $consecutive_no_updates");
                
                // Smart Early Stop
                if ($consecutive_no_updates >= $max_consecutive_no_updates) {
                    log_cron("[$currentSource] Đã quét $max_consecutive_no_updates phim liên tiếp không có cập nhật mới. Dừng quét nguồn này sớm.");
                    $shouldStopSource = true;
                    break;
                }
            }
        }
        
        if ($shouldStopSource) {
            break;
        }
    }
}

log_cron("Cron Smart Crawl hoàn thành. Đã cập nhật/thêm mới: $updated phim. Bỏ qua: $skipped phim không thay đổi.");
?>
