<?php
$content = file_get_contents("scratch/phimtop1-crawler/controllers/backend/AdminCrawlPhimTop1.php");
$new_func = <<<EOT
    public function getPhimTop1Genres() {
        \$genres = array(
            'Hành Động', 'Tình Cảm', 'Hài Hước', 'Cổ Trang', 'Tâm Lý', 'Hình Sự', 
            'Chiến Tranh', 'Thể Thao', 'Võ Thuật', 'Viễn Tưởng', 'Phiêu Lưu', 'Khoa Học', 
            'Kinh Dị', 'Âm Nhạc', 'Thần Thoại', 'Tài Liệu', 'Gia Đình', 'Chính kịch', 
            'Bí ẩn', 'Học Đường', 'Kinh Điển', 'Thanh Xuân', 'Trinh Thám'
        );
        \$result = array();
        foreach (\$genres as \$g) {
            \$result[] = array(
                'code' => \$g,
                'name_vi' => \$g,
                'name_en' => \$g
            );
        }
        return \$result;
    }
EOT;
$content = preg_replace('/public function getPhimTop1Genres\(\) \{.*?\n    \}/s', $new_func, $content);
file_put_contents("scratch/phimtop1-crawler/controllers/backend/AdminCrawlPhimTop1.php", $content);
