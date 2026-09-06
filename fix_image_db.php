<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();
if ($pdo) {
    // Determine DB type
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'sqlite') {
        // SQLite doesn't support @temp variable like MySQL
        $pdo->exec("UPDATE movies SET 
            thumb_url = (SELECT poster_url FROM movies AS m2 WHERE m2.id = movies.id),
            poster_url = (SELECT thumb_url FROM movies AS m3 WHERE m3.id = movies.id)");
    } else {
        // MySQL
        $pdo->exec("UPDATE movies SET thumb_url = (@temp:=thumb_url), thumb_url = poster_url, poster_url = @temp;");
    }
    echo "<h1>Đã đảo ngược thumb_url và poster_url thành công trong CSDL!</h1>";
    echo "<p>Vui lòng xóa file này sau khi chạy xong để bảo mật.</p>";
} else {
    echo "Lỗi kết nối cơ sở dữ liệu.";
}
