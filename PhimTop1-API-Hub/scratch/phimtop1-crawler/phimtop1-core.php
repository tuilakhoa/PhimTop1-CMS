<?php
/**
 * Plugin Name: PhimTop1 Crawler
 * Description: Hệ thống kho dữ liệu phim tập trung, cung cấp đầy đủ metadata (tiêu đề, mô tả, poster, diễn viên, thể loại, quốc gia, nguồn phát) thông qua API PhimTop1 đơn giản và linh hoạt.
 * Version: 1.0
 * Author: PhimTop1
 * Author URI: https://api.phimtop1.asia
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}
require_once 'define.php';
require_once OFIM_HELPERS_PATCH.'/cache.php';
require_once OFIM_HELPERS_PATCH.'/functions.php';
require_once OFIM_INCLUDE_PATCH.'/Controller.php';
require_once OFIM_INCLUDE_PATCH.'/Permalink.php';
require_once OFIM_INCLUDE_PATCH.'/Tax.php';
require_once OFIM_INCLUDE_PATCH.'/Shortcuts.php';
require_once OFIM_INCLUDE_PATCH.'/Ajax.php';
require_once 'crawl_movies.php';
require_once 'crawl_movies_phimtop1.php';

global $oController;
$oController = new oController();

if (is_admin()){
    require_once 'backend.php';
    new oFim_Backend();
}else{
    require_once OFIM_INCLUDE_PATCH.'/Episode.php';
}
