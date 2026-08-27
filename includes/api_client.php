
function _parseApiFilms($data, $apiSource, $isKids, $type, $slug, $page) {
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
        $result['pagination']['totalItems'] = $data['paginate']['total_items'] ?? 0;
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
    return _parseApiFilms($data, $apiSource, $isKids, $type, $slug, $page);
}

function fetchApiFilmsMulti($requests) {
    if (empty($requests)) return [];
    
    $settings = getSettings();
    $apiSource = $settings['apiSource'] ?? 'kkphim';
    $isKids = isKidsModeActive();
    
    require_once __DIR__ . '/cache_manager.php';
    $cache = new CacheManager();
    $results = [];
    $urls = [];
    $handles = [];
    $mh = curl_multi_init();
    
    foreach ($requests as $idx => $req) {
        $type = $req['type'] ?? 'home';
        $slug = $req['slug'] ?? '';
        $page = $req['page'] ?? 1;
        $keyword = $req['keyword'] ?? '';
        $category = $req['category'] ?? '';
        $country = $req['country'] ?? '';
        $year = $req['year'] ?? '';
        $sort = $req['sort'] ?? '';
        
        if ($type === 'genre') $type = 'the-loai';
        if ($type === 'country') $type = 'quoc-gia';
        
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
        
        if ($isKids && $type === 'home') {
            $type = 'the-loai';
            $slug = 'hoat-hinh';
        }
        
        $url = '';
        if ($apiSource === 'nguonc') {
            if ($type === 'home') $url = "https://phim.nguonc.com/api/films/phim-moi-cap-nhat?page=$page$queryString";
            else if ($type === 'search') $url = "https://phim.nguonc.com/api/films/search?keyword=" . rawurlencode($keyword) . "&page=$page$queryString";
            else if ($type === 'danh-sach') $url = "https://phim.nguonc.com/api/films/danh-sach/$slug?page=$page$queryString";
            else if ($type === 'the-loai') $url = "https://phim.nguonc.com/api/films/the-loai/$slug?page=$page$queryString";
            else if ($type === 'quoc-gia') $url = "https://phim.nguonc.com/api/films/quoc-gia/$slug?page=$page$queryString";
            else if ($type === 'nam-phat-hanh') $url = "https://phim.nguonc.com/api/films/nam-phat-hanh/$slug?page=$page$queryString";
        } else {
            if ($type === 'home') $url = "https://phimapi.com/v1/api/home?page=$page$queryString";
            else if ($type === 'search') $url = "https://phimapi.com/v1/api/tim-kiem?keyword=" . rawurlencode($keyword) . "&page=$page$queryString";
            else if (in_array($type, ['the-loai', 'quoc-gia'])) $url = "https://phimapi.com/v1/api/$type/$slug?page=$page$queryString";
            else if ($type === 'nam-phat-hanh') $url = "https://phimapi.com/v1/api/nam/$slug?page=$page$queryString";
            else $url = "https://phimapi.com/v1/api/danh-sach/" . ($slug ?: 'phim-le') . "?page=$page$queryString";
        }
        
        $cachedData = $cache->get($url, 900);
        if ($cachedData) {
            $results[$idx] = $cachedData;
        } else {
            $urls[$idx] = $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_multi_add_handle($mh, $ch);
            $handles[$idx] = $ch;
        }
    }
    
    if (!empty($handles)) {
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        foreach ($handles as $idx => $ch) {
            $res = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($res && $httpCode >= 200 && $httpCode < 300) {
                $cache->set($urls[$idx], $res);
                $results[$idx] = $res;
            } else {
                $staleData = $cache->getStale($urls[$idx]);
                if ($staleData) {
                    $results[$idx] = $staleData;
                }
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
    }
    curl_multi_close($mh);
    
    $finalResults = [];
    foreach ($requests as $idx => $req) {
        if (!isset($results[$idx])) continue;
        $data = json_decode($results[$idx], true);
        if ($data) {
            $type = $req['type'] ?? 'home';
            if ($isKids && $type === 'home') $type = 'the-loai';
            $slug = $req['slug'] ?? '';
            if ($isKids && $type === 'home') $slug = 'hoat-hinh';
            $page = $req['page'] ?? 1;
            
            $parsed = _parseApiFilms($data, $apiSource, $isKids, $type, $slug, $page);
            if ($parsed) {
                $finalResults[$idx] = $parsed;
            }
        }
    }
    
    return $finalResults;
}



function _parseMovieDetail($data, $apiSource) {
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
        $result['pagination']['totalItems'] = $data['paginate']['total_items'] ?? 0;
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
    return _parseMovieDetail($data, $apiSource);
}

function fetchApiMovieDetailMulti($slugs) {
    if (empty($slugs)) return [];
    
    $settings = getSettings();
    $apiSource = $settings['apiSource'] ?? 'kkphim';
    
    require_once __DIR__ . '/cache_manager.php';
    $cache = new CacheManager();
    $results = [];
    $urls = [];
    $handles = [];
    
    $mh = curl_multi_init();
    
    foreach ($slugs as $slug) {
        $url = '';
        if ($apiSource === 'nguonc') {
            $url = "https://phim.nguonc.com/api/film/" . rawurlencode($slug);
        } else {
            $url = "https://phimapi.com/phim/" . rawurlencode($slug);
        }
        
        $cachedData = $cache->get($url, 3600);
        if ($cachedData) {
            $results[$slug] = $cachedData;
        } else {
            $urls[$slug] = $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_multi_add_handle($mh, $ch);
            $handles[$slug] = $ch;
        }
    }
    
    if (!empty($handles)) {
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        foreach ($handles as $slug => $ch) {
            $res = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($res && $httpCode >= 200 && $httpCode < 300) {
                $cache->set($urls[$slug], $res);
                $results[$slug] = $res;
            } else {
                $staleData = $cache->getStale($urls[$slug]);
                if ($staleData) {
                    $results[$slug] = $staleData;
                }
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
    }
    curl_multi_close($mh);
    
    $finalResults = [];
    foreach ($slugs as $slug) {
        if (!isset($results[$slug])) continue;
        $data = json_decode($results[$slug], true);
        if ($data) {
            $parsed = _parseMovieDetail($data, $apiSource);
            if ($parsed) {
                $finalResults[] = $parsed;
            }
        }
    }
    
    return $finalResults;
}
