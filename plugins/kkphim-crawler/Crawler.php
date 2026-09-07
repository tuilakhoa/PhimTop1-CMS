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

    // 1. Lấy danh sách phim mới nhất
    public function getLatestMovies($page = 1) {
        if ($this->source === 'nguonc') {
            return $this->request("/api/films/phim-moi-cap-nhat?page={$page}");
        } elseif ($this->source === 'vsmov') {
            return $this->request("/api/danh-sach/phim-moi-cap-nhat?page={$page}");
        } else {
            return $this->request("/v1/api/danh-sach?page={$page}");
        }
    }

    // 2. Tìm kiếm phim
    public function searchMovies($keyword, $limit = 10) {
        if ($this->source === 'nguonc') {
            return $this->request("/api/films/search?keyword=" . urlencode($keyword));
        } elseif ($this->source === 'vsmov') {
            return $this->request("/api/tim-kiem?keyword=" . urlencode($keyword) . "&limit=" . $limit);
        } else {
            return $this->request("/v1/api/tim-kiem?keyword=" . urlencode($keyword) . "&limit=" . $limit);
        }
    }

    // 3. Lấy chi tiết phim
    public function getMovieDetail($slug) {
        if ($this->source === 'nguonc') {
            return $this->request("/api/film/" . urlencode($slug));
        } elseif ($this->source === 'vsmov') {
            return $this->request("/api/phim/" . urlencode($slug));
        } else {
            return $this->request("/v1/api/phim/" . urlencode($slug));
        }
    }

    // 4. Lấy hình ảnh phim
    public function getMovieImages($slug) {
        if ($this->source === 'kkphim') {
            return $this->request("/v1/api/phim/" . urlencode($slug) . "/images");
        }
        return null; // Các nguồn khác có thể không hỗ trợ endpoint này riêng
    }

    // 5. Lấy thông tin diễn viên / đạo diễn
    public function getMoviePeoples($slug) {
        if ($this->source === 'kkphim') {
            return $this->request("/v1/api/phim/" . urlencode($slug) . "/peoples");
        }
        return null;
    }

    // 6. Lấy thông tin TMDB
    public function getTmdbInfo($type, $id) {
        if ($this->source === 'kkphim') {
            return $this->request("/tmdb/{$type}/{$id}");
        }
        return null;
    }

    // 7. Lấy thông tin IMDB
    public function getImdbInfo($id) {
        if ($this->source === 'kkphim') {
            return $this->request("/imdb/title/{$id}");
        }
        return null;
    }

    // 8. Lấy danh sách thể loại
    public function getCategories() {
        if ($this->source === 'kkphim') {
            return $this->request("/the-loai");
        }
        return null;
    }

    // 9. Lấy danh sách quốc gia
    public function getCountries() {
        if ($this->source === 'kkphim') {
            return $this->request("/quoc-gia");
        }
        return null;
    }

    // 10. Lấy thông tin keywords
    public function getMovieKeywords($slug) {
        if ($this->source === 'kkphim') {
            return $this->request("/v1/api/phim/" . urlencode($slug) . "/keywords");
        }
        return null;
    }

    // Tiện ích: Tải và lưu ảnh về local
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

    public static function fetchMovieFromAllSources($slug) {
        $urls = [
            'KKPhim' => "https://phimapi.com/v1/api/phim/" . urlencode($slug),
            'Nguồn C' => "https://phim.nguonc.com/api/film/" . urlencode($slug),
            'VsMov' => "https://vsmov.com/api/phim/" . urlencode($slug)
        ];
        
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
            if ($active) {
                curl_multi_select($multi, 0.5);
            }
        } while ($active && $status == CURLM_OK);
        
        $mainMovie = null;
        $episodes = [];
        $peoplesData = [];
        $imagesData = [];
        $keywordsData = [];
        
        foreach ($channels as $sourceName => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300 && $response) {
                $res = json_decode($response, true);
                if ($res && (isset($res['data']['item']) || isset($res['movie']))) {
                    $movie = $res['data']['item'] ?? $res['movie'];
                    
                    if ($mainMovie && empty($mainMovie['status']) && !empty($movie['status'])) {
                        $mainMovie['status'] = $movie['status'];
                    }
                    if (!$mainMovie) {
                        $mainMovie = $movie;
                        $mainMovie['APP_DOMAIN_CDN_IMAGE'] = $res['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
                        
                        // Chuẩn hóa dữ liệu Nguồn C về chuẩn chung
                        // Nếu là Nguồn C, chuẩn hóa dữ liệu
                        if ($sourceName === 'Nguồn C') {
                            $mainMovie['origin_name'] = $mainMovie['original_name'] ?? '';
                            $mainMovie['content'] = $mainMovie['description'] ?? '';
                            $mainMovie['episode_current'] = $mainMovie['current_episode'] ?? '';
                            

                            $mainMovie['origin_name'] = $mainMovie['original_name'] ?? '';
                            $mainMovie['content'] = $mainMovie['description'] ?? '';
                            $mainMovie['episode_current'] = $mainMovie['current_episode'] ?? '';
                            // Đưa về chuẩn KKPhim gốc (Không đảo ngược): thumb_url là dọc, poster_url là ngang
                            $mainMovie['thumb_url'] = $movie['thumb_url'] ?? '';
                            $mainMovie['poster_url'] = $movie['poster_url'] ?? '';
                            $mainMovie['actor'] = isset($mainMovie['casts']) ? explode(', ', $mainMovie['casts']) : [];
                            if (is_string($mainMovie['director'])) {
                                $mainMovie['director'] = explode(', ', $mainMovie['director']);
                            }
                            
                            $mainMovie['quality'] = $movie['quality'] ?? '';
                            $mainMovie['lang'] = $movie['language'] ?? '';

                            // Phân tách Category của Nguồn C
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
                        
                        
                        // Chuẩn hóa dữ liệu VsMov
                        if ($sourceName === 'VsMov') {
                            // Đưa về chuẩn KKPhim gốc (Không đảo ngược): thumb_url là dọc, poster_url là ngang
                            $mainMovie['thumb_url'] = $movie['thumb_url'] ?? '';
                            $mainMovie['poster_url'] = $movie['poster_url'] ?? '';
                        }

                        if ($sourceName === 'KKPhim') {
                            // Đưa về chuẩn KKPhim gốc (Không đảo ngược): thumb_url là dọc, poster_url là ngang
                            $mainMovie['thumb_url'] = $movie['thumb_url'] ?? '';
                            $mainMovie['poster_url'] = $movie['poster_url'] ?? '';
                        }
                    }
                    
                    $epList = $res['episodes'] ?? ($movie['episodes'] ?? []);
                    foreach ($epList as $server) {
                        $server['server_name'] = $sourceName . ' - ' . ($server['server_name'] ?? 'Server 1');
                        
                        // Chuẩn hóa mảng tập phim của Nguồn C (dùng 'items' và 'embed') sang chuẩn KKPhim ('server_data', 'link_embed')
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
        }
        curl_multi_close($multi);
        
        // Luôn fetch thông tin diễn viên, hình ảnh từ KKPhim (nguồn giàu meta nhất)
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
}
