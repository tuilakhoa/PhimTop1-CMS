<?php
$content = file_get_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php");

$new_funcs = <<<'EOT'
function crawl_phimtop1_movies_handle($url, $code, $updated_at, $slug, $language, $title, $filter_genre = array())
{
    try {
        $sourcePage = @file_get_contents($url);
        if ($sourcePage === false) return json_encode(['status'=>false, 'msg'=>'Lỗi mạng', 'wait'=>true, 'schedule_code'=>SCHEDULE_CRAWLER_TYPE_ERROR]);
        $sourcePage = json_decode($sourcePage, true);
        if (empty($sourcePage) || empty($sourcePage['movie'])) return json_encode(['status'=>false, 'msg'=>'Không có phim', 'wait'=>true, 'schedule_code'=>SCHEDULE_CRAWLER_TYPE_ERROR]);
        
        $movie = $sourcePage['movie'];
        
        $args = [
            'post_type' => 'ophim',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_ophim_meta_id',
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
                'post_type' => 'ophim',
            ];
            $post_id = wp_insert_post($post_data);
        }
        
        update_post_meta($post_id, '_ophim_meta_id', $movie['id']);
        
        update_post_meta($post_id, 'ophim_name', $movie['name']);
        update_post_meta($post_id, 'ophim_origin_name', $movie['origin_name']);
        update_post_meta($post_id, 'ophim_content', $movie['content']);
        update_post_meta($post_id, 'ophim_type', $movie['type'] === 'single' ? 'single' : 'series');
        update_post_meta($post_id, 'ophim_status', $movie['status'] === 'completed' ? 'completed' : 'ongoing');
        update_post_meta($post_id, 'ophim_thumb_url', $movie['thumb_url']);
        update_post_meta($post_id, 'ophim_poster_url', $movie['poster_url']);
        update_post_meta($post_id, 'ophim_trailer_url', $movie['trailer_url']);
        update_post_meta($post_id, 'ophim_episode_current', $movie['episode_current']);
        update_post_meta($post_id, 'ophim_episode_total', $movie['episode_total']);
        update_post_meta($post_id, 'ophim_quality', $movie['quality']);
        update_post_meta($post_id, 'ophim_lang', $movie['lang']);
        update_post_meta($post_id, 'ophim_showtimes', $movie['showtimes']);
        update_post_meta($post_id, 'ophim_year', $movie['year']);
        update_post_meta($post_id, 'ophim_view', $movie['view']);
        
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['actor'])), 'ophim_actors', false);
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['director'])), 'ophim_directors', false);
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['category'])), 'ophim_categories', false);
        wp_set_object_terms($post_id, array_map('trim', explode(',', $movie['country'])), 'ophim_regions', false);
        wp_set_object_terms($post_id, $movie['year'], 'ophim_years', false);
        
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
    $episode_list = array_values($servers);
    update_post_meta($post_id, 'ophim_episode_list', $episode_list);
}
EOT;

$content = preg_replace('/function crawl_phimtop1_movies_handle.*?return \$hl_status;\n}/s', $new_funcs, $content);
file_put_contents("scratch/phimtop1-crawler/crawl_movies_phimtop1.php", $content);
