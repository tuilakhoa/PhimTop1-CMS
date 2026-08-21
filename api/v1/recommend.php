<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-App-API-Key');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';

// Authenticate user without forcing exit
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (!function_exists('verifyToken')) {
    function verifyToken($token) {
        global $jwtSecret;
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        
        $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $jwtSecret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if (hash_equals($base64UrlSignature, $parts[2])) {
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
            if (isset($payload['exp']) && $payload['exp'] >= time()) {
                return $payload;
            }
        }
        return null;
    }
}

$user = verifyToken($token);

if (!$user) {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }
}
$pdo = getPDO();
$action = $_GET['action'] ?? 'personal';
$limit = (int)($_GET['limit'] ?? 12);

if ($action === 'personal') {
    $settings = getSettings();
    $displayMode = $settings['displayMode'] ?? 'api';
    
    if (!$user) {
        $items = [];
        if ($displayMode === 'api') {
            // Fetch random page from external API to increase index chances
            $randomPage = mt_rand(1, 20); // API typically has many pages, 1-20 is a safe range
            $res = fetchApiFilms('home', '', $randomPage);
            if ($res && !empty($res['items'])) {
                $items = $res['items'];
                shuffle($items);
                $items = array_slice($items, 0, $limit);
            }
        } else {
            // Local DB: Optimized random approach
            $countStmt = $pdo->query("SELECT COUNT(*) FROM movies");
            $totalMovies = (int)$countStmt->fetchColumn();
            
            if ($totalMovies > 0) {
                $offset = mt_rand(0, max(0, $totalMovies - $limit));
                $stmt = $pdo->prepare("SELECT * FROM movies LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                shuffle($items); // Add visual randomness to the fetched slice
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $items, 'is_personalized' => false]);
        exit;
    }

    // Get user history
    $stmt = $pdo->prepare("SELECT movie_slug FROM watch_history WHERE user_email = ? ORDER BY updated_at DESC LIMIT 5");
    $stmt->execute([$user['email']]);
    $historySlugs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($historySlugs)) {
        // No history, return trending/latest
        $items = [];
        if ($displayMode === 'api') {
            $res = fetchApiFilms('home', '', 1);
            if ($res && !empty($res['items'])) {
                $items = array_slice($res['items'], 0, $limit);
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM movies ORDER BY view DESC, updated_at DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode(['status' => 'success', 'data' => $items, 'is_personalized' => false]);
        exit;
    }

    // Find characteristics of watched movies
    $placeholders = str_repeat('?,', count($historySlugs) - 1) . '?';
    $stmt = $pdo->prepare("SELECT type, lang, year FROM movies WHERE slug IN ($placeholders)");
    $stmt->execute($historySlugs);
    $historyMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $types = [];
    $langs = [];
    foreach ($historyMovies as $m) {
        if (!empty($m['type'])) $types[$m['type']] = ($types[$m['type']] ?? 0) + 1;
        if (!empty($m['lang'])) $langs[$m['lang']] = ($langs[$m['lang']] ?? 0) + 1;
    }

    arsort($types);
    arsort($langs);
    
    $topType = !empty($types) ? array_key_first($types) : null;
    $topLang = !empty($langs) ? array_key_first($langs) : null;

    $recommended = [];

    if ($displayMode === 'api') {
        // In API mode, we try to fetch from the top type or just fallback to latest
        $apiType = 'home';
        $apiSlug = '';
        if ($topType === 'series' || $topType === 'hoathinh') {
            $apiType = 'danh-sach';
            $apiSlug = ($topType === 'hoathinh') ? 'hoat-hinh' : 'phim-bo';
        } elseif ($topType === 'single' || $topType === 'phimle') {
            $apiType = 'danh-sach';
            $apiSlug = 'phim-le';
        }
        $res = fetchApiFilms($apiType, $apiSlug, 1);
        if ($res && !empty($res['items'])) {
            $recommended = $res['items'];
            // Filter out already watched
            $recommended = array_filter($recommended, function($item) use ($historySlugs) {
                return !in_array($item['slug'] ?? '', $historySlugs);
            });
            $recommended = array_values($recommended);
        }
    } else {
        $where = "slug NOT IN ($placeholders)";
        $params = $historySlugs;

        if ($topType) {
            $where .= " AND type = ?";
            $params[] = $topType;
        }
        if ($topLang) {
            $where .= " AND lang = ?";
            $params[] = $topLang;
        }

        // Query recommended movies
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE $where ORDER BY view DESC, updated_at DESC LIMIT ?");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k + 1, $v);
        }
        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $recommended = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // If we didn't find enough, backfill
    if (count($recommended) < $limit) {
        $needed = $limit - count($recommended);
        if ($displayMode === 'api') {
            $res = fetchApiFilms('home', '', 2); // get page 2 for backfill
            $backfill = $res['items'] ?? [];
        } else {
            $excludeSlugs = array_merge($historySlugs, array_column($recommended, 'slug'));
            $placeholders2 = str_repeat('?,', count($excludeSlugs) - 1) . '?';
            
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE slug NOT IN ($placeholders2) ORDER BY view DESC LIMIT ?");
            foreach ($excludeSlugs as $k => $v) {
                $stmt->bindValue($k + 1, $v);
            }
            $stmt->bindValue(count($excludeSlugs) + 1, $needed, PDO::PARAM_INT);
            $stmt->execute();
            $backfill = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $recommended = array_merge($recommended, $backfill);
    }

    // Ensure we only return $limit items
    $recommended = array_slice($recommended, 0, $limit);

    echo json_encode(['status' => 'success', 'data' => $recommended, 'is_personalized' => true]);
    exit;
}


http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
