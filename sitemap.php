<?php
require_once __DIR__ . '/includes/db.php';
$settings = getSettings();

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// Dynamic slugs
$slugMovie = $settings['slugMovie'] ?? 'phim';
$slugList = $settings['slugList'] ?? 'danh-sach';
$slugGenre = $settings['slugGenre'] ?? 'the-loai';
$slugCountry = $settings['slugCountry'] ?? 'quoc-gia';

// Sitemap Settings
$sitemapLimit = (int)($settings['sitemapLimit'] ?? 5000);
$includeMovies = ($settings['sitemapIncludeMovies'] ?? 1) == 1;
$includeCategories = ($settings['sitemapIncludeCategories'] ?? 1) == 1;
$linksPerFile = (int)($settings['sitemapLinksPerFile'] ?? 1000);
if ($linksPerFile <= 0) $linksPerFile = 1000;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;

// Helpers to output URL
function outUrl($loc, $lastmod, $changefreq, $priority) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
    if ($lastmod) echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

require_once __DIR__ . '/includes/repositories.php';
$catRepo = getCategoryRepository();
$movieRepo = getMovieRepository();

$allCats = $includeCategories ? $catRepo->getCategories() : [];
$catCount = count($allCats);

$allMoviesResult = $includeMovies ? $movieRepo->getMovies(1, 999999, '') : ['total' => 0, 'items' => []]; // get all for now to count, we can limit later
$allMovies = $allMoviesResult['items'];
$movieCount = count($allMovies);
if ($movieCount > $sitemapLimit) {
    $movieCount = $sitemapLimit;
}

// 1. Sitemap Index Mode (No page specified)
if ($page === 0) {
    header("Content-Type: application/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Total links calculation
    $totalLinks = 1; // 1 for homepage
    $totalLinks += $catCount;
    $totalLinks += $movieCount;
    
    $totalPages = ceil($totalLinks / $linksPerFile);
    if ($totalPages == 0) $totalPages = 1;
    
    for ($i = 1; $i <= $totalPages; $i++) {
        echo "  <sitemap>\n";
        echo "    <loc>{$baseUrl}/sitemap-{$i}.xml</loc>\n";
        echo "    <lastmod>" . date('c') . "</lastmod>\n";
        echo "  </sitemap>\n";
    }
    
    echo '</sitemapindex>';
    exit;
}

// 2. Sitemap Detail Mode (Page specified)
header("Content-Type: application/xml; charset=utf-8");
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$offset = ($page - 1) * $linksPerFile;
$currentCount = 0;
$itemsToFetch = $linksPerFile;

// Home page is always the very first item (Index 0)
if ($offset === 0) {
    outUrl("{$baseUrl}/", date('c'), "daily", "1.0");
    $currentCount++;
    $itemsToFetch--;
}

$globalIndex = 1; // Index 0 was home page

// Categories
if ($includeCategories && $itemsToFetch > 0) {
    if ($offset < $globalIndex + $catCount) {
        $catOffset = max(0, $offset - $globalIndex);
        $catLimit = min($itemsToFetch, $catCount - $catOffset);
        
        $catsToProcess = array_slice($allCats, $catOffset, $catLimit);
        foreach ($catsToProcess as $row) {
            $prefix = ($row['type'] ?? '') === 'genre' ? $slugGenre : $slugCountry;
            outUrl("{$baseUrl}/{$prefix}/" . ($row['slug'] ?? ''), null, "weekly", "0.6");
            $currentCount++;
            $itemsToFetch--;
        }
    }
    $globalIndex += $catCount;
}

// Movies
if ($includeMovies && $itemsToFetch > 0) {
    if ($offset < $globalIndex + $movieCount) {
        $movieOffset = max(0, $offset - $globalIndex);
        $movieLimit = min($itemsToFetch, $movieCount - $movieOffset);
        
        if ($movieLimit > 0) {
            $moviesToProcess = array_slice($allMovies, $movieOffset, $movieLimit);
            foreach ($moviesToProcess as $row) {
                $date = isset($row['updated_at']) ? date('c', strtotime($row['updated_at'])) : date('c');
                outUrl("{$baseUrl}/{$slugMovie}/" . ($row['slug'] ?? ''), $date, "weekly", "0.8");
                $currentCount++;
                $itemsToFetch--;
            }
        }
    }
}

echo '</urlset>';
