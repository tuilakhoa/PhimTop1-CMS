<?php
$msgLower = 'tìm phim doraemon';
$category = null;
$country = null;
$stopWords = [
    'nào hay không', 'tìm cho tôi', 'có phim nào', 'diễn viên', 'đạo diễn', 'thể loại', 
    'nào không', 'tìm giúp', 'tên là', 'có phim', 'phim bộ', 'phim lẻ', 'chiếu rạp', 
    'dài tập', 'nhiều tập', 'hoạt hình', 'điểm cao', 'hay nhất', 'đỉnh nhất', 'mới nhất', 
    'gần đây', 'năm nay', 'những', 'muốn', 'xem', 'tìm', 'phim', 'nào', 'hay', 'không', 
    'cho', 'tôi', 'giúp', 'của', 'về', 'đóng', 'có', 'là', 'năm', 'top', 'điểm'
];
if ($category) $stopWords[] = mb_strtolower($category, 'UTF-8');
if ($country) $stopWords[] = mb_strtolower($country, 'UTF-8');
usort($stopWords, function($a, $b) { return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8'); });

$keyword = $msgLower;
foreach ($stopWords as $sw) {
    $keyword = preg_replace('/\b' . preg_quote($sw, '/') . '\b/iu', ' ', $keyword);
}
$keyword = trim(preg_replace('/\s+/', ' ', $keyword));
$searchKeyword = mb_strlen($keyword, 'UTF-8') > 1 ? $keyword : null;

echo "searchKeyword: '$searchKeyword'\n";
