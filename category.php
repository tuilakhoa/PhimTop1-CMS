<?php
require_once __DIR__ . '/includes/db.php';
checkSetup();

$type = $_GET['type'] ?? '';
$slug = $_GET['slug'] ?? '';
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$settings = getSettings();
$movies = [];
$title = "Danh sách phim";
$domain = 'https://phimimg.com/';
global $pageTitle, $pageDesc;
$siteName = $settings['siteName'] ?? 'PhimTop1';

if (($settings['displayMode'] ?? 'api') === 'crawl') {
    $pdo = getPDO();
    if ($pdo) {
        $limit = 24;
        $offset = ($page - 1) * $limit;
        if ($type) {
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE type = ? ORDER BY updated_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $type, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $title = "Danh sách: " . htmlspecialchars($type);
            $pageTitle = $title . ' - ' . $siteName;
        } else {
            $stmt = $pdo->prepare("SELECT * FROM movies ORDER BY updated_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
        }
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $apiType = in_array($type, ['the-loai', 'quoc-gia']) ? $type : 'the-loai';
    $url = $slug ? "https://phimapi.com/v1/api/$apiType/$slug" : "https://phimapi.com/v1/api/danh-sach/" . ($type ?: 'phim-le');
    
    if (isset($_GET['sort'])) {
        $sortParts = explode('-', $_GET['sort']);
        if (count($sortParts) == 2) {
            $_GET['sort_field'] = $sortParts[0];
            $_GET['sort_type'] = $sortParts[1];
        }
    }
    
    $qs = http_build_query(array_diff_key($_GET, array_flip(['type', 'slug', 'sort'])));
    if ($qs) $url .= "?$qs";
    
    $res = @file_get_contents($url);
    if ($res) {
        $data = json_decode($res, true);
        if (isset($data['data']['titlePage'])) {
            $title = $data['data']['titlePage'];
            $pageTitle = $title . ' - ' . $siteName;
        }
        if (isset($data['data']['items'])) {
            $movies = $data['data']['items'];
            $domain = $data['data']['APP_DOMAIN_CDN_IMAGE'] ?? 'https://phimimg.com/';
        }
        $apiParams = $data['data']['params'] ?? [];
        $seoOnPage = $data['data']['seoOnPage'] ?? [];
        $breadCrumb = $data['data']['breadCrumb'] ?? [];
        if (!empty($seoOnPage['descriptionHead'])) {
            $pageDesc = $seoOnPage['descriptionHead'];
        } else {
            $pageDesc = "Danh sách phim $title tuyển chọn mới nhất tại $siteName.";
        }
        
        if (isset($apiParams['pagination'])) {
            $totalPages = $apiParams['pagination']['totalPages'] ?? 1;
            $currentPage = $apiParams['pagination']['currentPage'] ?? $page;
        }
    }
}


$theme = $settings['theme'] ?? 'dark';
$themeFile = __DIR__ . "/themes/{$theme}/" . basename(__FILE__);
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/" . basename(__FILE__);
}
