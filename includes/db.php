<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
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
        'tmdbApiKey' => 'b775c363e46a24e8c885479b0131c4d2',
        'googleIndexJson' => '',
        'indexNowKey' => '',
        'slugMovie' => 'phim',
        'slugWatch' => 'xem-phim',
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
        'updateServerUrl' => 'https://update.phimtop1.asia/check'
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
            if (!isset($row['db_version']) || $row['db_version'] < 2) {
                // Update db_version to trigger migrations in updateSettings
                updateSettings(['db_version' => 2]);
                $row['db_version'] = 2;
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
        "ALTER TABLE settings ADD COLUMN tmdbApiKey VARCHAR(255) DEFAULT 'b775c363e46a24e8c885479b0131c4d2'",
        "ALTER TABLE settings ADD COLUMN googleIndexJson TEXT",
        "ALTER TABLE settings ADD COLUMN indexNowKey VARCHAR(255)",
        "ALTER TABLE settings ADD COLUMN slugMovie VARCHAR(50) DEFAULT 'phim'",
        "ALTER TABLE settings ADD COLUMN slugWatch VARCHAR(50) DEFAULT 'xem-phim'",
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
        "ALTER TABLE settings ADD COLUMN updateServerUrl VARCHAR(255) DEFAULT 'https://update.phimtop1.asia/check'"
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
    session_start();
    if (!isset($_SESSION['admin'])) {
        header("Location: /login");
        exit;
    }
}
