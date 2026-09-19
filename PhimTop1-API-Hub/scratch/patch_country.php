<?php
$content = file_get_contents("scratch/phimtop1-crawler/controllers/backend/AdminCrawlPhimTop1.php");
$new_func = <<<EOT
    public function getPhimTop1Regions() {
        return \$this->cache->remember('phimtop1_regions.txt', 3600, function() {
            \$url = API_DOMAIN . '/quoc-gia';
            \$resp = @file_get_contents(\$url);
            if (\$resp === false) return array();
            \$json = json_decode(\$resp);
            if (empty(\$json->data) || empty(\$json->data->items)) return array();
            \$regions = array();
            foreach (\$json->data->items as \$item) {
                \$regions[] = array(
                    'code' => \$item->name,
                    'name_vi' => \$item->name,
                    'name_en' => \$item->name,
                );
            }
            return \$regions;
        });
    }

    public function getLastLog() {
EOT;
$content = str_replace('public function getLastLog() {', $new_func, $content);
$content = str_replace('$genres = $this->getPhimTop1Genres();', "\$genres = \$this->getPhimTop1Genres();\n        \$regions = \$this->getPhimTop1Regions();", $content);
file_put_contents("scratch/phimtop1-crawler/controllers/backend/AdminCrawlPhimTop1.php", $content);

$template = file_get_contents("scratch/phimtop1-crawler/template/backend/crawl-phimtop1.php");
$country_ui = <<<EOT
                            <tr valign="top">
                                <th scope="row">
                                    <label for="filter_region">Bỏ qua quốc gia (PhimTop1)</label>
                                </th>
                                <td>
                                    <div style="display: flex; flex-wrap: wrap; gap: 10px; max-height: 200px; overflow-y: auto; padding: 10px; border: 1px solid #ccd0d4; border-radius: 4px; background: #fff;">
                                        <?php
                                        \$filter_region = oIsset(\$crawl_settings, 'filter_region', array());
                                        if (!is_array(\$filter_region)) \$filter_region = array();
                                        if (!empty(\$regions)) {
                                            foreach (\$regions as \$region) {
                                                \$checked = in_array(\$region['code'], \$filter_region) ? 'checked' : '';
                                                echo '<label style="flex: 1 1 30%; min-width: 150px;">';
                                                echo '<input type="checkbox" name="crawl_phimtop1_schedule_settings[filter_region][]" value="'.esc_attr(\$region['code']).'" '.\$checked.'> ';
                                                echo esc_html(\$region['name_vi']);
                                                echo '</label>';
                                            }
                                        } else {
                                            echo '<p>Không lấy được danh sách quốc gia từ API.</p>';
                                        }
                                        ?>
                                    </div>
                                    <p class="description">Chọn các quốc gia muốn BỎ QUA khi cào phim. Phim thuộc quốc gia đã chọn sẽ không được lưu vào hệ thống.</p>
                                </td>
                            </tr>
EOT;
$template = preg_replace('/(<tr valign="top">\s*<th scope="row">\s*<label for="filter_genre">.*?<\/tr>)/s', "$1\n$country_ui", $template);
file_put_contents("scratch/phimtop1-crawler/template/backend/crawl-phimtop1.php", $template);

$crawler = file_get_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php");
// Fix the crawler backend receiver logic to handle filterRegion
$crawler = str_replace("\$filter_genre = isset(\$_POST['filterGenre'])", "\$filter_region = isset(\$_POST['filterRegion']) && is_array(\$_POST['filterRegion']) ? array_map('sanitize_text_field', \$_POST['filterRegion']) : array();\n    \$filter_genre = isset(\$_POST['filterGenre'])", $crawler);
$crawler = str_replace("\$filter_genre);", "\$filter_genre, \$filter_region);", $crawler);
$crawler = str_replace("\$filter_genre = array())", "\$filter_genre = array(), \$filter_region = array())", $crawler);

$filter_logic = <<<EOT
        if (!empty(\$filter_region) && !empty(\$movie['country'])) {
            \$movie_countries = array_map('trim', explode(',', \$movie['country']));
            foreach (\$filter_region as \$exclude_code) {
                if (in_array(\$exclude_code, \$movie_countries)) {
                    return json_encode(array(
                        'status' => false,
                        'msg' => 'Bỏ qua: phim thuộc quốc gia nằm trong danh sách loại trừ',
                        'wait' => false,
                        'schedule_code' => SCHEDULE_CRAWLER_TYPE_FILTER
                    ));
                }
            }
        }
        
        \$args = [
EOT;
$crawler = str_replace("\$args = [", $filter_logic, $crawler);
file_put_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php", $crawler);

// Finally we need to update script-phimtop1.js to pass filterRegion
$js = file_get_contents("scratch/phimtop1-crawler/public/js/script-phimtop1.js");
$js = str_replace("filterGenre: $('input[name=\"crawl_phimtop1_schedule_settings[filter_genre][]\"]:checked').map(function(){return $(this).val();}).get(),", "filterGenre: $('input[name=\"crawl_phimtop1_schedule_settings[filter_genre][]\"]:checked').map(function(){return $(this).val();}).get(),\n                            filterRegion: $('input[name=\"crawl_phimtop1_schedule_settings[filter_region][]\"]:checked').map(function(){return $(this).val();}).get(),", $js);
file_put_contents("scratch/phimtop1-crawler/public/js/script-phimtop1.js", $js);
