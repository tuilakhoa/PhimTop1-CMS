<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>">
    <style>
        body { background-color: #000000; color: #d1d5db; }
        .text-blue-500 { color: #f43f5e; }
        .bg-kk-red { background-color: #f43f5e; }
        .card-movie { transition: transform 0.2s, box-shadow 0.2s; }
        .card-movie:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-[#000000] text-gray-300 font-sans antialiased'); ?>>
    <?php
    // Fetch Thể Loại from API
    $genres = get_transient('pt1_api_genres');
    if (false === $genres) {
        $res = @file_get_contents('https://api.phimtop1.asia/api/the-loai');
        $data = json_decode($res, true);
        if (isset($data['status']) && $data['status'] == true) {
            $genres = $data['data']['items'];
            set_transient('pt1_api_genres', $genres, 12 * HOUR_IN_SECONDS);
        } else {
            $genres = [];
        }
    }
    
    // Fetch Quốc Gia from API
    $countries = get_transient('pt1_api_countries');
    if (false === $countries) {
        $res = @file_get_contents('https://api.phimtop1.asia/api/quoc-gia');
        $data = json_decode($res, true);
        if (isset($data['status']) && $data['status'] == true) {
            $countries = $data['data']['items'];
            set_transient('pt1_api_countries', $countries, 12 * HOUR_IN_SECONDS);
        } else {
            $countries = [];
        }
    }
    ?>
    <header class="bg-[#000000]/80 backdrop-blur-xl border-b border-gray-800/50 sticky top-0 z-50 transition-all">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="<?php echo home_url(); ?>" class="text-xl font-extrabold text-white flex items-center gap-2 hover:opacity-80 transition-opacity">
                <i class="fas fa-play-circle text-blue-500 text-2xl"></i> <span class="tracking-tight"><?php bloginfo('name'); ?></span>
            </a>
            <nav class="hidden md:flex items-center gap-1 font-semibold text-[13px]">
                <a href="<?php echo home_url(); ?>" class="px-4 py-1.5 rounded-full hover:bg-white/5 hover:text-white transition text-gray-300">TRANG CHỦ</a>
                <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="px-4 py-1.5 rounded-full hover:bg-white/5 hover:text-white transition text-gray-300">PHIM MỚI</a>
                
                <!-- Dropdown Thể Loại -->
                <div class="relative group h-full flex items-center">
                    <a href="javascript:void(0)" class="px-4 py-1.5 rounded-full hover:bg-white/5 hover:text-white transition text-gray-300 flex items-center gap-1.5">
                        THỂ LOẠI <i class="fas fa-chevron-down text-[10px] opacity-70"></i>
                    </a>
                    <div class="absolute left-0 top-full mt-2 w-[450px] bg-[#0a0a0a] border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 grid grid-cols-3 p-4 gap-2 z-50">
                        <?php foreach($genres as $g): ?>
                            <a href="<?php echo home_url('/the-loai/' . $g['slug']); ?>" class="text-gray-400 hover:text-white hover:bg-white/5 px-3 py-2 rounded-lg transition-colors text-xs truncate">
                                <?php echo esc_html($g['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Dropdown Quốc Gia -->
                <div class="relative group h-full flex items-center">
                    <a href="javascript:void(0)" class="px-4 py-1.5 rounded-full hover:bg-white/5 hover:text-white transition text-gray-300 flex items-center gap-1.5">
                        QUỐC GIA <i class="fas fa-chevron-down text-[10px] opacity-70"></i>
                    </a>
                    <div class="absolute left-0 top-full mt-2 w-[300px] bg-[#0a0a0a] border border-gray-800 rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 grid grid-cols-2 p-4 gap-2 z-50">
                        <?php foreach($countries as $c): ?>
                            <a href="<?php echo home_url('/quoc-gia/' . $c['slug']); ?>" class="text-gray-400 hover:text-white hover:bg-white/5 px-3 py-2 rounded-lg transition-colors text-xs truncate">
                                <?php echo esc_html($c['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </nav>
            <div class="flex items-center gap-4">
                <form id="live-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative hidden sm:block">
                    <input id="live-search-input" type="text" autocomplete="off" name="s" value="<?php echo get_search_query(); ?>" placeholder="Tìm kiếm phim..." class="bg-gray-800/40 text-sm text-white rounded-full pl-9 pr-4 py-1.5 w-56 focus:outline-none focus:ring-1 focus:ring-kk-red border border-gray-700/50 transition-all focus:w-64 placeholder-gray-500">
                    <button type="submit" class="absolute left-3 top-2 text-gray-500 hover:text-white transition">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                    <!-- Bảng kết quả search -->
                    <div id="live-search-results" class="absolute top-full mt-2 right-0 w-80 bg-[#0a0a0a] border border-gray-800 rounded-xl shadow-2xl overflow-hidden hidden z-50 max-h-96 overflow-y-auto"></div>
                </form>
            </div>
        <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-white focus:outline-none ml-4">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        </div>
        
        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu" class="hidden md:hidden bg-[#0a0a0a] border-t border-gray-800 absolute w-full left-0 top-full shadow-2xl flex flex-col">
            <div class="p-4 border-b border-gray-800">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative w-full">
                    <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="Tìm kiếm phim..." class="bg-gray-800/80 text-sm text-white rounded-full pl-10 pr-4 py-2 w-full focus:outline-none focus:ring-1 focus:ring-kk-red border border-gray-700">
                    <button type="submit" class="absolute left-3 top-2.5 text-gray-400 hover:text-white transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <a href="<?php echo home_url(); ?>" class="px-6 py-3 border-b border-gray-800/50 text-gray-300 hover:text-white hover:bg-white/5">TRANG CHỦ</a>
            <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="px-6 py-3 border-b border-gray-800/50 text-gray-300 hover:text-white hover:bg-white/5">PHIM MỚI</a>
            
            <div class="px-6 py-3 border-b border-gray-800/50">
                <div class="text-gray-300 font-bold mb-2 text-sm text-blue-500">THỂ LOẠI</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach(array_slice($genres, 0, 10) as $g): ?>
                        <a href="<?php echo home_url('/the-loai/' . $g['slug']); ?>" class="text-xs text-gray-400 hover:text-white bg-gray-800/50 px-2 py-1 rounded"><?php echo esc_html($g['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="px-6 py-3 pb-6">
                <div class="text-gray-300 font-bold mb-2 text-sm text-blue-500">QUỐC GIA</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach(array_slice($countries, 0, 10) as $c): ?>
                        <a href="<?php echo home_url('/quoc-gia/' . $c['slug']); ?>" class="text-xs text-gray-400 hover:text-white bg-gray-800/50 px-2 py-1 rounded"><?php echo esc_html($c['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 py-8">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('live-search-input');
    const searchResults = document.getElementById('live-search-results');
    let timeoutId = null;

    if(!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(timeoutId);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        timeoutId = setTimeout(() => {
            searchResults.classList.remove('hidden');
            searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm"><i class="fas fa-spinner fa-spin mr-2"></i> Đang tìm...</div>';

            fetch('/wp-admin/admin-ajax.php?action=live_search_movies&q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    let html = '';
                    data.forEach(movie => {
                        html += `
                        <a href="${movie.url}" class="flex items-center gap-3 p-3 hover:bg-gray-800 transition border-b border-gray-800/50 last:border-0 group">
                            <img src="${movie.thumb}" alt="${movie.title}" class="w-10 h-14 object-cover rounded shadow-md">
                            <div class="flex-1 min-w-0 text-left">
                                <h4 class="text-sm font-bold text-gray-200 truncate group-hover:text-red-500 transition-colors">${movie.title}</h4>
                                <p class="text-xs text-gray-500 truncate mt-0.5">${movie.origin_name} (${movie.year})</p>
                            </div>
                        </a>`;
                    });
                    searchResults.innerHTML = html;
                } else {
                    searchResults.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Không tìm thấy phim nào.</div>';
                }
            })
            .catch(() => {
                searchResults.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Lỗi kết nối.</div>';
            });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
