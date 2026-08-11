<?php

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_set_cookie_params([
        'lifetime' => 86400 * 30, // 30 days
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
$dbConfigPath = __DIR__ . '/../config.json';
$jwtSecret = "super-secret-key-for-movie-app";

function getDbConfig() {
    global $dbConfigPath;
    if (file_exists($dbConfigPath)) {
        return json_decode(file_get_contents($dbConfigPath), true);
    }
    return null;
}

function saveDbConfig($newConfig) {
    global $dbConfigPath;
    file_put_contents($dbConfigPath, json_encode($newConfig, JSON_PRETTY_PRINT));
}

function getPDO() {
    $config = getDbConfig();
    if (!$config) return null;
    
    // Prevent PDO connection if dbType is firestore
    if (isset($config['type']) && $config['type'] === 'firestore') {
        return null; // A proper Firestore adapter needs to be implemented
    }
    
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password'] ?? '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

function getFirestore() {
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'firestore') {
        require_once __DIR__ . '/firestore_helper.php';
        return new FirestoreClient($config['projectId'], $config['serviceAccount']);
    }
    return null;
}

function getSettings() {
    $pdo = getPDO();
    $defaultSettings = [
        'initialized' => false,
        'id' => 1,
        'adminPath' => '/admin',
        'displayMode' => 'api',
        'theme' => 'dark',
        'cmsVersion' => '1.0.2',
        'githubRepo' => 'kkphim/cms-core',
        'githubBranch' => 'main',
        'githubToken' => '',
        'autoCheckUpdates' => 1,
        'lastUpdateCheck' => '',
        'latestRelease' => '',
        'siteName' => 'PhimTop1',
        'seoTitle' => 'PhimTop1 - Xem Phim Online Chất Lượng Cao',
        'seoDesc' => 'Hệ thống xem phim trực tuyến chất lượng cao, cập nhật liên tục mỗi ngày.',
        'seoKeywords' => 'xem phim, phim online, phim hay, phim vietsub',
        'logoUrl' => '',
        'useLogoAsFavicon' => 0,
        'verifyGoogle' => '',
        'verifyBing' => '',
        'verifyYandex' => '',
        'customHead' => '',
        'customBody' => '',
        'footerText' => '',
        'socialFacebook' => '',
        'socialYoutube' => '',
        'socialTwitter' => '',
        'socialTelegram' => '',
        'cfTurnstileKey' => '',
        'cfTurnstileSecret' => '',
        'cfAnalyticsToken' => '',
        'cfApiToken' => '',
        'cfAccountId' => '',
        'cfZoneId' => '',
        'gaMeasurementId' => '',
        'gaPropertyId' => '',
        'tmdbApiKey' => 'b775c363e46a24e8c885479b0131c4d2',
        'googleIndexJson' => '',
        'indexNowKey' => '',
        'slugMovie' => 'phim',
        'slugWatch' => 'xem-phim',
        'slugComic' => 'truyen',
        'slugRead' => 'doc-truyen',
        'slugComicList' => 'danh-sach-truyen',
        'slugList' => 'danh-sach',
        'slugGenre' => 'the-loai',
        'slugCountry' => 'quoc-gia',
        'sitemapLimit' => 5000,
        'sitemapIncludeMovies' => 1,
        'sitemapIncludeCategories' => 1,
        'sitemapLinksPerFile' => 1000,
        'googleClientId' => '',
        'googleClientSecret' => '',
        'googleAllowedEmails' => '',
        'msClientId' => '',
        'msClientSecret' => '',
        'msTenantId' => 'common',
        'geminiApiKey' => '',
        'openaiApiKey' => '',
        'aiProvider' => 'gemini',
        'allowAutoUpdate' => 1,
        'appApiKey' => '',
        'appBannerEnabled' => 0,
        'appDownloadUrl' => '',
        'appDownloadUrlTv' => '',
        'appSchemaEnabled' => 0,
        'appSchemaName' => '',
        'appSchemaOs' => 'Android, iOS',
        'appSchemaCategory' => 'EntertainmentApplication',
        'appSchemaPrice' => '0',
        'appSchemaCurrency' => 'VND',
        'appSchemaRatingValue' => '4.8',
        'appSchemaRatingCount' => '1250',
        'featuredType' => 'latest',
        'featuredMovieSlug' => '',
        'featuredStyle' => 'slider',
        'featuredCount' => 5,
        'enableWatchingSession' => 1,
        'trackAnonymousSession' => 0
    ];
    
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'firestore') {
        require_once __DIR__ . '/firestore_helper.php';
        $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
        $data = $fs->getDocument('settings', '1');
        if ($data) {
            $data['initialized'] = true;
            return array_merge($defaultSettings, $data);
        }
        return $defaultSettings;
    }

    if (!$pdo) return $defaultSettings;
    
    try {
        $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['initialized'] = true;
            
            // Auto-migrate schema based on code version
            if (!isset($row['db_version']) || $row['db_version'] < 4) {
                // Update db_version to trigger migrations in updateSettings
                updateSettings(['db_version' => 4]);
                $row['db_version'] = 4;
            }
            
            return array_merge($defaultSettings, $row);
        }
    } catch (PDOException $e) {
        // Handle case where table doesn't exist
    }
    return $defaultSettings;
}

