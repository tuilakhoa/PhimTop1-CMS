=================================================
       THONG TIN KHOI TAO VPS (ALMALINUX 8)      
=================================================
IP Public     : 103.72.97.151
Link Quan Tri : http://103.72.97.151:7800/9ygan081
Tai Khoan     : admin151sb4
Mat Khau      : FQ5tiXcP4xkE
=================================================
Luu y: Vui long luu lai thong tin va xoa file nay.
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>">
    <style>
        body { background-color: #0f1115; color: #d1d5db; }
        .text-kk-red { color: #f43f5e; }
        .bg-kk-red { background-color: #f43f5e; }
        .card-movie { transition: transform 0.2s, box-shadow 0.2s; }
        .card-movie:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-[#0f1115] text-gray-300 font-sans antialiased'); ?>>
    <header class="bg-[#181a20] border-b border-gray-800 sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="<?php echo home_url(); ?>" class="text-2xl font-black text-white flex items-center gap-2">
                <i class="fas fa-play-circle text-kk-red"></i> <?php bloginfo('name'); ?>
            </a>
            <nav class="hidden md:flex gap-6 font-bold text-sm">
                <a href="<?php echo home_url(); ?>" class="hover:text-kk-red transition text-white">TRANG CHỦ</a>
                <a href="<?php echo get_post_type_archive_link('movie'); ?>" class="hover:text-kk-red transition text-gray-300">PHIM MỚI</a>
                <a href="#" class="hover:text-kk-red transition text-gray-300">THỂ LOẠI</a>
                <a href="#" class="hover:text-kk-red transition text-gray-300">QUỐC GIA</a>
            </nav>
            <div class="flex items-center gap-4">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative hidden sm:block">
                    <input type="text" name="s" value="<?php echo get_search_query(); ?>" placeholder="Tìm kiếm phim..." class="bg-[#272a30] text-sm text-white rounded-full px-4 py-2 w-64 focus:outline-none focus:ring-1 focus:ring-kk-red border border-gray-700">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-white transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 py-8">
