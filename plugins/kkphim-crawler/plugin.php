<?php
// Tên Plugin: Crawler Đa Nguồn (KKPhim, Nguồn C, VsMov)
// Phiên bản: 1.1.0

add_filter('admin_menu_groups', function($groups) {
    if (!isset($groups['Quản Lý Nội Dung'])) {
        $groups['Quản Lý Nội Dung'] = [];
    }
    $groups['Quản Lý Nội Dung']['plugin_kkphim_crawler'] = [
        'icon' => 'download',
        'title' => 'Crawl Phim Đa Nguồn'
    ];
    return $groups;
});

add_filter('admin_page_file', function($file, $page) {
    if ($page === 'plugin_kkphim_crawler') {
        return __DIR__ . '/admin_page.php';
    }
    return $file;
});