function updateSettings($updates) {
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'firestore') {
        require_once __DIR__ . '/firestore_helper.php';
        $fs = new FirestoreClient($config['projectId'], $config['serviceAccount']);
        $fs->setDocument('settings', '1', $updates);
        return;
    }

    $pdo = getPDO();
    if (!$pdo || empty($updates)) return;
    
    // Auto-migrate schema if columns are missing
    $migrations = [
        "CREATE TABLE IF NOT EXISTS movies (
            id VARCHAR(100) PRIMARY KEY, name VARCHAR(255) NOT NULL, origin_name VARCHAR(255),
            slug VARCHAR(255) UNIQUE NOT NULL, thumb_url TEXT, poster_url TEXT, year INT, type VARCHAR(100),
            status VARCHAR(100), episode_current VARCHAR(100), quality VARCHAR(100), lang VARCHAR(100),
            chieu_rap TINYINT(1) DEFAULT 0, view INT DEFAULT 0, content TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS categories (
            slug VARCHAR(255) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            movie_slug VARCHAR(255) NOT NULL,
            user_name VARCHAR(100) NOT NULL,
            content TEXT NOT NULL,
            status VARCHAR(50) DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            password VARCHAR(255) NULL,
            avatar TEXT,
            role VARCHAR(50) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS watch_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_email VARCHAR(255) NOT NULL,
            movie_slug VARCHAR(255) NOT NULL,
            movie_name VARCHAR(255) NOT NULL,
            episode_name VARCHAR(100) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_movie (user_email, movie_slug)
        )",
        "CREATE TABLE IF NOT EXISTS user_follows (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_email VARCHAR(255) NOT NULL,
            item_slug VARCHAR(255) NOT NULL,
            item_type VARCHAR(50) DEFAULT 'movie',
            item_name VARCHAR(255) NOT NULL,
            thumb_url TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_item (user_email, item_slug)
        )",
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_email VARCHAR(255) NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            url VARCHAR(255),
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "ALTER TABLE settings ADD COLUMN siteName VARCHAR(255) DEFAULT 'PhimTop1'",
        "ALTER TABLE members ADD COLUMN password VARCHAR(255) NULL",
        "ALTER TABLE members ADD COLUMN role VARCHAR(50) DEFAULT 'user'",
        "ALTER TABLE settings ADD COLUMN seoTitle TEXT",
        "ALTER TABLE settings ADD COLUMN seoDesc TEXT",
        "ALTER TABLE settings ADD COLUMN seoKeywords TEXT",
        "ALTER TABLE settings ADD COLUMN logoUrl VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN verifyGoogle VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN verifyBing VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN verifyYandex VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN customHead TEXT",
        "ALTER TABLE settings ADD COLUMN customBody TEXT",
        "ALTER TABLE settings ADD COLUMN footerText TEXT",
        "ALTER TABLE settings ADD COLUMN socialFacebook VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN socialYoutube VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN socialTwitter VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN socialTelegram VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN cfTurnstileKey VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN cfTurnstileSecret VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN db_version INT DEFAULT 1",
        "ALTER TABLE settings ADD COLUMN cfAnalyticsToken VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN cfApiToken VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN cfAccountId VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN cfZoneId VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN gaMeasurementId VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN gaPropertyId VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN tmdbApiKey VARCHAR(255) DEFAULT 'b775c363e46a24e8c885479b0131c4d2'",
        "ALTER TABLE settings ADD COLUMN googleIndexJson TEXT",
        "ALTER TABLE settings ADD COLUMN indexNowKey VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN slugMovie VARCHAR(50) DEFAULT 'phim'",
        "ALTER TABLE settings ADD COLUMN slugWatch VARCHAR(50) DEFAULT 'xem-phim'",
        "ALTER TABLE settings ADD COLUMN slugComic VARCHAR(50) DEFAULT 'truyen'",
        "ALTER TABLE settings ADD COLUMN slugRead VARCHAR(50) DEFAULT 'doc-truyen'",
        "ALTER TABLE settings ADD COLUMN slugComicList VARCHAR(50) DEFAULT 'danh-sach-truyen'",
        "ALTER TABLE settings ADD COLUMN slugList VARCHAR(50) DEFAULT 'danh-sach'",
        "ALTER TABLE settings ADD COLUMN slugGenre VARCHAR(50) DEFAULT 'the-loai'",
        "ALTER TABLE settings ADD COLUMN slugCountry VARCHAR(50) DEFAULT 'quoc-gia'",
        "ALTER TABLE settings ADD COLUMN sitemapLimit INT DEFAULT 5000",
        "ALTER TABLE settings ADD COLUMN sitemapIncludeMovies TINYINT(1) DEFAULT 1",
        "ALTER TABLE settings ADD COLUMN sitemapIncludeCategories TINYINT(1) DEFAULT 1",
        "ALTER TABLE settings ADD COLUMN sitemapLinksPerFile INT DEFAULT 1000",
        "ALTER TABLE settings ADD COLUMN googleClientId VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN googleClientSecret VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN googleAllowedEmails TEXT",
        "ALTER TABLE settings ADD COLUMN updateServerUrl VARCHAR(255) DEFAULT 'tuilakhoa/PhimTop1-CMS'",
        "ALTER TABLE settings ADD COLUMN allowAutoUpdate TINYINT(1) DEFAULT 1",
        "ALTER TABLE settings ADD COLUMN apiSource VARCHAR(50) DEFAULT 'kkphim'",
        "ALTER TABLE settings ADD COLUMN msClientId VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN msClientSecret VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN msTenantId VARCHAR(255) DEFAULT 'common'",
        "ALTER TABLE settings ADD COLUMN geminiApiKey VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN openaiApiKey VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN aiProvider VARCHAR(50) DEFAULT 'gemini'",
        "ALTER TABLE settings ADD COLUMN comicApiUrl VARCHAR(255) DEFAULT 'https://otruyenapi.com/v1/api'",
        "ALTER TABLE settings ADD COLUMN appApiKey VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN appBannerEnabled TINYINT(1) DEFAULT 0",
        "ALTER TABLE settings ADD COLUMN appDownloadUrl VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN appDownloadUrlTv VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN appSchemaEnabled TINYINT(1) DEFAULT 0",
        "ALTER TABLE settings ADD COLUMN appSchemaName VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN appSchemaOs VARCHAR(255) DEFAULT 'Android, iOS'",
        "ALTER TABLE settings ADD COLUMN appSchemaCategory VARCHAR(255) DEFAULT 'EntertainmentApplication'",
        "ALTER TABLE settings ADD COLUMN appSchemaPrice VARCHAR(50) DEFAULT '0'",
        "ALTER TABLE settings ADD COLUMN appSchemaCurrency VARCHAR(10) DEFAULT 'VND'",
        "ALTER TABLE settings ADD COLUMN appSchemaRatingValue VARCHAR(10) DEFAULT '4.8'",
        "ALTER TABLE settings ADD COLUMN appSchemaRatingCount VARCHAR(20) DEFAULT '1250'",
        "CREATE TABLE IF NOT EXISTS seo_metadata (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(50) NOT NULL,
            item_id VARCHAR(255) NOT NULL,
            custom_slug VARCHAR(255) NULL,
            seo_title VARCHAR(255) NULL,
            seo_desc TEXT NULL,
            seo_keywords TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY type_item (type, item_id),
            UNIQUE KEY type_custom_slug (type, custom_slug)
        )",
        "CREATE TABLE IF NOT EXISTS active_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            device_id VARCHAR(100) UNIQUE NOT NULL,
            device_name VARCHAR(255),
            platform VARCHAR(50) DEFAULT 'web',
            movie_slug VARCHAR(255),
            movie_name VARCHAR(255),
            episode_name VARCHAR(100),
            user_name VARCHAR(255),
            is_logged_in TINYINT(1) DEFAULT 0,
            pending_command VARCHAR(50),
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "ALTER TABLE settings ADD COLUMN featuredType VARCHAR(50) DEFAULT 'latest'",
        "ALTER TABLE settings ADD COLUMN featuredMovieSlug VARCHAR(255) DEFAULT ''",
        "ALTER TABLE settings ADD COLUMN featuredStyle VARCHAR(50) DEFAULT 'slider'",
        "ALTER TABLE settings ADD COLUMN featuredCount INT DEFAULT 5",
        "ALTER TABLE settings ADD COLUMN enableWatchingSession TINYINT(1) DEFAULT 1",
        "ALTER TABLE settings ADD COLUMN trackAnonymousSession TINYINT(1) DEFAULT 0",
        "ALTER TABLE settings ADD COLUMN useLogoAsFavicon TINYINT(1) DEFAULT 0",
        // Cleanup old deprecated columns from original setup
        "ALTER TABLE settings DROP COLUMN githubRepo",
        "ALTER TABLE settings DROP COLUMN cmsVersion",
        "ALTER TABLE settings DROP COLUMN updateServerUrl"
    ];

    foreach ($migrations as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Exception $e) {} // Ignore if columns already exist
    }

    $setClause = [];
    $values = [];
    foreach ($updates as $k => $v) {
        $setClause[] = "$k = ?";
        if (is_array($v) || is_object($v)) $v = json_encode($v);
        if (is_bool($v)) $v = $v ? 1 : 0;
        $values[] = $v;
    }
    $values[] = 1;
    $sql = "UPDATE settings SET " . implode(', ', $setClause) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
}

