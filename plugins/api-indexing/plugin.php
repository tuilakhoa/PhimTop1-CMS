<?php
// Tên Plugin: Tự động Indexing (Google/IndexNow)
// Phiên bản: 1.0.0

add_filter('admin_menu_groups', function($groups) {
    if (!isset($groups['Hệ Thống'])) {
        $groups['Hệ Thống'] = [];
    }
    // Chèn menu API Indexing vào
    $groups['Hệ Thống']['plugin_api_indexing'] = [
        'icon' => 'globe',
        'title' => 'API Indexing'
    ];
    return $groups;
});

// Điều hướng trang
add_filter('admin_page_file', function($file, $page) {
    if ($page === 'plugin_api_indexing') {
        return __DIR__ . '/admin_page.php';
    }
    return $file;
});
