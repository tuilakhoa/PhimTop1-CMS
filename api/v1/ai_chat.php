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

// 1. Extract Year
if (preg_match('/năm\s*(\d{4})/i', $msgLower, $matches)) {
    $year = $matches[1];
} else if (preg_match('/\b(19\d{2}|20\d{2})\b/', $msgLower, $matches)) {
    $year = $matches[1];
}

// 2. Extract Category
$genres = ['hành động', 'tình cảm', 'hài hước', 'cổ trang', 'tâm lý', 'hình sự', 'chiến tranh', 'thể thao', 'võ thuật', 'viễn tưởng', 'phiêu lưu', 'khoa học', 'kinh dị', 'âm nhạc', 'thần thoại', 'tài liệu', 'gia đình', 'chính kịch', 'bí ẩn', 'học đường', 'kinh điển', 'thanh xuân', 'trinh thám', 'hoạt hình'];
foreach ($genres as $g) {
    if (mb_strpos($msgLower, $g, 0, 'UTF-8') !== false) {
        $category = $g;
        break;
    }
}

// 3. Extract Country
$countries = ['hàn quốc', 'trung quốc', 'thái lan', 'việt nam', 'âu mỹ', 'mỹ', 'đài loan', 'hồng kông', 'nhật bản', 'nhật', 'ấn độ'];
foreach ($countries as $c) {
    if (mb_strpos($msgLower, $c, 0, 'UTF-8') !== false) {
        $country = $c;
        if ($country === 'mỹ') $country = 'âu mỹ';
        if ($country === 'nhật') $country = 'nhật bản';
        break;
    }
}

// 4. Extract Type
if (preg_match('/(phim bộ|dài tập|nhiều tập)/i', $msgLower)) {
    $type = 'series';
} else if (preg_match('/(phim lẻ|chiếu rạp)/i', $msgLower)) {
    $type = 'single';
} else if (mb_strpos($msgLower, 'hoạt hình', 0, 'UTF-8') !== false) {
    $type = 'hoathinh';
}

// 5. Sorting Intent
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

// Xóa các stop words để lấy keyword chính
$stopWords = ['có phim', 'trinh thám', 'hành động', 'tình cảm', 'nào hay không', 'nào không', 'tìm cho tôi', 'tìm giúp', 'thể loại', 'phim', 'tên là', 'của', 'đóng', 'diễn viên', 'năm', 'đạo diễn', 'điểm', 'cao', 'hay', 'nhất', 'mới'];
$keyword = $msgLower;
foreach ($stopWords as $sw) {
    $keyword = preg_replace('/\b' . preg_quote($sw, '/') . '\b/i', '', $keyword);
}
$keyword = trim(preg_replace('/\s+/', ' ', $keyword));

// Nếu có category mà user hỏi "có phim trinh thám nào hay không" thì keyword sẽ rỗng
// Nếu keyword dài hơn 2 ký tự, ta sẽ dùng keyword để search fulltext
$searchKeyword = mb_strlen($keyword, 'UTF-8') > 1 ? $keyword : null;

$query = "SELECT * FROM movies WHERE 1=1";
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

if ($searchKeyword) {
    // Search in multiple fields
    $query .= " AND (name LIKE ? OR origin_name LIKE ? OR content LIKE ? OR actors_json LIKE ?)";
    $params[] = '%' . $searchKeyword . '%';
    $params[] = '%' . $searchKeyword . '%';
    $params[] = '%' . $searchKeyword . '%';
    $params[] = '%' . $searchKeyword . '%';
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
    $isApp = isset($_GET['is_app']) && $_GET['is_app'] == 1;

    if (count($results) > 0) {
        $reply = $isApp ? "Dạ, em tìm thấy " . count($results) . " phim " : "<p>Dạ, em tìm thấy <b>" . count($results) . " phim</b> ";
        if ($category) $reply .= mb_convert_case($category, MB_CASE_TITLE, "UTF-8") . " ";
        if ($country) $reply .= "của " . mb_convert_case($country, MB_CASE_TITLE, "UTF-8") . " ";
        if ($year) $reply .= "năm $year ";
        if ($isTopRated) $reply .= "có điểm đánh giá cao ";
        if ($isNewest) $reply .= "mới nhất ";
        if ($searchKeyword) {
            $reply .= $isApp ? "liên quan tới \"$searchKeyword\" " : "liên quan tới \"<b>$searchKeyword</b>\" ";
        }
        $reply .= $isApp ? "cho anh/chị đây ạ:\n\n" : "cho anh/chị đây ạ:</p>";
        
        if (!$isApp) {
            $reply .= "<div style='display:flex; flex-direction:column; gap:10px; margin-top:10px;'>";
        }
        
        foreach ($results as $m) {
            $actors = isset($m['actors_json']) ? implode(", ", json_decode($m['actors_json'], true) ?? []) : 'Đang cập nhật';
            if(empty($actors)) $actors = 'Đang cập nhật';
            $vote = !empty($m['tmdb_vote']) ? $m['tmdb_vote'] : (!empty($m['imdb_vote']) ? $m['imdb_vote'] : 'N/A');
            
            $content = !empty($m['content']) ? mb_strimwidth(strip_tags($m['content']), 0, 150, "...") : 'Đang cập nhật';
            
            if ($isApp) {
                // Flutter App sẽ render text (Và Movie Cards sẽ dc parse từ list $movies)
                // Text ngắn gọn để ko bị rối UI chat
                $reply .= "• {$m['name']} ({$m['year']})\n  ⭐ $vote | 🎭 Diễn viên: $actors\n\n";
            } else {
                // Web hiển thị HTML Card xịn xò
                $reply .= "<div style='background:#111; padding:10px; border-radius:8px; border:1px solid #333;'>";
                $reply .= "<h4 style='margin:0; color:#06b6d4;'><a href='/phim/{$m['slug']}' target='_blank' style='color:#06b6d4; text-decoration:none;'>{$m['name']} ({$m['year']})</a></h4>";
                $reply .= "<p style='margin:5px 0 0 0; font-size:12px; color:#aaa;'>⭐ <b>$vote</b> | 🎭 <b>Diễn viên:</b> $actors</p>";
                $reply .= "<p style='margin:5px 0 0 0; font-size:12px; color:#888;'>$content</p>";
                $reply .= "<div style='margin-top:8px;'><a href='/phim/{$m['slug']}' target='_blank' style='display:inline-block; padding:4px 12px; background:#06b6d4; color:#fff; border-radius:4px; font-size:12px; text-decoration:none;'>▶ Xem phim ngay</a></div>";
                $reply .= "</div>";
            }
        }
        if (!$isApp) {
            $reply .= "</div>";
        }
    } else {
        $reply = "Xin lỗi, hiện tại hệ thống chưa tìm thấy dữ liệu nào khớp với yêu cầu của anh/chị. Anh/chị thử đổi từ khóa khác xem sao nhé!";
    }

    echo json_encode([
        'status' => 'success',
        'reply' => $reply,
        'movies' => $isApp ? $results : [] // App dùng results để render horizontal list, Web ko cần
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
