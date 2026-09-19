<?php
require_once 'includes/db.php';
$pdo = getPDO();
if (!$pdo) die("Database connection failed.\n");

// Lấy tất cả slug của phim AVDBAPI (sử dụng upload18) và TopXX (sử dụng xhub.network / topxx)
$stmt = $pdo->query("SELECT DISTINCT movie_slug FROM episodes WHERE embed_url LIKE '%upload18.%' OR embed_url LIKE '%xhub.network%'");
$slugs1 = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt2 = $pdo->query("SELECT DISTINCT slug FROM movies WHERE thumb_url LIKE '%upload18.%' OR poster_url LIKE '%upload18.%' OR thumb_url LIKE '%topxx.vip%' OR poster_url LIKE '%topxx.vip%'");
$slugs2 = $stmt2->fetchAll(PDO::FETCH_COLUMN);

$slugs = array_unique(array_merge($slugs1, $slugs2));

if (empty($slugs)) {
    echo "Khong tim thay phim AVDBAPI/TopXX nao trong DB de xoa.\n";
    exit;
}

$count = count($slugs);
echo "Đang xóa $count phim từ nguồn AVDBAPI / TopXX...\n";

$chunkedSlugs = array_chunk($slugs, 100);
foreach ($chunkedSlugs as $chunk) {
    $inQuery = implode(',', array_fill(0, count($chunk), '?'));
    
    // Xóa episodes
    $pdo->prepare("DELETE FROM episodes WHERE movie_slug IN ($inQuery)")->execute($chunk);
    
    // Xóa views
    $pdo->prepare("DELETE FROM movie_views WHERE movie_slug IN ($inQuery)")->execute($chunk);
    
    // Xóa seo metadata
    $pdo->prepare("DELETE FROM seo_metadata WHERE item_id IN ($inQuery) AND type = 'movie'")->execute($chunk);
    
    // Xóa movies
    $pdo->prepare("DELETE FROM movies WHERE slug IN ($inQuery)")->execute($chunk);
}

echo "Xóa thành công $count phim và các dữ liệu liên quan khỏi hệ thống!\n";
