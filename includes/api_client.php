<?php
// API Fetch Helper
require_once __DIR__ . '/cache_manager.php';

function fetchApiWithCache($url, $ttl = 900) {
    $cache = new CacheManager();
    $cachedData = $cache->get($url, $ttl);
    
    if ($cachedData) {
        return $cachedData;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($res && $httpCode >= 200 && $httpCode < 300) {
        $cache->set($url, $res);
        return $res;
    }
    
    // Fallback to stale cache if API fails
    $staleData = $cache->getStale($url);
    if ($staleData) {
        return $staleData;
    }
    
    return null;
}

function isKidsModeActive() {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    if (isset($_SESSION['current_profile']['is_kids_mode']) && $_SESSION['current_profile']['is_kids_mode'] == 1) {
        return true;
    }
    
    $headers = getallheaders();
    $profileId = $headers['X-Profile-Id'] ?? $headers['x-profile-id'] ?? '';
    if ($profileId) {
        $pdo = getPDO();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT is_kids_mode FROM user_profiles WHERE id = ?");
            $stmt->execute([$profileId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['is_kids_mode'] == 1) {
                return true;
            }
        }
    }
    return false;
}

function fetchApiFilms($type, $slug = '', $page = 1, $keyword = '', $category = '', $country = '', $year = '', $sort = '') {
    $settings = getSettings();
    $apiSource = $settings['apiSource'] ?? 'kkphim';
    
    // Map internal DB types to API types
    if ($type === 'genre') $type = 'the-loai';
    if ($type === 'country') $type = 'quoc-gia';
    
    $url = '';
    
    $queryParams = [];
    if (!empty($category)) $queryParams[] = "category=" . urlencode($category);
    if (!empty($country)) $queryParams[] = "country=" . urlencode($country);
    if (!empty($year)) $queryParams[] = "year=" . urlencode($year);
    if (!empty($sort)) {
        $sortParts = explode('-', $sort);
        if (count($sortParts) == 2) {
            $queryParams[] = "sort_field=" . urlencode($sortParts[0]);
            $queryParams[] = "sort_type=" . urlencode($sortParts[1]);
        }
    }
    
    $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
    
    $isKids = isKidsModeActive();
    if ($isKids && $type === 'home') {
        $type = 'the-loai';
        $slug = 'hoat-hinh';
    }
    
    if ($apiSource === 'nguonc') {
        if ($type === 'home') $url = "https://phim.nguonc.com/api/films/phim-moi-cap-nhat?page=$page$queryString";
        else if ($type === 'search') $url = "https://phim.nguonc.com/api/films/search?keyword=" . rawurlencode($keyword) . "&page=$page$queryString";
        else if ($type === 'danh-sach') $url = "https://phim.nguonc.com/api/films/danh-sach/$slug?page=$page$queryString";
        else if ($type === 'the-loai') $url = "https://phim.nguonc.com/api/films/the-loai/$slug?page=$page$queryString";
        else if ($type === 'quoc-gia') $url = "https://phim.nguonc.com/api/films/quoc-gia/$slug?page=$page$queryString";
        else if ($type === 'nam-phat-hanh') $url = "https://phim.nguonc.com/api/films/nam-phat-hanh/$slug?page=$page$queryString";
    } else { // kkphim
        if ($type === 'home') $url = "https://phimapi.com/v1/api/home?page=$page$queryString";
        else if ($type === 'search') $url = "https://phimapi.com/v1/api/tim-kiem?keyword=" . rawurlencode($keyword) . "&page=$page$queryString";
        else if (in_array($type, ['the-loai', 'quoc-gia'])) $url = "https://phimapi.com/v1/api/$type/$slug?page=$page$queryString";
        else if ($type === 'nam-phat-hanh') $url = "https://phimapi.com/v1/api/nam/$slug?page=$page$queryString";
        else $url = "https://phimapi.com/v1/api/danh-sach/" . ($slug ?: 'phim-le') . "?page=$page$queryString";
    }
    
    
    $res = fetchApiWithCache($url, 900); // 15 mins cache for list
    if (!$res) return null;
    $data = json_decode($res, true);
    
    $result = [
        'items' => [],
        'titlePage' => '',
        'domain' => 'https://phimimg.com/',
        'seoOnPage' => (object)[],
        'params' => (object)[],
        'pagination' => [
            'totalPages' => 1,
            'currentPage' => $page
        ]
    ];
    
    if ($apiSource === 'nguonc') {
        $result['items'] = $data['items'] ?? [];
        $result['pagination']['totalPages'] = $data['paginate']['total_page'] ?? 1;
        $result['pagination']['currentPage'] = $data['paginate']['current_page'] ?? $page;
        $result['domain'] = ''; // NguonC returns absolute URLs
    } else {
        // KKPhim
        if (isset($data['data']['items'])) $result['items'] = $data['data']['items'];
        else if (isset($data['items'])) $result['items'] = $data['items'];
        
        $result['titlePage'] = $data['data']['titlePage'] ?? '';
        $result['domain'] = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? $data['pathImage'] ?? 'https://phimimg.com/';
        $result['seoOnPage'] = !empty($data['data']['seoOnPage']) ? $data['data']['seoOnPage'] : (object)[];
        $result['params'] = !empty($data['data']['params']) ? $data['data']['params'] : (object)[];
        
        if (isset($data['data']['params']['pagination'])) {
            $result['pagination'] = $data['data']['params']['pagination'];
        } else if (isset($data['pagination'])) {
            $result['pagination'] = $data['pagination'];
        }
        
        // Swap thumb_url and poster_url for KKPhim
        if (!empty($result['items'])) {
            foreach ($result['items'] as &$item) {
                $temp = $item['thumb_url'] ?? '';
                $item['thumb_url'] = $item['poster_url'] ?? '';
                $item['poster_url'] = $temp;
            }
        }
    }
    
    if ($isKids && !empty($result['items'])) {
        $safeSlugs = ['hoat-hinh', 'gia-dinh', 'anime', 'hoc-duong', 'thieu-nhi'];
        if ($type !== 'the-loai' || !in_array($slug, $safeSlugs)) {
            $safeItems = [];
            foreach ($result['items'] as $item) {
                $isSafe = false;
                $cats = $item['category'] ?? (isset($item['categories']) ? $item['categories'] : []);
                if (is_array($cats)) {
                    foreach ($cats as $cat) {
                        $catSlug = is_array($cat) ? ($cat['slug'] ?? '') : (is_string($cat) ? $cat : '');
                        $catName = is_array($cat) ? ($cat['name'] ?? '') : (is_string($cat) ? $cat : '');
                        if (in_array($catSlug, $safeSlugs) || stripos($catName, 'hoạt hình') !== false || stripos($catName, 'gia đình') !== false || stripos($catName, 'anime') !== false || stripos($catName, 'thiếu nhi') !== false) {
                            $isSafe = true;
                            break;
                        }
                    }
                }
                if ($isSafe) {
                    $safeItems[] = $item;
                }
            }
            $result['items'] = $safeItems;
        }
    }
    if (!empty($result['items'])) {
        require_once __DIR__ . '/repositories.php';
        $repo = getMovieRepository();
        $blockedSlugs = $repo->getBlockedSlugs();
        
        if (!empty($blockedSlugs)) {
            $result['items'] = array_filter($result['items'], function($item) use ($blockedSlugs) {
                return !in_array($item['slug'] ?? '', $blockedSlugs);
            });
            $result['items'] = array_values($result['items']);
        }
    }
    
    return $result;
}

function fetchApiMovieDetail($slug) {
    $settings = getSettings();
    $apiSource = $settings['apiSource'] ?? 'kkphim';
    
    $url = '';
    if ($apiSource === 'nguonc') {
        $url = "https://phim.nguonc.com/api/film/" . rawurlencode($slug);
    } else { // kkphim
        $url = "https://phimapi.com/phim/" . rawurlencode($slug);
    }
    
    $res = fetchApiWithCache($url, 3600); // 1 hour cache for movie details
    if (!$res) return null;
    $data = json_decode($res, true);
    
    $result = [
        'movie' => null,
        'episodes' => [],
        'seoOnPage' => (object)[],
        'domain' => 'https://phimimg.com/'
    ];
    
    if ($apiSource === 'nguonc') {
        if (!isset($data['movie'])) return null;
        $result['movie'] = $data['movie'];
        if (isset($result['movie']['episodes'])) {
            $result['episodes'] = $result['movie']['episodes'];
            foreach ($result['episodes'] as &$server) {
                $server['server_data'] = $server['items'] ?? [];
                foreach ($server['server_data'] as &$ep) {
                    $ep['link_embed'] = $ep['embed'] ?? '';
                    $ep['link_m3u8'] = $ep['m3u8'] ?? '';
                }
            }
        }
        $result['domain'] = ''; // NguonC gives absolute URLs
        // Format mapping
        if (isset($result['movie']['original_name']) && !isset($result['movie']['origin_name'])) {
            $result['movie']['origin_name'] = $result['movie']['original_name'];
        }
        if (isset($result['movie']['description']) && !isset($result['movie']['content'])) {
            $result['movie']['content'] = $result['movie']['description'];
        }
    } else {
        // KKPhim
        if (isset($data['data']['item'])) {
            $result['movie'] = $data['data']['item'];
            $result['episodes'] = $result['movie']['episodes'] ?? [];
            $result['seoOnPage'] = !empty($data['data']['seoOnPage']) ? $data['data']['seoOnPage'] : (object)[];
            $result['domain'] = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
        } else if (isset($data['movie'])) {
            $result['movie'] = $data['movie'];
            $result['episodes'] = $data['episodes'] ?? [];
            if (empty($result['episodes']) && isset($result['movie']['episodes'])) {
                $result['episodes'] = $result['movie']['episodes'];
            }
            $result['domain'] = $data['pathImage'] ?? 'https://phimimg.com/';
        }
    }
    
    if (!empty($result['movie']['thumb_url']) && !preg_match('/^http/', $result['movie']['thumb_url'])) {
        $result['movie']['thumb_url'] = rtrim($result['domain'], '/') . '/' . ltrim($result['movie']['thumb_url'], '/');
    }
    if (!empty($result['movie']['poster_url']) && !preg_match('/^http/', $result['movie']['poster_url'])) {
        $result['movie']['poster_url'] = rtrim($result['domain'], '/') . '/' . ltrim($result['movie']['poster_url'], '/');
    }
    
    if ($apiSource === 'kkphim') {
        if (!empty($result['movie'])) {
            $temp = $result['movie']['thumb_url'] ?? '';
            $result['movie']['thumb_url'] = $result['movie']['poster_url'] ?? '';
            $result['movie']['poster_url'] = $temp;
        }
    }
    
    if (isKidsModeActive() && !empty($result['movie'])) {
        $safeSlugs = ['hoat-hinh', 'gia-dinh', 'anime', 'hoc-duong', 'thieu-nhi'];
        $isSafe = false;
        $cats = $result['movie']['category'] ?? [];
        if (is_array($cats)) {
            foreach ($cats as $cat) {
                $catSlug = is_array($cat) ? ($cat['slug'] ?? '') : (is_string($cat) ? $cat : '');
                $catName = is_array($cat) ? ($cat['name'] ?? '') : (is_string($cat) ? $cat : '');
                if (in_array($catSlug, $safeSlugs) || stripos($catName, 'hoạt hình') !== false || stripos($catName, 'gia đình') !== false || stripos($catName, 'anime') !== false || stripos($catName, 'thiếu nhi') !== false) {
                    $isSafe = true;
                    break;
                }
            }
        }
        if (!$isSafe) {
            return null; // Restricted in Kids Mode
        }
    }
    
    return $result;
}

function getYearsList() {
    $cache = new CacheManager();
    $cacheKey = 'years_list';
    
    $cached = $cache->get($cacheKey);
    if ($cached) return json_decode($cached, true);
    
    $ch = curl_init('https://phimapi.com/nam-phat-hanh');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if ($data) {
        $cache->set($cacheKey, json_encode($data), 86400); // 1 day
        return $data;
    }
    
    return [];
}
