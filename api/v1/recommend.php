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
require_once __DIR__ . '/history.php'; // To reuse verifyToken and get user

$pdo = getPDO();
$action = $_GET['action'] ?? 'personal';
$limit = (int)($_GET['limit'] ?? 12);

if ($action === 'personal') {
    if (!$user) {
        // Fallback: Just return trending/latest movies if not logged in
        $stmt = $pdo->prepare("SELECT * FROM movies ORDER BY view DESC, updated_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $items, 'is_personalized' => false]);
        exit;
    }

    // Get user history
    $stmt = $pdo->prepare("SELECT movie_slug FROM watch_history WHERE user_email = ? ORDER BY updated_at DESC LIMIT 5");
    $stmt->execute([$user['email']]);
    $historySlugs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($historySlugs)) {
        // No history, return trending
        $stmt = $pdo->prepare("SELECT * FROM movies ORDER BY view DESC, updated_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    // If we didn't find enough, backfill
    if (count($recommended) < $limit) {
        $needed = $limit - count($recommended);
        $excludeSlugs = array_merge($historySlugs, array_column($recommended, 'slug'));
        $placeholders2 = str_repeat('?,', count($excludeSlugs) - 1) . '?';
        
        $stmt = $pdo->prepare("SELECT * FROM movies WHERE slug NOT IN ($placeholders2) ORDER BY view DESC LIMIT ?");
        foreach ($excludeSlugs as $k => $v) {
            $stmt->bindValue($k + 1, $v);
        }
        $stmt->bindValue(count($excludeSlugs) + 1, $needed, PDO::PARAM_INT);
        $stmt->execute();
        $backfill = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $recommended = array_merge($recommended, $backfill);
    }

    echo json_encode(['status' => 'success', 'data' => $recommended, 'is_personalized' => true]);
    exit;
}


http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
