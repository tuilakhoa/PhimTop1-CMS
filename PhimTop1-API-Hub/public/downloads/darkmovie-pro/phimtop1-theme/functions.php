<?php
// Đăng ký Custom Post Type 'movie'
add_action('init', 'pt1_register_movie_cpt');
function pt1_register_movie_cpt() {
    register_post_type('movie', array(
        'labels'      => array(
            'name'          => 'Phim', 
            'singular_name' => 'Phim',
            'menu_name'     => 'Kho Phim'
        ),
        'public'      => true,
        'has_archive' => true,
        'supports'    => array('title', 'editor', 'thumbnail'),
        'menu_icon'   => 'dashicons-video-alt3',
        'rewrite'     => array('slug' => 'phim')
    ));
}

// Thêm theme support
add_action('after_setup_theme', 'pt1_theme_setup');
function pt1_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}

// Live Search Movies AJAX
add_action('wp_ajax_live_search_movies', 'phimtop1_live_search_movies');
add_action('wp_ajax_nopriv_live_search_movies', 'phimtop1_live_search_movies');
function phimtop1_live_search_movies() {
    $q = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
    if (empty($q)) wp_send_json([]);
    
    $args = array(
        'post_type' => 'movie',
        's' => $q,
        'posts_per_page' => 5,
        'post_status' => 'publish'
    );
    $query = new WP_Query($args);
    $results = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $thumb = get_post_meta(get_the_ID(), 'thumb_url', true);
            if(empty($thumb)) $thumb = 'https://via.placeholder.com/40x56?text=No+Thumb';
            $results[] = array(
                'title' => get_the_title(),
                'url' => get_the_permalink(),
                'thumb' => $thumb,
                'origin_name' => get_post_meta(get_the_ID(), 'origin_name', true) ?: '',
                'year' => get_post_meta(get_the_ID(), 'year', true) ?: ''
            );
        }
        wp_reset_postdata();
    }
    wp_send_json($results);
}