function checkSetup() {
    $settings = getSettings();
    $currentFile = basename($_SERVER['PHP_SELF']);
    if (!$settings['initialized']) {
        if ($currentFile !== 'setup.php') {
            header("Location: /setup");
            exit;
        }
    } else {
        if ($currentFile === 'setup.php') {
            header("Location: /");
            exit;
        }
    }
}

function requireAdmin() {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    if (!isset($_SESSION['admin'])) {
        header("Location: /login");
        exit;
    }
}

// SEO Helper Functions
require_once __DIR__ . '/repositories.php';

function getSeoMetadata($type, $slug) {
    $repo = getSeoRepository();
    return $repo->getSeoMetadata($type, $slug);
}

function resolveCustomSlug($type, $customSlug) {
    $repo = getSeoRepository();
    return $repo->resolveCustomSlug($type, $customSlug);
}

// API Fetch Helper
function fetchApiWithCache($url, $ttl = 900) {
    $cacheDir = __DIR__ . '/../cache/api';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0777, true);
    
    $cacheFile = $cacheDir . '/' . md5($url) . '.json';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        $cachedData = file_get_contents($cacheFile);
        if ($cachedData) return $cachedData;
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
        file_put_contents($cacheFile, $res);
        return $res;
    }
    
    // Fallback to stale cache if API fails
    if (file_exists($cacheFile)) {
        return file_get_contents($cacheFile);
    }
    
    return null;
}

