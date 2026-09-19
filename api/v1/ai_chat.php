<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/db.php';

$message = $_GET['q'] ?? ($_POST['q'] ?? '');

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập câu hỏi.']);
    exit;
}

$msgLower = mb_strtolower($message, 'UTF-8');

$year = null;
$category = null;
$country = null;
$type = null;
$isTopRated = false;
$isNewest = false;

if (preg_match('/năm\s*(\d{4})/i', $msgLower, $matches)) {
    $year = $matches[1];
} else if (preg_match('/\b(19\d{2}|20\d{2})\b/', $msgLower, $matches)) {
    $year = $matches[1];
}

$genres = ['hành động', 'tình cảm', 'hài hước', 'cổ trang', 'tâm lý', 'hình sự', 'chiến tranh', 'thể thao', 'võ thuật', 'viễn tưởng', 'phiêu lưu', 'khoa học', 'kinh dị', 'âm nhạc', 'thần thoại', 'tài liệu', 'gia đình', 'chính kịch', 'bí ẩn', 'học đường', 'kinh điển', 'thanh xuân', 'trinh thám', 'hoạt hình'];
foreach ($genres as $g) {
    if (mb_strpos($msgLower, $g, 0, 'UTF-8') !== false) {
        $category = $g;
        break;
    }
}

$countries = ['hàn quốc', 'trung quốc', 'thái lan', 'việt nam', 'âu mỹ', 'mỹ', 'đài loan', 'hồng kông', 'nhật bản', 'nhật', 'ấn độ'];
foreach ($countries as $c) {
    if (mb_strpos($msgLower, $c, 0, 'UTF-8') !== false) {
        $country = $c;
        if ($country === 'mỹ') $country = 'âu mỹ';
        if ($country === 'nhật') $country = 'nhật bản';
        break;
    }
}

if (preg_match('/(phim bộ|dài tập|nhiều tập)/i', $msgLower)) {
    $type = 'series';
} else if (preg_match('/(phim lẻ|chiếu rạp)/i', $msgLower)) {
    $type = 'single';
} else if (mb_strpos($msgLower, 'hoạt hình', 0, 'UTF-8') !== false) {
    $type = 'hoathinh';
}

if (preg_match('/(điểm cao|hay nhất|đỉnh nhất|top)/i', $msgLower)) {
    $isTopRated = true;
}
if (preg_match('/(mới nhất|gần đây|năm nay)/i', $msgLower)) {
    $isNewest = true;
}

$pdo = getPDO();
if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
    exit;
}

$query = "SELECT name, slug, year, origin_name, type, thumb_url, tmdb_vote FROM movies WHERE 1=1";
$params = [];

if ($year) {
    $query .= " AND year = ?";
    $params[] = $year;
}
if ($category) {
    $query .= " AND categories_json LIKE ?";
    $params[] = '%' . $category . '%';
}
if ($country) {
    $query .= " AND countries_json LIKE ?";
    $params[] = '%' . $country . '%';
}
if ($type) {
    $query .= " AND type = ?";
    $params[] = $type;
}

if (!$year && !$category && !$country && !$type) {
    $keyword = trim(str_replace(['tìm cho tôi', 'phim', 'có phim nào', 'tên là'], '', $msgLower));
    if (!empty($keyword)) {
        $query .= " AND (name LIKE ? OR origin_name LIKE ?)";
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
    }
}

if ($isTopRated) {
    $query .= " ORDER BY CAST(tmdb_vote AS DECIMAL(10,1)) DESC, updated_at DESC";
} else {
    $query .= " ORDER BY updated_at DESC";
}
$query .= " LIMIT 10";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $reply = "";
    if (count($results) > 0) {
        $reply = "Dạ, em tìm thấy " . count($results) . " phim ";
        if ($category) $reply .= mb_convert_case($category, MB_CASE_TITLE, "UTF-8") . " ";
        if ($country) $reply .= "của " . mb_convert_case($country, MB_CASE_TITLE, "UTF-8") . " ";
        if ($year) $reply .= "năm $year ";
        if ($isTopRated) $reply .= "có điểm đánh giá cao ";
        if ($isNewest) $reply .= "mới nhất ";
        $reply .= "phù hợp với yêu cầu của anh/chị đây ạ:";
    } else {
        $reply = "Xin lỗi, hiện tại em chưa tìm thấy phim nào khớp với yêu cầu của anh/chị. Anh/chị thử đổi từ khóa hoặc thể loại khác xem sao nhé!";
    }

    echo json_encode([
        'status' => 'success',
        'reply' => trim($reply),
        'movies' => $results
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
