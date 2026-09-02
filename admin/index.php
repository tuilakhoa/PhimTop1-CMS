<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

$settings = getSettings();
$successMsg = '';
$errorMsg = '';

// Handle Settings Update (Shared across seo and settings pages)
if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    $updates = [];
    $updates['displayMode'] = 'api';
    if (isset($_POST['apiSource'])) $updates['apiSource'] = $_POST['apiSource'];
    if (isset($_POST['allowRegistration'])) $updates['allowRegistration'] = (int)$_POST['allowRegistration'];
    if (isset($_POST['enableComics'])) $updates['enableComics'] = (int)$_POST['enableComics'];
    if (isset($_POST['comicApiUrl'])) $updates['comicApiUrl'] = $_POST['comicApiUrl'];
    if (isset($_POST['tmdbApiKey'])) $updates['tmdbApiKey'] = $_POST['tmdbApiKey'];
    if (isset($_POST['siteName'])) $updates['siteName'] = $_POST['siteName'];
    if (isset($_POST['seoTitle'])) $updates['seoTitle'] = $_POST['seoTitle'];
    if (isset($_POST['seoDesc'])) $updates['seoDesc'] = $_POST['seoDesc'];
    if (isset($_POST['seoKeywords'])) $updates['seoKeywords'] = $_POST['seoKeywords'];
    if (isset($_POST['ogTitle'])) $updates['ogTitle'] = $_POST['ogTitle'];
    if (isset($_POST['ogDesc'])) $updates['ogDesc'] = $_POST['ogDesc'];
    if (isset($_POST['ogType'])) $updates['ogType'] = $_POST['ogType'];
    if (isset($_POST['ogLocale'])) $updates['ogLocale'] = $_POST['ogLocale'];
    if (isset($_POST['seoAuthor'])) $updates['seoAuthor'] = $_POST['seoAuthor'];
    if (isset($_POST['seoPublisher'])) $updates['seoPublisher'] = $_POST['seoPublisher'];
    if (isset($_POST['themeColor'])) $updates['themeColor'] = $_POST['themeColor'];
    if (isset($_POST['canonicalBaseUrl'])) $updates['canonicalBaseUrl'] = $_POST['canonicalBaseUrl'];
    if (isset($_POST['verifyGoogle'])) $updates['verifyGoogle'] = $_POST['verifyGoogle'];
    if (isset($_POST['verifyBing'])) $updates['verifyBing'] = $_POST['verifyBing'];
    if (isset($_POST['verifyYandex'])) $updates['verifyYandex'] = $_POST['verifyYandex'];
    if (isset($_POST['customHead'])) $updates['customHead'] = $_POST['customHead'];
    if (isset($_POST['customBody'])) $updates['customBody'] = $_POST['customBody'];
    
    // Security & Analytics
    if (isset($_POST['cfTurnstileKey'])) $updates['cfTurnstileKey'] = $_POST['cfTurnstileKey'];
    if (isset($_POST['cfTurnstileSecret'])) $updates['cfTurnstileSecret'] = $_POST['cfTurnstileSecret'];
    if (isset($_POST['cfAnalyticsToken'])) $updates['cfAnalyticsToken'] = $_POST['cfAnalyticsToken'];
    if (isset($_POST['cfApiToken'])) $updates['cfApiToken'] = $_POST['cfApiToken'];
    if (isset($_POST['cfAccountId'])) $updates['cfAccountId'] = $_POST['cfAccountId'];
    if (isset($_POST['cfZoneId'])) $updates['cfZoneId'] = $_POST['cfZoneId'];
    if (isset($_POST['gaMeasurementId'])) $updates['gaMeasurementId'] = $_POST['gaMeasurementId'];
    if (isset($_POST['gaPropertyId'])) $updates['gaPropertyId'] = trim($_POST['gaPropertyId']);

    // Indexing APIs
    if (isset($_POST['googleIndexJson'])) $updates['googleIndexJson'] = $_POST['googleIndexJson'];
    if (isset($_POST['indexNowKey'])) {
        $updates['indexNowKey'] = $_POST['indexNowKey'];
        // Generate IndexNow key file
        if (!empty($_POST['indexNowKey'])) {
            $key = trim($_POST['indexNowKey']);
            file_put_contents(__DIR__ . "/../{$key}.txt", $key);
        }
    }

    // Footer & Social
    if (isset($_POST['footerText'])) $updates['footerText'] = $_POST['footerText'];
    if (isset($_POST['socialFacebook'])) $updates['socialFacebook'] = $_POST['socialFacebook'];
    if (isset($_POST['socialYoutube'])) $updates['socialYoutube'] = $_POST['socialYoutube'];
    if (isset($_POST['socialTwitter'])) $updates['socialTwitter'] = $_POST['socialTwitter'];
    if (isset($_POST['socialTelegram'])) $updates['socialTelegram'] = $_POST['socialTelegram'];
    
    
    // Router Slugs
    if (isset($_POST['slugMovie'])) $updates['slugMovie'] = $_POST['slugMovie'];
    if (isset($_POST['slugWatch'])) $updates['slugWatch'] = $_POST['slugWatch'];
    if (isset($_POST['slugComic'])) $updates['slugComic'] = $_POST['slugComic'];
    if (isset($_POST['slugRead'])) $updates['slugRead'] = $_POST['slugRead'];
    if (isset($_POST['slugList'])) $updates['slugList'] = $_POST['slugList'];
    if (isset($_POST['slugGenre'])) $updates['slugGenre'] = $_POST['slugGenre'];
    if (isset($_POST['slugCountry'])) $updates['slugCountry'] = $_POST['slugCountry'];
    if (isset($_POST['slugComicList'])) $updates['slugComicList'] = $_POST['slugComicList'];
    
    // Sitemap
    if (isset($_POST['sitemapLimit'])) $updates['sitemapLimit'] = (int)$_POST['sitemapLimit'];
    if (isset($_POST['sitemapIncludeMovies'])) $updates['sitemapIncludeMovies'] = (int)$_POST['sitemapIncludeMovies'];
    if (isset($_POST['sitemapIncludeCategories'])) $updates['sitemapIncludeCategories'] = (int)$_POST['sitemapIncludeCategories'];
    if (isset($_POST['sitemapLinksPerFile'])) $updates['sitemapLinksPerFile'] = (int)$_POST['sitemapLinksPerFile'];
    
    // Google OAuth
    if (isset($_POST['enableGoogleLogin'])) $updates['enableGoogleLogin'] = (int)$_POST['enableGoogleLogin'];
    if (isset($_POST['googleClientId'])) $updates['googleClientId'] = $_POST['googleClientId'];
    if (isset($_POST['googleClientSecret'])) $updates['googleClientSecret'] = $_POST['googleClientSecret'];
    if (isset($_POST['enableMicrosoftLogin'])) $updates['enableMicrosoftLogin'] = (int)$_POST['enableMicrosoftLogin'];
    if (isset($_POST['msClientId'])) $updates['msClientId'] = $_POST['msClientId'];
    if (isset($_POST['msClientSecret'])) $updates['msClientSecret'] = $_POST['msClientSecret'];
    if (isset($_POST['msTenantId'])) $updates['msTenantId'] = $_POST['msTenantId'];
    if (isset($_POST['googleAllowedEmails'])) $updates['googleAllowedEmails'] = $_POST['googleAllowedEmails'];
    
    // AI Integration
    if (isset($_POST['geminiApiKey'])) $updates['geminiApiKey'] = $_POST['geminiApiKey'];
    if (isset($_POST['openaiApiKey'])) $updates['openaiApiKey'] = $_POST['openaiApiKey'];
    if (isset($_POST['aiProvider'])) $updates['aiProvider'] = $_POST['aiProvider'];

    // SMTP Settings
    if (isset($_POST['smtpHost'])) $updates['smtpHost'] = $_POST['smtpHost'];
    if (isset($_POST['smtpPort'])) $updates['smtpPort'] = $_POST['smtpPort'];
    if (isset($_POST['smtpUser'])) $updates['smtpUser'] = $_POST['smtpUser'];
    if (isset($_POST['smtpPass'])) $updates['smtpPass'] = $_POST['smtpPass'];

    // Update Settings
    if (isset($_POST['allowAutoUpdate'])) $updates['allowAutoUpdate'] = (int)$_POST['allowAutoUpdate'];
    if (isset($_POST['enableContinueWatching'])) $updates['enableContinueWatching'] = (int)$_POST['enableContinueWatching'];
    
    // Featured Banner Settings
    if (isset($_POST['featuredType'])) $updates['featuredType'] = $_POST['featuredType'];
    if (isset($_POST['featuredMovieSlug'])) $updates['featuredMovieSlug'] = $_POST['featuredMovieSlug'];
    if (isset($_POST['featuredStyle'])) $updates['featuredStyle'] = $_POST['featuredStyle'];
    if (isset($_POST['featuredCount'])) $updates['featuredCount'] = (int)$_POST['featuredCount'];
    if (isset($_POST['enableWatchingSession'])) $updates['enableWatchingSession'] = (int)$_POST['enableWatchingSession'];
    if (isset($_POST['trackAnonymousSession'])) $updates['trackAnonymousSession'] = (int)$_POST['trackAnonymousSession'];

    // DB Config
    if (isset($_POST['dbType'])) {
        $dbType = $_POST['dbType'];
        $newDbConfig = ['type' => $dbType];
        
        if ($dbType === 'mysql') {
            $newDbConfig['host'] = $_POST['dbHost'] ?? '127.0.0.1';
            $newDbConfig['database'] = $_POST['dbName'] ?? '';
            $newDbConfig['user'] = $_POST['dbUser'] ?? '';
            $newDbConfig['password'] = $_POST['dbPass'] ?? '';
        } else if ($dbType === 'firestore') {
            $newDbConfig['projectId'] = $_POST['projectId'] ?? '';
            $saStr = $_POST['serviceAccount'] ?? '{}';
            $newDbConfig['serviceAccount'] = json_decode($saStr, true);
        }
        saveDbConfig($newDbConfig);
    }
    if (isset($_POST['useLogoAsFavicon'])) {
        $updates['useLogoAsFavicon'] = (int)$_POST['useLogoAsFavicon'];
    } else {
        $updates['useLogoAsFavicon'] = 0;
    }

    $uploadDir = __DIR__ . '/../assets/';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

    $convertToIco = function($sourcePath, $destPath) {
        if (!file_exists($sourcePath)) return false;
        $mime = @mime_content_type($sourcePath);
        
        if (extension_loaded('gd')) {
            $image = null;
            if ($mime === 'image/jpeg') $image = @imagecreatefromjpeg($sourcePath);
            elseif ($mime === 'image/png') $image = @imagecreatefrompng($sourcePath);
            elseif ($mime === 'image/webp') $image = @imagecreatefromwebp($sourcePath);
            elseif ($mime === 'image/gif') $image = @imagecreatefromgif($sourcePath);
            
            if ($image) {
                $w = imagesx($image);
                $h = imagesy($image);
                $size = min(256, max($w, $h));
                $resized = imagecreatetruecolor($size, $size);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefilledrectangle($resized, 0, 0, $size, $size, $transparent);
                
                $ratio = min($size / $w, $size / $h);
                $scaled_w = $w * $ratio;
                $scaled_h = $h * $ratio;
                $dst_x = ($size - $scaled_w) / 2;
                $dst_y = ($size - $scaled_h) / 2;
                
                imagecopyresampled($resized, $image, $dst_x, $dst_y, 0, 0, $scaled_w, $scaled_h, $w, $h);
                
                ob_start();
                imagepng($resized);
                $pngData = ob_get_clean();
                imagedestroy($image);
                imagedestroy($resized);
                
                $icoHeader = pack("vvv", 0, 1, 1);
                $icoDir = pack("CCCCvvVV", 0, 0, 0, 0, 1, 32, strlen($pngData), 22);
                file_put_contents($destPath, $icoHeader . $icoDir . $pngData);
                return true;
            }
        }
        
        if ($mime === 'image/png') {
            $pngData = file_get_contents($sourcePath);
            if (substr($pngData, 0, 8) === "\x89PNG\x0d\x0a\x1a\x0a") {
                $icoHeader = pack("vvv", 0, 1, 1);
                $icoDir = pack("CCCCvvVV", 0, 0, 0, 0, 1, 32, strlen($pngData), 22);
                file_put_contents($destPath, $icoHeader . $icoDir . $pngData);
                return true;
            }
        }
        
        return copy($sourcePath, $destPath);
    };

    // Handle Logo Upload
    $logoUploadedPath = null;
    if (isset($_FILES['logoFile']) && $_FILES['logoFile']['error'] === UPLOAD_ERR_OK) {
        $fileName = 'logo_' . time() . '.' . pathinfo($_FILES['logoFile']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['logoFile']['tmp_name'], $targetPath)) {
            $updates['logoUrl'] = '/assets/' . $fileName;
            $logoUploadedPath = $targetPath;
        }
    }
    
    // Handle Favicon Upload
    if (isset($_FILES['faviconFile']) && $_FILES['faviconFile']['error'] === UPLOAD_ERR_OK) {
        $fileName = 'favicon_' . time() . '.ico';
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['faviconFile']['tmp_name'], $targetPath . '.tmp')) {
            $convertToIco($targetPath . '.tmp', $targetPath);
            @unlink($targetPath . '.tmp');
            $updates['faviconUrl'] = '/assets/' . $fileName;
            $updates['useLogoAsFavicon'] = 0; // Turn off auto logo conversion if user manually uploads a favicon
        }
    } elseif (!empty($updates['useLogoAsFavicon'])) {
        $sourceLogo = $logoUploadedPath;
        if (!$sourceLogo && !empty($settings['logoUrl'])) {
            $sourceLogo = __DIR__ . '/..' . $settings['logoUrl'];
        }
        
        if ($sourceLogo && file_exists($sourceLogo)) {
            $fileName = 'favicon_logo_' . time() . '.ico';
            $targetPath = $uploadDir . $fileName;
            if ($convertToIco($sourceLogo, $targetPath)) {
                $updates['faviconUrl'] = '/assets/' . $fileName;
            }
        }
    }
    
    // Handle Apple Touch Icon Upload
    if (isset($_FILES['appleTouchIconFile']) && $_FILES['appleTouchIconFile']['error'] === UPLOAD_ERR_OK) {
        $fileName = 'apple_icon_' . time() . '.' . pathinfo($_FILES['appleTouchIconFile']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['appleTouchIconFile']['tmp_name'], $targetPath)) {
            $updates['appleTouchIconUrl'] = '/assets/' . $fileName;
        }
    }
    
    // Handle OG Image Upload
    if (isset($_FILES['ogImageFile']) && $_FILES['ogImageFile']['error'] === UPLOAD_ERR_OK) {
        $fileName = 'og_' . time() . '.' . pathinfo($_FILES['ogImageFile']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['ogImageFile']['tmp_name'], $targetPath)) {
            $updates['ogImageUrl'] = '/assets/' . $fileName;
        }
    }
    
    updateSettings($updates);
    $settings = getSettings();
    
    // Update .htaccess for SEO Slugs
    $htaccessPath = __DIR__ . '/../.htaccess';
    if (file_exists($htaccessPath)) {
        $htaccessContent = file_get_contents($htaccessPath);
        
        $slugMovie = $settings['slugMovie'] ?? 'phim';
        $slugWatch = $settings['slugWatch'] ?? 'xem-phim';
        $slugComic = $settings['slugComic'] ?? 'truyen';
        $slugRead = $settings['slugRead'] ?? 'doc-truyen';
        $slugComicList = $settings['slugComicList'] ?? 'danh-sach-truyen';
        $slugList = $settings['slugList'] ?? 'danh-sach';
        $slugGenre = $settings['slugGenre'] ?? 'the-loai';
        $slugCountry = $settings['slugCountry'] ?? 'quoc-gia';
        
        $seoBlock = "# SEO URL Rewrites\n";
        $seoBlock .= "    RewriteRule ^{$slugMovie}/([^/]+)/?$ movie.php?slug=$1 [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^{$slugWatch}/([^/]+)/([^/]+)/?$ watch.php?slug=$1&ep=$2 [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^{$slugComicList}/?$ danh-sach-truyen.php [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^{$slugComic}/([^/]+)/?$ comic.php?slug=$1 [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^{$slugRead}/([^/]+)/([^/]+)/?$ read.php?slug=$1&chap=$2 [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^{$slugList}/([^/]+)/?$ category.php?type=$1 [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^{$slugGenre}/([^/]+)/?$ category.php?slug=$1&type=the-loai [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^{$slugCountry}/([^/]+)/?$ category.php?slug=$1&type=quoc-gia [QSA,L]\n";
        $seoBlock .= "    RewriteRule ^tim-kiem/?$ search.php [QSA,L]\n";
        
        // Replace the block dynamically using regex
        $newHtaccess = preg_replace('/# SEO URL Rewrites.*?(?=(<\/IfModule>|<\/FilesMatch>|$))/is', str_replace(['\\', '$'], ['\\\\', '\$'], $seoBlock), $htaccessContent);
        if ($newHtaccess && $newHtaccess !== $htaccessContent) {
            file_put_contents($htaccessPath, $newHtaccess);
        }
    }
    
    $successMsg = "Đã cập nhật cài đặt thành công!";
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    session_destroy();
    header("Location: /admin_login.php");
    exit;
}