function fetchApiFilms($type, $slug = '', $page = 1, $keyword = '', $category = '', $country = '', $year = '') {
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
    
    $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
    
    if ($apiSource === 'nguonc') {
        if ($type === 'home') $url = "https://phim.nguonc.com/api/films/phim-moi-cap-nhat?page=$page$queryString";
        else if ($type === 'search') $url = "https://phim.nguonc.com/api/films/search?keyword=" . rawurlencode($keyword) . "&page=$page$queryString";
        else if ($type === 'danh-sach') $url = "https://phim.nguonc.com/api/films/danh-sach/$slug?page=$page$queryString";
        else if ($type === 'the-loai') $url = "https://phim.nguonc.com/api/films/the-loai/$slug?page=$page$queryString";
        else if ($type === 'quoc-gia') $url = "https://phim.nguonc.com/api/films/quoc-gia/$slug?page=$page$queryString";
        else if ($type === 'nam-phat-hanh') $url = "https://phim.nguonc.com/api/films/nam-phat-hanh/$slug?page=$page$queryString";
    } else if ($apiSource === 'ophim') {
        if ($type === 'home') $url = "https://ophim1.com/danh-sach/phim-moi-cap-nhat?page=$page$queryString";
        else if ($type === 'search') $url = "https://ophim1.com/v1/api/tim-kiem?keyword=" . rawurlencode($keyword) . "&page=$page$queryString";
        else if (in_array($type, ['the-loai', 'quoc-gia'])) $url = "https://ophim1.com/v1/api/$type/$slug?page=$page$queryString";
        else if ($type === 'nam-phat-hanh') $url = "https://ophim1.com/v1/api/nam/$slug?page=$page$queryString";
        else $url = "https://ophim1.com/v1/api/danh-sach/" . ($slug ?: 'phim-le') . "?page=$page$queryString";
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
        'seoOnPage' => [],
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
        // KKPhim / Ophim
        if (isset($data['data']['items'])) $result['items'] = $data['data']['items'];
        else if (isset($data['items'])) $result['items'] = $data['items'];
        
        $result['titlePage'] = $data['data']['titlePage'] ?? '';
        $result['domain'] = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? $data['pathImage'] ?? 'https://phimimg.com/';
        $result['seoOnPage'] = $data['data']['seoOnPage'] ?? [];
        
        if (isset($data['data']['params']['pagination'])) {
            $result['pagination'] = $data['data']['params']['pagination'];
        } else if (isset($data['pagination'])) {
            $result['pagination'] = $data['pagination'];
        }
        
        // Swap thumb_url and poster_url for KKPhim/Ophim
        if (!empty($result['items'])) {
            foreach ($result['items'] as &$item) {
                $temp = $item['thumb_url'] ?? '';
                $item['thumb_url'] = $item['poster_url'] ?? '';
                $item['poster_url'] = $temp;
            }
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
    } else if ($apiSource === 'ophim') {
        $url = "https://ophim1.com/phim/" . rawurlencode($slug);
    } else { // kkphim
        $url = "https://phimapi.com/phim/" . rawurlencode($slug);
    }
    
    $res = fetchApiWithCache($url, 3600); // 1 hour cache for movie details
    if (!$res) return null;
    $data = json_decode($res, true);
    
    $result = [
        'movie' => null,
        'episodes' => [],
        'seoOnPage' => [],
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
        // KKPhim / Ophim
        if (isset($data['data']['item'])) {
            $result['movie'] = $data['data']['item'];
            $result['episodes'] = $result['movie']['episodes'] ?? [];
            $result['seoOnPage'] = $data['data']['seoOnPage'] ?? [];
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
    
    if ($apiSource === 'kkphim' || $apiSource === 'ophim') {
        if (!empty($result['movie'])) {
            $temp = $result['movie']['thumb_url'] ?? '';
            $result['movie']['thumb_url'] = $result['movie']['poster_url'] ?? '';
            $result['movie']['poster_url'] = $temp;
        }
    }
    
    return $result;
}

// Comic API Fetch Helper (OTruyen)
function fetchApiComics($type, $slug = '', $page = 1, $keyword = '') {
    global $settings;
    $baseUrl = rtrim($settings['comicApiUrl'] ?? 'https://otruyenapi.com/v1/api', '/');
    $url = '';
    
    if ($type === 'home') $url = "$baseUrl/home";
    else if ($type === 'search') $url = "$baseUrl/tim-kiem?keyword=" . rawurlencode($keyword) . "&page=$page";
    else if (in_array($type, ['the-loai'])) $url = "$baseUrl/the-loai/$slug?page=$page";
    else $url = "$baseUrl/danh-sach/" . ($slug ?: 'truyen-moi') . "?page=$page";
    
    $res = fetchApiWithCache($url, 900);
    if (!$res) return null;
    $data = json_decode($res, true);
    
    $result = [
        'items' => [],
        'titlePage' => '',
        'domain' => 'https://otruyencdn.com/',
        'seoOnPage' => [],
        'pagination' => [
            'totalPages' => 1,
            'currentPage' => $page
        ]
    ];
    
    if (isset($data['data']['items'])) $result['items'] = $data['data']['items'];
    
    $result['titlePage'] = $data['data']['titlePage'] ?? '';
    $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://otruyencdn.com/';
    $result['domain'] = rtrim($domain, '/') . '/uploads/comics/';
    $result['seoOnPage'] = $data['data']['seoOnPage'] ?? [];
    
    if (isset($data['data']['params']['pagination'])) {
        $result['pagination'] = $data['data']['params']['pagination'];
    }
    
    // Ensure origin_name is a string to prevent Android app from crashing
    if (!empty($result['items'])) {
        foreach ($result['items'] as &$item) {
            if (isset($item['origin_name']) && is_array($item['origin_name'])) {
                $item['origin_name'] = implode(', ', $item['origin_name']);
            }
        }
    }
    
    return $result;
}

function fetchApiComicDetail($slug) {
    global $settings;
    $baseUrl = rtrim($settings['comicApiUrl'] ?? 'https://otruyenapi.com/v1/api', '/');
    $url = "$baseUrl/truyen-tranh/" . rawurlencode($slug);
    
    $res = fetchApiWithCache($url, 3600);
    if (!$res) return null;
    $data = json_decode($res, true);
    
    $result = [
        'comic' => null,
        'chapters' => [],
        'seoOnPage' => [],
        'domain' => 'https://otruyencdn.com/'
    ];
    
    if (isset($data['data']['item'])) {
        $result['comic'] = $data['data']['item'];
        // Fix missing properties to match Movie structure if needed by theme
        if (!isset($result['comic']['year'])) $result['comic']['year'] = '';
        if (!isset($result['comic']['quality'])) $result['comic']['quality'] = '';
        if (!isset($result['comic']['lang'])) $result['comic']['lang'] = '';
        if (!isset($result['comic']['time'])) $result['comic']['time'] = '';
        
        // Ensure origin_name is a string to prevent Android app from crashing
        if (isset($result['comic']['origin_name']) && is_array($result['comic']['origin_name'])) {
            $result['comic']['origin_name'] = implode(', ', $result['comic']['origin_name']);
        }
        
        $result['chapters'] = $result['comic']['chapters'] ?? [];
        $result['seoOnPage'] = $data['data']['seoOnPage'] ?? [];
        $result['domain'] = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://otruyencdn.com/';
    }
    
    $baseImgUrl = rtrim($result['domain'], '/') . '/uploads/comics/';
    if (!empty($result['comic']['thumb_url']) && !preg_match('/^http/', $result['comic']['thumb_url'])) {
        $result['comic']['thumb_url'] = $baseImgUrl . ltrim($result['comic']['thumb_url'], '/');
    }
    if (!empty($result['comic']['poster_url']) && !preg_match('/^http/', $result['comic']['poster_url'])) {
        $result['comic']['poster_url'] = $baseImgUrl . ltrim($result['comic']['poster_url'], '/');
    }
    
    // Lấy ảnh poster từ seoSchema nếu poster_url không có sẵn trong item
    if (empty($result['comic']['poster_url']) && !empty($result['seoOnPage']['seoSchema']['image'])) {
        $result['comic']['poster_url'] = $result['seoOnPage']['seoSchema']['image'];
    }
    
    return $result;
}

require_once __DIR__ . '/plugins.php';
