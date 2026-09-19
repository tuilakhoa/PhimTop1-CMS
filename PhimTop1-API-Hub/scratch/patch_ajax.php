<?php
$content = file_get_contents("scratch/phimtop1-crawler/includes/Ajax.php");
$new_ajax = <<<EOT
add_action('wp_ajax_crawl_phimtop1_save_filter_genre', 'crawl_phimtop1_save_filter_genre');
function crawl_phimtop1_save_filter_genre()
{
    if (!defined('OFIM_CACHE_FILTER_GENRE_PHIMTOP1')) {
        wp_send_json_error(array('msg' => 'Constant not defined'));
        return;
    }
    \$listG = isset(\$_POST['filterGenrePhimTop1']) && is_array(\$_POST['filterGenrePhimTop1']) ? \$_POST['filterGenrePhimTop1'] : (isset(\$_POST['filterGenrePhimTop1']) ? array(\$_POST['filterGenrePhimTop1']) : array());
    \$listR = isset(\$_POST['filterRegionPhimTop1']) && is_array(\$_POST['filterRegionPhimTop1']) ? \$_POST['filterRegionPhimTop1'] : (isset(\$_POST['filterRegionPhimTop1']) ? array(\$_POST['filterRegionPhimTop1']) : array());
    \$listG = array_values(array_map('sanitize_text_field', \$listG));
    \$listR = array_values(array_map('sanitize_text_field', \$listR));
    \$data = array('filterGenrePhimTop1' => \$listG, 'filterRegionPhimTop1' => \$listR);
    \$path = OFIM_CACHE_FILTER_GENRE_PHIMTOP1;
    \$dir = dirname(\$path);
    if (!is_dir(\$dir)) {
        wp_send_json_error(array('msg' => 'Cache dir not found'));
        return;
    }
    \$written = @file_put_contents(\$path, json_encode(\$data, JSON_UNESCAPED_UNICODE));
    if (\$written === false) {
        wp_send_json_error(array('msg' => 'Could not write file'));
        return;
    }
    wp_send_json_success(array('msg' => 'Saved!'));
}
EOT;
$content = preg_replace('/add_action\(\'wp_ajax_crawl_phimtop1_save_filter_genre\'.*?return;\n    \}\n    wp_send_json_success\(array\(\'msg\' => \'Saved!\'\)\);\n\}/s', $new_ajax, $content);
file_put_contents("scratch/phimtop1-crawler/includes/Ajax.php", $content);
