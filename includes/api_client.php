<?php
// API Fetch Helper
require_once __DIR__ . '/cache_manager.php';

function fetchApiFilms($type, $slug = '', $page = 1, $keyword = '', $category = '', $country = '', $year = '', $sort = '') {
    return fetchLocalFilms($type, $slug, $page, $keyword, $category, $country, $year, $sort);
}

function fetchApiMovieDetail($slug) {
    return fetchLocalMovieDetail($slug);
}

function getYearsList() {
    $pdo = getPDO();
    if (!$pdo) return [];
    $stmt = $pdo->query("SELECT DISTINCT year FROM movies WHERE year > 0 ORDER BY year DESC");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $result = [];
    foreach ($years as $y) {
        $result[] = ['name' => (string)$y, 'slug' => (string)$y];
    }
    return $result;
}

function fetchLocalFilms($type, $slug = '', $page = 1, $keyword = '', $category = '', $country = '', $year = '', $sort = '') {
    $pdo = getPDO();
    if (!$pdo) return null;
    $limit = 24;
    $offset = ($page - 1) * $limit;
    
    $where = ["1=1"];
    $params = [];
    $join = "";
    
    if ($keyword) {
        $join = "LEFT JOIN seo_metadata sm ON m.slug = sm.item_id AND sm.type = 'movie'";
        $where[] = "(m.name LIKE ? OR m.origin_name LIKE ? OR m.slug LIKE ? OR sm.seo_keywords LIKE ?)";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
        $params[] = "%$keyword%";
    }
    
    if ($type === 'danh-sach' && $slug) {
        if ($slug === 'phim-le') $where[] = "m.type = 'single'";
        else if ($slug === 'phim-bo') $where[] = "m.type = 'series'";
        else if ($slug === 'hoat-hinh') $where[] = "m.type = 'hoathinh'";
        else if ($slug === 'tv-shows') $where[] = "m.type = 'tvshows'";
    } else if ($type === 'the-loai' && $slug) {
        $where[] = "m.categories_json LIKE ?";
        $params[] = '%"slug":"' . $slug . '"%';
    } else if ($type === 'quoc-gia' && $slug) {
        $where[] = "m.countries_json LIKE ?";
        $params[] = '%"slug":"' . $slug . '"%';
    }
    
    if ($year) {
        $where[] = "m.year = ?";
        $params[] = $year;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $countSql = "SELECT COUNT(DISTINCT m.id) FROM movies m $join WHERE $whereClause";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);
    
    $sql = "SELECT DISTINCT m.* FROM movies m $join WHERE $whereClause ORDER BY m.updated_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as &$item) {
        if (!empty($item['categories_json'])) {
            $item['category'] = json_decode($item['categories_json'], true);
        } else {
            $item['category'] = [];
        }
        if (!empty($item['countries_json'])) {
            $item['country'] = json_decode($item['countries_json'], true);
        } else {
            $item['country'] = [];
        }
    }
    
    return [
        'items' => $items,
        'titlePage' => 'Danh Sách Phim',
        'domain' => '',
        'seoOnPage' => [],
        'params' => [],
        'pagination' => [
            'totalPages' => $totalPages,
            'currentPage' => $page
        ]
    ];
}

function fetchLocalMovieDetail($slug) {
    $pdo = getPDO();
    if (!$pdo) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE slug = ?");
    $stmt->execute([$slug]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$movie) return null;
    
    if (!empty($movie['categories_json'])) {
        $movie['category'] = json_decode($movie['categories_json'], true);
    } else {
        $movie['category'] = [];
    }
    
    if (!empty($movie['countries_json'])) {
        $movie['country'] = json_decode($movie['countries_json'], true);
    } else {
        $movie['country'] = [];
    }
    
    $stmtEp = $pdo->prepare("SELECT * FROM episodes WHERE movie_slug = ? ORDER BY id ASC");
    $stmtEp->execute([$slug]);
    $eps = $stmtEp->fetchAll(PDO::FETCH_ASSOC);
    
    $episodes = [];
    foreach ($eps as $ep) {
        $serverName = $ep['server_name'];
        if (!isset($episodes[$serverName])) {
            $episodes[$serverName] = [
                'server_name' => $serverName,
                'server_data' => []
            ];
        }
        $episodes[$serverName]['server_data'][] = [
            'name' => $ep['name'],
            'slug' => $ep['slug'],
            'filename' => $ep['filename'],
            'link_embed' => $ep['embed_url'],
            'link_m3u8' => $ep['m3u8_url']
        ];
    }
    
    // Fetch SEO metadata for keywords
    $stmtSeo = $pdo->prepare("SELECT seo_keywords FROM seo_metadata WHERE item_id = ? AND type = 'movie'");
    $stmtSeo->execute([$slug]);
    $seoRow = $stmtSeo->fetch(PDO::FETCH_ASSOC);
    if ($seoRow && !empty($seoRow['seo_keywords'])) {
        $movie['seo_keywords'] = $seoRow['seo_keywords'];
    }
    
    return [
        'movie' => $movie,
        'episodes' => array_values($episodes),
        'seoOnPage' => [],
        'domain' => ''
    ];
}
