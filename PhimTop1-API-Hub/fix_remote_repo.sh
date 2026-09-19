ssh -p 24700 root@103.72.97.151 << 'REMOTESCRIPT'
php -r '
$file = "/www/wwwroot/api.phimtop1.asia/includes/repo/MovieRepository.php";
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace(
        "updated_at=VALUES(updated_at)",
        "updated_at=IF(episode_current != VALUES(episode_current) OR status != VALUES(status) OR quality != VALUES(quality), VALUES(updated_at), updated_at)",
        $content
    );
    file_put_contents($file, $content);
    echo "Đã vá lỗi cập nhật thời gian hàng loạt trong MovieRepository.php!\n";
} else {
    echo "Không tìm thấy file MovieRepository.php tại $file\n";
}

$file2 = "/www/wwwroot/api.phimtop1.asia/plugins/kkphim-crawler/update_missing_data.php";
if (file_exists($file2)) {
    $content2 = file_get_contents($file2);
    $content2 = str_replace(
        "\$stmtTouch->execute([date(",
        "// \$stmtTouch->execute([date(",
        $content2
    );
    file_put_contents($file2, $content2);
    echo "Đã vá lỗi script quét data rác (update_missing_data.php)!\n";
}
'
REMOTESCRIPT
