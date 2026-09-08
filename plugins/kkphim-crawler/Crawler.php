<?php
class KKPhimCrawler {
    private $source;
    private $baseUrl;
    
    public function __construct($source = 'kkphim') {
        $this->source = $source;
        if ($source === 'nguonc') {
            $this->baseUrl = 'https://phim.nguonc.com';
        } elseif ($source === 'vsmov') {
            $this->baseUrl = 'https://vsmov.com';
        } else {
            $this->baseUrl = 'https://phimapi.com';
        }
    }
    
    private function request($endpoint) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            return json_decode($response, true);
        }
        return null;
    }

    public function getLatestMovies($page = 1) {
        if ($this->source === 'nguonc') {
            return $this->request("/api/films/phim-moi-cap-nhat?page={$page}");
        } elseif ($this->source === 'vsmov') {
            return $this->request("/api/danh-sach/phim-moi-cap-nhat?page={$page}");
        } else {
            return $this->request("/v1/api/danh-sach?page={$page}");
        }
    }

    public function searchMovies($keyword, $limit = 10) {
        if ($this->source === 'nguonc') {
            return $this->request("/api/films/search?keyword=" . urlencode($keyword));
        } elseif ($this->source === 'vsmov') {
            return $this->request("/api/tim-kiem?keyword=" . urlencode($keyword) . "&limit=" . $limit);
        } else {
            return $this->request("/v1/api/tim-kiem?keyword=" . urlencode($keyword) . "&limit=" . $limit);
        }
    }

    public function getMovieDetail($slug) {
        if ($this->source === 'nguonc') {
            return $this->request("/api/film/" . urlencode($slug));
        } elseif ($this->source === 'vsmov') {
            return $this->request("/api/phim/" . urlencode($slug));
        } else {
            return $this->request("/v1/api/phim/" . urlencode($slug));
        }
    }

    public function getMovieImages($slug) {
        if ($this->source === 'kkphim') {
            return $this->request("/v1/api/phim/" . urlencode($slug) . "/images");
        }
        return null;
    }

    public function getMoviePeoples($slug) {
        if ($this->source === 'kkphim') {
            return $this->request("/v1/api/phim/" . urlencode($slug) . "/peoples");
        }
        return null;
    }

    public function getTmdbInfo($type, $id) {
        if ($this->source === 'kkphim') {
            return $this->request("/tmdb/{$type}/{$id}");
        }
        return null;
    }

    public function getImdbInfo($id) {
        if ($this->source === 'kkphim') {
            return $this->request("/imdb/title/{$id}");
        }
        return null;
    }

    public function getCategories() {
        if ($this->source === 'kkphim') {
            return $this->request("/the-loai");
        }
        return null;
    }

    public function getCountries() {
        if ($this->source === 'kkphim') {
            return $this->request("/quoc-gia");
        }
        return null;
    }

    public function getMovieKeywords($slug) {
        if ($this->source === 'kkphim') {
            return $this->request("/v1/api/phim/" . urlencode($slug) . "/keywords");
        }
        return null;
    }

    public function downloadImage($url, $savePath) {
        if (empty($url)) return false;
        
        $ch = curl_init($url);
        $fp = fopen($savePath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        
        return empty($error) && file_exists($savePath) && filesize($savePath) > 0;
    }

    public static function getLatestMoviesFromAllSources($page = 1, $sourceFilter = 'all') {
        $urls = [];
        if ($sourceFilter === 'all' || $sourceFilter === 'kkphim') {
            $urls['KKPhim'] = "https://phimapi.com/v1/api/danh-sach?page={$page}";
        }
        if ($sourceFilter === 'all' || $sourceFilter === 'nguonc') {
            $urls['Nguồn C'] = "https://phim.nguonc.com/api/films/phim-moi-cap-nhat?page={$page}";
        }
        if ($sourceFilter === 'all' || $sourceFilter === 'vsmov') {
            $urls['VsMov'] = "https://vsmov.com/api/danh-sach/phim-moi-cap-nhat?page={$page}";
        }
        $multi = curl_multi_init();
        $channels = [];
        foreach ($urls as $sourceName => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
            curl_multi_add_handle($multi, $ch);
            $channels[$sourceName] = $ch;
        }
        $active = null;
        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) curl_multi_select($multi, 0.5);
        } while ($active && $status == CURLM_OK);
        
        $results = [];
        foreach ($channels as $sourceName => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
            if ($httpCode >= 200 && $httpCode < 300 && $response) {
                $res = json_decode($response, true);
                if ($res) $results[$sourceName] = $res;
            }
        }
        curl_multi_close($multi);
        return $results;
    }

    public static function fetchMultipleMoviesFromAllSources(array $slugs) {
        if (empty($slugs)) return [];
        $multi = curl_multi_init();
        $channels = [];
        foreach ($slugs as $slug) {
            $urls = [
                'KKPhim' => "https://phimapi.com/v1/api/phim/" . urlencode($slug),
                'Nguồn C' => "https://phim.nguonc.com/api/film/" . urlencode($slug),
                'VsMov' => "https://vsmov.com/api/phim/" . urlencode($slug)
            ];
            foreach ($urls as $sourceName => $url) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
                curl_multi_add_handle($multi, $ch);
                $channels[$slug][$sourceName] = $ch;
            }
        }
        $active = null;
        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) curl_multi_select($multi, 0.5);
        } while ($active && $status == CURLM_OK);
        
        $results = [];
        foreach ($slugs as $slug) {
            $responses = [];
            foreach (['KKPhim', 'Nguồn C', 'VsMov'] as $sourceName) {
                $ch = $channels[$slug][$sourceName];
                $response = curl_multi_getcontent($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_multi_remove_handle($multi, $ch);
                curl_close($ch);
                if ($httpCode >= 200 && $httpCode < 300 && $response) {
                    $res = json_decode($response, true);
                    if ($res) $responses[$sourceName] = $res;
                }
            }
            $results[$slug] = self::mergeMovieData($responses, $slug);
        }
        curl_multi_close($multi);
        return $results;
    }

    public static function fetchMovieFromAllSources($slug) {
        $res = self::fetchMultipleMoviesFromAllSources([$slug]);
        return $res[$slug] ?? null;
    }

    private static function mergeMovieData(array $responses, $slug) {
        $mainMovie = null;
        $episodes = [];
        $peoplesData = [];
        $imagesData = [];
        $keywordsData = [];
        
        foreach ($responses as $sourceName => $res) {
            if ($res && (isset($res['data']['item']) || isset($res['movie']))) {
                $movie = $res['data']['item'] ?? $res['movie'];
                
                if ($mainMovie && empty($mainMovie['status']) && !empty($movie['status'])) {
                    $mainMovie['status'] = $movie['status'];
                }
                if (!$mainMovie) {
                    $mainMovie = $movie;
                    $mainMovie['APP_DOMAIN_CDN_IMAGE'] = $res['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
                    
                    if ($sourceName === 'Nguồn C') {
                        $mainMovie['origin_name'] = $mainMovie['original_name'] ?? '';
                        $mainMovie['content'] = $mainMovie['description'] ?? '';
                        $mainMovie['episode_current'] = $mainMovie['current_episode'] ?? '';
                        $mainMovie['thumb_url'] = $movie['thumb_url'] ?? '';
                        $mainMovie['poster_url'] = $movie['poster_url'] ?? '';
                        $mainMovie['actor'] = isset($mainMovie['casts']) ? explode(', ', $mainMovie['casts']) : [];
                        if (is_string($mainMovie['director'])) $mainMovie['director'] = explode(', ', $mainMovie['director']);
                        $mainMovie['quality'] = $movie['quality'] ?? '';
                        $mainMovie['lang'] = $movie['language'] ?? '';

                        $standardCategories = [];
                        $standardCountries = [];
                        if (isset($mainMovie['category']) && is_array($mainMovie['category'])) {
                            foreach ($mainMovie['category'] as $catGroup) {
                                $groupName = mb_strtolower($catGroup['group']['name'] ?? '', 'UTF-8');
                                if (isset($catGroup['list']) && is_array($catGroup['list'])) {
                                    foreach ($catGroup['list'] as $item) {
                                        $itemName = mb_strtolower($item['name'] ?? '', 'UTF-8');
                                        $safeSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', str_replace('đ', 'd', str_replace('Đ', 'd', iconv('UTF-8', 'ASCII//TRANSLIT', $item['name']))))));
                                        $itemData = ['name' => $item['name'], 'slug' => $safeSlug];
                                        
                                        if (strpos($groupName, 'quốc gia') !== false || strpos($groupName, 'quoc gia') !== false) {
                                            $standardCountries[] = $itemData;
                                        } elseif (strpos($groupName, 'thể loại') !== false || strpos($groupName, 'the loai') !== false) {
                                            $standardCategories[] = $itemData;
                                        } elseif ($groupName === 'năm' || $groupName === 'nam') {
                                            $mainMovie['year'] = $item['name'];
                                        } elseif (strpos($groupName, 'định dạng') !== false || strpos($groupName, 'dinh dang') !== false) {
                                            if (strpos($itemName, 'phim bộ') !== false) $mainMovie['type'] = 'series';
                                            elseif (strpos($itemName, 'phim lẻ') !== false) $mainMovie['type'] = 'single';
                                            elseif (strpos($itemName, 'hoạt hình') !== false) $mainMovie['type'] = 'hoathinh';
                                            elseif (strpos($itemName, 'tv show') !== false) $mainMovie['type'] = 'tvshows';
                                            
                                            if (strpos($itemName, 'đang chiếu') !== false) $mainMovie['status'] = 'ongoing';
                                            elseif (strpos($itemName, 'hoàn') !== false || strpos($itemName, 'trọn') !== false) $mainMovie['status'] = 'completed';
                                            elseif (strpos($itemName, 'sắp chiếu') !== false || strpos($itemName, 'trailer') !== false) $mainMovie['status'] = 'trailer';
                                        }
                                    }
                                }
                            }
                        }
                        $mainMovie['category'] = $standardCategories;
                        $mainMovie['country'] = $standardCountries;
                    }
                    
                    if ($sourceName === 'VsMov' || $sourceName === 'KKPhim') {
                        $mainMovie['thumb_url'] = $movie['thumb_url'] ?? '';
                        $mainMovie['poster_url'] = $movie['poster_url'] ?? '';
                    }
                }
                
                $epList = $res['episodes'] ?? ($movie['episodes'] ?? []);
                foreach ($epList as $server) {
                    $server['server_name'] = $sourceName . ' - ' . ($server['server_name'] ?? 'Server 1');
                    if (isset($server['items']) && !isset($server['server_data'])) {
                        $server['server_data'] = [];
                        foreach ($server['items'] as $epItem) {
                            $server['server_data'][] = [
                                'name' => $epItem['name'] ?? '',
                                'slug' => $epItem['slug'] ?? '',
                                'filename' => $epItem['name'] ?? '',
                                'link_embed' => $epItem['embed'] ?? ($epItem['link_embed'] ?? ''),
                                'link_m3u8' => $epItem['m3u8'] ?? ($epItem['link_m3u8'] ?? '')
                            ];
                        }
                        unset($server['items']);
                    }
                    $episodes[] = $server;
                }
            }
        }
        
        if ($mainMovie) {
            $crawler = new KKPhimCrawler('kkphim');
            $peoplesRes = $crawler->getMoviePeoples($slug);
            $peoplesData = ($peoplesRes && !empty($peoplesRes['data']['peoples'])) ? $peoplesRes['data']['peoples'] : [];
            
            $imagesRes = $crawler->getMovieImages($slug);
            $imagesData = ($imagesRes && isset($imagesRes['data'])) ? $imagesRes['data'] : [];
            
            $kwRes = $crawler->getMovieKeywords($slug);
            $keywordsData = ($kwRes && isset($kwRes['data']['keywords'])) ? $kwRes['data']['keywords'] : [];
        }
        
        return [
            'movie' => $mainMovie,
            'episodes' => $episodes,
            'peoples' => $peoplesData,
            'images' => $imagesData,
            'keywords' => $keywordsData
        ];
    }

    public static function isMovieBlockedByCategoriesOrCountries($movie, $pdo) {
        if (empty($movie['category']) && empty($movie['country'])) return false;
        
        $slugsToCheck = [];
        if (!empty($movie['category'])) {
            foreach ($movie['category'] as $cat) {
                if (!empty($cat['slug'])) $slugsToCheck[] = $cat['slug'];
            }
        }
        if (!empty($movie['country'])) {
            foreach ($movie['country'] as $country) {
                if (!empty($country['slug'])) $slugsToCheck[] = $country['slug'];
            }
        }
        
        if (empty($slugsToCheck)) return false;
        
        $placeholders = implode(',', array_fill(0, count($slugsToCheck), '?'));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug IN ($placeholders) AND is_blocked = 1");
        $stmt->execute($slugsToCheck);
        $count = $stmt->fetchColumn();
        
        return $count > 0;
    }
}
