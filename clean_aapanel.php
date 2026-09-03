<?php
// Script dọn dẹp file rác và source code App không cần thiết trên Web Server (aaPanel / cPanel)
session_start();

$itemsToDelete = [
    'phimtop1_flutter', // Toàn bộ mã nguồn Flutter App (Android/iOS/Windows/Linux)
    'clean_movies.php', // Script dọn dẹp DB sau khi chạy xong
    'README.md',
    'GEMINI.md',
    '.git',
    '.gitignore',
    '.gitattributes'
];

echo "<h1>Dọn dẹp hệ thống aaPanel / cPanel</h1>";

$deletedCount = 0;

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) {
        return false;
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            @unlink($file);
        }
    }
    return @rmdir($dirPath);
}

foreach ($itemsToDelete as $item) {
    $path = __DIR__ . '/' . $item;
    if (file_exists($path)) {
        if (is_dir($path)) {
            if (deleteDir($path)) {
                echo "<p style='color: green;'>Đã xoá thư mục: $item</p>";
                $deletedCount++;
            } else {
                echo "<p style='color: red;'>Lỗi khi xoá thư mục: $item (Kiểm tra quyền phân quyền chown www)</p>";
            }
        } else {
            if (@unlink($path)) {
                echo "<p style='color: green;'>Đã xoá tập tin: $item</p>";
                $deletedCount++;
            } else {
                echo "<p style='color: red;'>Lỗi khi xoá tập tin: $item (Kiểm tra quyền phân quyền)</p>";
            }
        }
    }
}

if ($deletedCount === 0) {
    echo "<p>Hệ thống đã sạch sẽ, không tìm thấy file rác nào.</p>";
} else {
    echo "<p><strong>Hoàn tất dọn dẹp!</strong></p>";
}

// Tự sát (Tự xoá chính file này)
@unlink(__FILE__);
echo "<p><em>File dọn dẹp này đã tự động xoá để đảm bảo bảo mật.</em></p>";
