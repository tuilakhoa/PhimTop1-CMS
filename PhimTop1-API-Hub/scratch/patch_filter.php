<?php
$content = file_get_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php");

$filter_logic = <<<'EOT'
        $movie = $sourcePage['movie'];
        
        if (!empty($filter_genre) && !empty($movie['category'])) {
            $movie_categories = array_map('trim', explode(',', $movie['category']));
            foreach ($filter_genre as $exclude_code) {
                if (in_array($exclude_code, $movie_categories)) {
                    return json_encode(array(
                        'status' => false,
                        'msg' => 'Bỏ qua: phim thuộc thể loại nằm trong danh sách loại trừ',
                        'wait' => false,
                        'schedule_code' => SCHEDULE_CRAWLER_TYPE_FILTER
                    ));
                }
            }
        }
        
        $args = [
EOT;

$content = str_replace("\$movie = \$sourcePage['movie'];\n        \n        \$args = [", $filter_logic, $content);
file_put_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php", $content);
