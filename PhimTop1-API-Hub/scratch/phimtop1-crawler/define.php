<?php


//URL
define('OFIM_PLUGIN_URL',    plugin_dir_url(__FILE__));
define('OFIM_PUBLIC_URL',    OFIM_PLUGIN_URL.'public');
define('OFIM_CSS_URL',       OFIM_PUBLIC_URL.'/css');
define('OFIM_IMAGE_URL',     OFIM_PUBLIC_URL.'/image');
define('OFIM_JS_URL',        OFIM_PUBLIC_URL.'/js');

//PATCH
define('DS'                 ,DIRECTORY_SEPARATOR);
define('OFIM_PLUGIN_PATCH'  ,plugin_dir_path(__FILE__));
define('OFIM_CONFIG_PATCH'  ,OFIM_PLUGIN_PATCH.'configs');
define('OFIM_CONTROLLERS_PATCH'  ,OFIM_PLUGIN_PATCH.'controllers');
define('OFIM_HELPERS_PATCH'  ,OFIM_PLUGIN_PATCH.'helpers');
define('OFIM_INCLUDE_PATCH'  ,OFIM_PLUGIN_PATCH.'includes');
define('OFIM_MODELS_PATCH'  ,OFIM_PLUGIN_PATCH.'models');
define('OFIM_TEMPLADE_PATCH'  ,OFIM_PLUGIN_PATCH.'template');
define('OFIM_VALIDATES_PATCH'  ,OFIM_PLUGIN_PATCH.'validates');

//OTHER
define('OFIM_CACHE_TIME', 172800);
define('OFIM_CACHE_DIR', OFIM_PLUGIN_PATCH . 'cache/');
define('OFIM_CACHE_FILTER_GENRE_PHIMTOP1', OFIM_CACHE_DIR . 'filter_genre_phimtop1.json');

define('OFIM_PREFIX'  ,'OFIM_');

// PHIMTOP1 API (site dùng cho link tài liệu; API_DOMAIN = base REST v1)
define('PHIMTOP1_SITE_URL', 'https://api.phimtop1.asia');
define('API_DOMAIN', PHIMTOP1_SITE_URL . '/api');

// Lưu ý: tên option trong DB vẫn crawl_movie_* để tương thích site đã cài.
define('CRAWL_PHIMTOP1_OPTION_SETTINGS', 'crawl_phimtop1_schedule_settings');
define('CRAWL_PHIMTOP1_OPTION_RUNNING', 'crawl_phimtop1_schedule_running');
define('CRAWL_PHIMTOP1_OPTION_SECRET_KEY', 'crawl_phimtop1_schedule_secret_key');

define('SCHEDULE_CRAWLER_TYPE_NOTHING', 0);
define('SCHEDULE_CRAWLER_TYPE_INSERT', 1);
define('SCHEDULE_CRAWLER_TYPE_UPDATE', 2);
define('SCHEDULE_CRAWLER_TYPE_ERROR', 3);
define('SCHEDULE_CRAWLER_TYPE_FILTER', 4);

define('CRAWL_PHIMTOP1_PATH', plugin_dir_path(__FILE__));
define('CRAWL_PHIMTOP1_PATH_SCHEDULE_JSON', CRAWL_PHIMTOP1_PATH . 'schedule.json');
