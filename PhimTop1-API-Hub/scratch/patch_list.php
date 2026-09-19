<?php
$content = file_get_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php");
$new_func = <<<EOT
function crawl_phimtop1_page_handle(\$url, \$language = 'vi')
{
    \$sourcePage = file_get_contents(\$url);
    \$sourcePage = json_decode(\$sourcePage);
    \$listMovies = [];
    if (isset(\$sourcePage->data) && isset(\$sourcePage->data->items)) {
        \$items = \$sourcePage->data->items;
        foreach (\$items as \$item) {
            \$title = \$item->name;
            \$slug = \$item->slug;
            \$code = \$item->id ?? \$item->slug;
            \$updated_at = date('Y-m-d H:i:s');
            array_push(\$listMovies, API_DOMAIN . "/phim/{\$slug}|{\$code}|{\$updated_at}|{\$title}|{\$slug}|{\$language}");
        }
        return join("\\n", \$listMovies);
    }
    return \$listMovies;
}
EOT;
$content = preg_replace('/function crawl_phimtop1_page_handle.*?return \$listMovies;\n}/s', $new_func, $content);
file_put_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php", $content);
