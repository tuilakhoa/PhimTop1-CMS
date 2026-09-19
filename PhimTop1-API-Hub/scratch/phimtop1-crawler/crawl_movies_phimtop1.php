<?php

function crawl_phimtop1_page_handle($url, $language = 'vi')
{
    $sourcePage = file_get_contents($url);
    $sourcePage = json_decode($sourcePage);
    $listMovies = [];
    if (isset($sourcePage->data) && isset($sourcePage->data->items)) {
        $items = $sourcePage->data->items;
        foreach ($items as $item) {
            $title = $item->name;
            $slug = $item->slug;
            $code = $item->id ?? $item->slug;
            $updated_at = date('Y-m-d H:i:s');
            array_push($listMovies, API_DOMAIN . "/phim/{$slug}|{$code}|{$updated_at}|{$title}|{$slug}|{$language}");
        }
        return join("\n", $listMovies);
    }
    return $listMovies;
}

add_action('wp_ajax_crawl_phimtop1_movies', 'crawl_phimtop1_movies');
function crawl_phimtop1_movies()
{
    $data_post = $_POST['url'];
    $url = explode('|', $data_post)[0];
    $code = explode('|', $data_post)[1];
    $updated_at = explode('|', $data_post)[2];
    $title = explode('|', $data_post)[3];
    $slug = explode('|', $data_post)[4];
    $language = explode('|', $data_post)[5];
    $filter_region = isset($_POST['filterRegion']) && is_array($_POST['filterRegion']) ? array_map('sanitize_text_field', $_POST['filterRegion']) : array();
    $filter_genre = isset($_POST['filterGenre']) && is_array($_POST['filterGenre']) ? array_map('sanitize_text_field', $_POST['filterGenre']) : array();
    $result = crawl_phimtop1_movies_handle($url, $code, $updated_at, $slug, $language, $title, $filter_genre, $filter_region);
    echo $result;
    die();
}

function crawl_phimtop1_movies_handle($url, $code, $updated_at, $slug, $language, $title, $filter_genre = array(), $filter_region = array())
{
    try {
        $sourcePage = @file_get_contents($url);
        if ($sourcePage === false) return json_encode(['status'=>false, 'msg'=>'Lỗi mạng', 'wait'=>true, 'schedule_code'=>SCHEDULE_CRAWLER_TYPE_ERROR]);
        $sourcePage = json_decode($sourcePage, true);
        if (empty($sourcePage) || empty($sourcePage['movie'])) return json_encode(['status'=>false, 'msg'=>'Không có phim', 'wait'=>true, 'schedule_code'=>SCHEDULE_CRAWLER_TYPE_ERROR]);
        
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
        
                if (!empty($filter_region) && !empty($movie['country'])) {
            $movie_countries = array_map('trim', explode(',', $movie['country']));
            foreach ($filter_region as $exclude_code) {
                if (in_array($exclude_code, $movie_countries)) {
                    return json_encode(array(
                        'status' => false,
                        'msg' => 'Bỏ qua: phim thuộc quốc gia nằm trong danh sách loại trừ',
                        'wait' => false,
                        'schedule_code' => SCHEDULE_CRAWLER_TYPE_FILTER
                    ));
                }
            }
        }
        
        $args = [
            'post_type' => 'movie',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_movie_api_id',
                    'value' => $movie['id'],
                    'compare' => '='
                ]
            ]
        ];
        $wp_query = new WP_Query($args);
        $insert = false;
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $post_id = get_the_ID();
            }
            wp_reset_postdata();
        } else {
            $insert = true;
            $post_data = [
                'post_title' => $movie['name'],
                'post_name' => $movie['slug'],
                'post_status' => 'publish',
                'post_type' => 'movie',
            ];
            $post_id = wp_insert_post($post_data);
        }
        
        update_post_meta($post_id, '_movie_api_id', $movie['id']);
        
        update_post_meta($post_id, 'name', $movie['name']);
        update_post_meta($post_id, 'origin_name', $movie['origin_name']);
        update_post_meta($post_id, 'content', $movie['content']);
        update_post_meta($post_id, 'type', $movie['type'] === 'single' ? 'single' : 'series');
        update_post_meta($post_id, 'status', $movie['status'] === 'completed' ? 'completed' : 'ongoing');
        update_post_meta($post_id, 'thumb_url', $movie['thumb_url']);
        update_post_meta($post_id, 'poster_url', $movie['poster_url']);
        update_post_meta($post_id, 'trailer_url', $movie['trailer_url']);
        update_post_meta($post_id, 'episode_current', $movie['episode_current']);
        update_post_meta($post_id, 'episode_total', $movie['episode_total']);
        update_post_meta($post_id, 'quality', $movie['quality']);
        update_post_meta($post_id, 'lang', $movie['lang']);
        update_post_meta($post_id, 'showtimes', $movie['showtimes']);
        update_post_meta($post_id, 'year', $movie['year']);
        update_post_meta($post_id, 'view', $movie['view']);
        
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['actor'])), 'movie_actors', false);
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['director'])), 'movie_directors', false);
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['category'])), 'movie_genres', false);
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['country'])), 'movie_regions', false);
        wp_set_object_terms($post_id, $movie['year'], 'movie_years', false);
        
        get_list_episode_phimtop1($sourcePage['episodes'], $post_id);
        
        return json_encode([
            'status' => true,
            'msg' => $insert ? 'Thêm thành công' : 'Cập nhật thành công',
            'wait' => false,
            'schedule_code' => $insert ? SCHEDULE_CRAWLER_TYPE_INSERT : SCHEDULE_CRAWLER_TYPE_UPDATE
        ]);
        
    } catch (Exception $e) {
        return json_encode(['status'=>false, 'msg'=>'Lỗi: ' . $e->getMessage(), 'wait'=>true, 'schedule_code'=>SCHEDULE_CRAWLER_TYPE_ERROR]);
    }
}

function get_list_episode_phimtop1($episodes, $post_id)
{
    $servers = [];
    foreach ($episodes as $ep) {
        $server_name = $ep['server_name'] ?: 'PhimTop1';
        if (!isset($servers[$server_name])) {
            $servers[$server_name] = [
                'server_name' => $server_name,
                'server_data' => []
            ];
        }
        $servers[$server_name]['server_data'][] = [
            'name' => $ep['name'],
            'slug' => $ep['slug'],
            'filename' => $ep['filename'],
            'link_embed' => $ep['embed_url'],
            'link_m3u8' => $ep['m3u8_url']
        ];
    }
    $episode_list = json_encode(array_values($servers));
    update_post_meta($post_id, 'episodes', $episode_list);
}