// Handle Feedback Actions
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'resolve_feedback') {
        $id = (int)$_POST['id'];
        $pdo = getPDO();
        if ($pdo) {
            $stmt = $pdo->prepare("UPDATE user_feedbacks SET status = 'resolved' WHERE id = ?");
            $stmt->execute([$id]);
            $successMsg = "Đã đánh dấu phản hồi là Đã xử lý.";
        }
    } else if ($_POST['action'] === 'delete_feedback') {
        $id = (int)$_POST['id'];
        $pdo = getPDO();
        if ($pdo) {
            $stmt = $pdo->prepare("DELETE FROM user_feedbacks WHERE id = ?");
            $stmt->execute([$id]);
            $successMsg = "Đã xóa phản hồi thành công.";
        }
    }
}

$currentPage = $_GET['page'] ?? 'dashboard';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 min-h-0 h-full overflow-y-auto p-4 md:p-8 custom-scrollbar relative z-10">
    
    <?php if ($successMsg): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-lg flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> <?= htmlspecialchars($successMsg) ?>
        </div>
    <?php endif; ?>

    <?php
    $pageFile = __DIR__ . "/pages/{$currentPage}.php";
    $pageFile = apply_filters('admin_page_file', $pageFile, $currentPage);
    
    if (file_exists($pageFile)) {
        include $pageFile;
    } else {
        echo "<h2 class='text-xl text-red-500'>Không tìm thấy trang yêu cầu!</h2>";
    }
    ?>
</div>

</div> <!-- Close wrapper from header.php -->

<script>
    lucide.createIcons();
    
    // Mobile Sidebar Toggle Logic
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const adminSidebar = document.getElementById('admin-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (mobileMenuBtn && adminSidebar && sidebarOverlay) {
        function toggleSidebar() {
            adminSidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        }

        mobileMenuBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }
</script>
<?php do_action('admin_footer'); ?>
</body>
</html>
