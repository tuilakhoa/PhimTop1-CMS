<?php
$content = file_get_contents("scratch/phimtop1-crawler/schedule-phimtop1.php");
$new_sched = <<<EOT
// Load filter genre from phimtop1 cache file
\$filter_genre = array();
\$filter_region = array();
if (defined('OFIM_CACHE_FILTER_GENRE_PHIMTOP1') && file_exists(OFIM_CACHE_FILTER_GENRE_PHIMTOP1)) {
    \$raw = @file_get_contents(OFIM_CACHE_FILTER_GENRE_PHIMTOP1);
    if (\$raw !== false) {
        \$dec = json_decode(\$raw, true);
        if (!empty(\$dec['filterGenrePhimTop1']) && is_array(\$dec['filterGenrePhimTop1'])) {
            \$filter_genre = \$dec['filterGenrePhimTop1'];
        }
        if (!empty(\$dec['filterRegionPhimTop1']) && is_array(\$dec['filterRegionPhimTop1'])) {
            \$filter_region = \$dec['filterRegionPhimTop1'];
        }
    }
}
if (empty(\$filter_genre) && isset(\$crawl_phimtop1_settings->filterGenrePhimTop1) && is_array(\$crawl_phimtop1_settings->filterGenrePhimTop1)) {
    \$filter_genre = \$crawl_phimtop1_settings->filterGenrePhimTop1;
}
if (empty(\$filter_region) && isset(\$crawl_phimtop1_settings->filterRegionPhimTop1) && is_array(\$crawl_phimtop1_settings->filterRegionPhimTop1)) {
    \$filter_region = \$crawl_phimtop1_settings->filterRegionPhimTop1;
}

try {
EOT;
$content = preg_replace('/\/\/ Load filter genre from phimtop1 cache file.*?try \{/s', $new_sched, $content);
$content = str_replace("\$result = crawl_phimtop1_movies_handle(\$url, \$code, \$updated_at, \$slug, \$language, \$title, \$filter_genre);", "\$result = crawl_phimtop1_movies_handle(\$url, \$code, \$updated_at, \$slug, \$language, \$title, \$filter_genre, \$filter_region);", $content);
file_put_contents("scratch/phimtop1-crawler/schedule-phimtop1.php", $content);
