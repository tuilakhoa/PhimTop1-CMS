<?php
require_once __DIR__ . '/logger.php';
set_error_handler("custom_error_handler");
set_exception_handler("custom_exception_handler");

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
    static $cachedConfig = null;
    if ($cachedConfig !== null) {
        return $cachedConfig;
    }
    global $dbConfigPath;
    if (file_exists($dbConfigPath)) {
        $cachedConfig = json_decode(file_get_contents($dbConfigPath), true);
        return $cachedConfig;
    }
    return null;
}

function saveDbConfig($newConfig) {
    global $dbConfigPath;
    file_put_contents($dbConfigPath, json_encode($newConfig, JSON_PRETTY_PRINT));
}

function getPDO() {
    static $pdoInstance = null;
    if ($pdoInstance !== null) {
        return $pdoInstance;
    }
    
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
        $pdoInstance = $pdo;
        return $pdoInstance;
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

function getSettings($forceRefresh = false) {
    static $cachedSettings = null;
    if (!$forceRefresh && $cachedSettings !== null) {
        return $cachedSettings;
    }
    
    $pdo = getPDO();
    $defaultSettings = [
        'initialized' => false,
        'id' => 1,
        'adminPath' => '/admin',
        'displayMode' => 'api',
        'apiSource' => 'kkphim',
        'theme' => 'phimhayok',
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
        'ogTitle' => '',
        'ogDesc' => '',
        'ogType' => 'website',
        'ogLocale' => 'vi_VN',
        'seoAuthor' => '',
        'seoPublisher' => '',
        'themeColor' => '#000000',
        'canonicalBaseUrl' => '',
        'logoUrl' => '/assets/images/logo.png',
        'useLogoAsFavicon' => 1,
        'smtpHost' => '',
        'smtpPort' => '587',
        'smtpUser' => '',
        'smtpPass' => '',
        'faviconUrl' => '',
        'appleTouchIconUrl' => '',
        'ogImageUrl' => '',
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
        'enableGoogleLogin' => 0,
        'googleClientId' => '',
        'googleClientSecret' => '',
        'googleAllowedEmails' => '',
        'enableMicrosoftLogin' => 0,
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
        'appDownloadUrlWindows' => '',
        'appDownloadUrlLinux' => '',
        'appDownloadUrlFedora' => '',
        'appDownloadUrlUbuntu' => '',
        'appInAppUpdateUrl' => '',
        'appDownloadUrlTv' => '',
        'appSchemaEnabled' => 0,
        'appSchemaName' => '',
        'appSchemaOs' => 'Android, iOS',
        'appSchemaCategory' => 'EntertainmentApplication',
        'appSchemaPrice' => '0',
        'appSchemaCurrency' => 'VND',
        'appSchemaRatingValue' => '4.8',
        'appSchemaRatingCount' => '1250',
        'appLatestVersion' => '1.0.0',
        'appBuildNumber' => 1,
        'appForceUpdate' => 0,
        'appLatestVersionIos' => '1.0.0',
        'appBuildNumberIos' => 1,
        'appForceUpdateIos' => 0,
        'appDownloadUrlIos' => '',
        'appUpdateMessage' => 'Đã có phiên bản mới, vui lòng cập nhật!',
        'featuredType' => 'latest',
        'featuredMovieSlug' => '',
        'featuredStyle' => 'slider',
        'featuredCount' => 5,
        'enableContinueWatching' => 1,
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
            if (!isset($row['db_version']) || $row['db_version'] < 30) {
                // Update db_version to trigger migrations in updateSettings
                updateSettings(['db_version' => 30]);
                $row['db_version'] = 30;
            }
            
            $cachedSettings = array_merge($defaultSettings, $row);
            return $cachedSettings;
        }
    } catch (PDOException $e) {
        // Handle case where table doesn't exist
    }
    $cachedSettings = $defaultSettings;
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
    require_once __DIR__ . '/migrate.php';
    runMigrations();

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
    try {
        $stmt->execute($values);
    } catch (PDOException $e) {
        Logger::error("Failed to update settings: " . $e->getMessage());
    }
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
        header("Location: /admin_login.php");
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

require_once __DIR__ . '/api_client.php';

require_once __DIR__ . '/plugins.php';
