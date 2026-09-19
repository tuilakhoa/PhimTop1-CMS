const fs = require('fs');

let headerCode = fs.readFileSync('new_header.php', 'utf8');

const oldForm = `<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative hidden sm:block">
                    <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="Tìm kiếm phim..." class="bg-gray-800/40 text-sm text-white rounded-full pl-9 pr-4 py-1.5 w-56 focus:outline-none focus:ring-1 focus:ring-kk-red border border-gray-700/50 transition-all focus:w-64 placeholder-gray-500">
                    <button type="submit" class="absolute left-3 top-2 text-gray-500 hover:text-white transition">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                </form>`;

const newForm = `<form id="live-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative hidden sm:block">
                    <input id="live-search-input" type="text" autocomplete="off" name="s" value="<?php echo get_search_query(); ?>" placeholder="Tìm kiếm phim..." class="bg-gray-800/40 text-sm text-white rounded-full pl-9 pr-4 py-1.5 w-56 focus:outline-none focus:ring-1 focus:ring-kk-red border border-gray-700/50 transition-all focus:w-64 placeholder-gray-500">
                    <button type="submit" class="absolute left-3 top-2 text-gray-500 hover:text-white transition">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                    <!-- Bảng kết quả search -->
                    <div id="live-search-results" class="absolute top-full mt-2 right-0 w-80 bg-[#181a20] border border-gray-800 rounded-xl shadow-2xl overflow-hidden hidden z-50 max-h-96 overflow-y-auto"></div>
                </form>`;

if(headerCode.includes('role="search"')) {
    headerCode = headerCode.replace(/<form role="search"[\s\S]*?<\/form>/, newForm);
    let script = fs.readFileSync('live_search.js', 'utf8');
    headerCode += '\n' + script;
    fs.writeFileSync('new_header.php', headerCode);
    console.log("Patched new_header.php");
}

let functionsCode = fs.readFileSync('public/downloads/phimtop1-theme/functions.php', 'utf8');
const ajaxCode = `
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
`;

if (!functionsCode.includes('phimtop1_live_search_movies')) {
    functionsCode += ajaxCode;
    fs.writeFileSync('public/downloads/phimtop1-theme/functions.php', functionsCode);
    console.log("Patched functions.php");
}

