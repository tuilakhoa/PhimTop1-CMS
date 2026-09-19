<?php
$content = file_get_contents("scratch/phimtop1-crawler/controllers/backend/AdminCrawlPhimTop1.php");
$new_func = <<<EOT
    public function getPhimTop1Genres() {
        return \$this->cache->remember('phimtop1_genres.txt', 3600, function() {
            \$url = API_DOMAIN . '/the-loai';
            \$resp = @file_get_contents(\$url);
            if (\$resp === false) {
                return array();
            }
            \$json = json_decode(\$resp);
            if (empty(\$json->data) || empty(\$json->data->items)) {
                return array();
            }
            \$genres = array();
            foreach (\$json->data->items as \$item) {
                \$genres[] = array(
                    'code' => \$item->name,
                    'name_vi' => \$item->name,
                    'name_en' => \$item->name,
                );
            }
            return \$genres;
        });
    }
EOT;
$content = preg_replace('/public function getPhimTop1Genres\(\) \{.*?\n    \}/s', $new_func, $content);
file_put_contents("scratch/phimtop1-crawler/controllers/backend/AdminCrawlPhimTop1.php", $content);
