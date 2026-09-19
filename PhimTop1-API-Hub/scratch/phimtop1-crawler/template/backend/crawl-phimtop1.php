<?php
$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
?>
<div class="wrap">
    <nav class="nav-tab-wrapper">
        <a href="?page=phimtop1-manager-crawl-phimtop1" class="nav-tab <?php if ($tab === null): ?>nav-tab-active<?php endif; ?>">Thủ
            công</a>
        <a href="?page=phimtop1-manager-crawl-phimtop1&tab=schedule"
           class="nav-tab <?php if ($tab === 'schedule'): ?>nav-tab-active<?php endif; ?>">Tự động</a>
    </nav>
    <div class="tab-content">
        <?php


        switch ($tab) :
            case 'schedule':
                $crawl_movie_settings = json_decode(get_option(CRAWL_PHIMTOP1_OPTION_SETTINGS, '[]'));
                ?>

                <div class="crawl_page">
                    <div class="postbox">
                        <div class="inside">
                            <b>Hưỡng dẫn cấu hình crontab</b>
                            <div>
                                <p>
                                    Thời gian thực hiện (<a href="https://crontab.guru/" target="_blank">Xem thêm</a>)
                                </p>
                                <p>
                                    Cấu hình crontab: <code><i style="color:blueviolet">*/10 * * * *</i> cd <i
                                                style="color:blueviolet">/path/to/</i>wp-content/plugins/xphim-plugin/ &&
                                        php -q schedule-phimtop1.php <i style="color:blueviolet">{secret_key}</i></code>
                                </p>
                                <p>
                                    Ví dụ:
                                    <br/>
                                    Mỗi 5 phút: <code>*/5 * * * * cd <?php echo OFIM_PLUGIN_PATCH; ?> && php -q
                                        schedule-phimtop1.php <i
                                                style="color:blueviolet"><?php echo get_option(CRAWL_PHIMTOP1_OPTION_SECRET_KEY, ''); ?></i></code>
                                    <br/>
                                    Mỗi 10 phút: <code>*/10 * * * * cd <?php echo OFIM_PLUGIN_PATCH; ?> && php -q
                                        schedule.php <i
                                                style="color:blueviolet"><?php echo get_option(CRAWL_PHIMTOP1_OPTION_SECRET_KEY, ''); ?></i></code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="crawl_page">
                    <div class="postbox">
                        <div class="inside">
                            <b>Cấu hình tự động</b>
                            <div>
                                <p>
                                    Secret Key: <input type="text" name="crawl_movie_schedule_secret"
                                                       value="<?php echo get_option(CRAWL_PHIMTOP1_OPTION_SECRET_KEY, ''); ?>">
                                    <button id="save_crawl_movie_schedule_secret" class="button">Lưu mật khẩu</button>
                                </p>
                            </div>
                            <div>
                                <p>
                                    Kích hoạt:
                                    <input type="checkbox" class="wppd-ui-toggle" id="crawl_movie_schedule_enable"
                                           name="crawl_movie_schedule_enable"
                                           value=""
                                        <?php echo (json_decode(file_get_contents(CRAWL_PHIMTOP1_PATH_SCHEDULE_JSON))->enable === true) ? 'checked' : ''; ?>
                                    >
                                </p>
                            </div>
                            <div>
                                <p>Trạng
                                    thái: <?php echo (int)get_option(CRAWL_PHIMTOP1_OPTION_RUNNING, 0) === 1 ? "<code style='color: blue'>Đang chạy...</code>" : "<code style='color: chocolate'>Dừng</code>"; ?></p>
                            </div>
                            <div>
                                <p>Page đầu: <code
                                            style="color: blue"><?php echo $crawl_movie_settings->pageFrom ?? "N/A"; ?></code>
                                </p>
                                <p>Page cuối: <code
                                            style="color: blue"><?php echo $crawl_movie_settings->pageTo ?? "N/A"; ?></code></p>
                            </div>

                            <div class="notice notice-success">
                                <p>File logs: <code
                                            style="color:brown"><?php echo $schedule_log['log_filename']; ?></code></p>
                                <textarea style="width: 100%" rows="10" id="schedule_log" class=""
                                          readonly><?php echo $schedule_log['log_data']; ?></textarea>
                            </div>

                        </div>
                    </div>
                </div>
                <?php
                break;
            case 'about':
                ?>
                <div class="crawl_page">
                    <div class="postbox">
                        <div class="inside">
                            PHIMTOP1 (PhimTop1.vip) là kho dữ liệu phim tập trung, cập nhật nhanh, chất lượng cao, ổn
                            định. Tốc độ phát cực nhanh với đường truyền băng thông cao, đảm bảo đáp ứng được
                            lượng xem phim trực tuyến lớn. Đồng thời giúp nhà phát triển website phim giảm thiểu chi phí
                            của các dịch vụ lưu trữ và stream. <br/>
                            - Hàng ngày chạy tools tầm 10 đến 20 pages đầu (tùy số lượng phim được cập nhật trong ngày)
                            để update tập mới hoặc thêm phim mới!<br/>
                            - Trộn link vài lần để thay đổi thứ tự crawl & update. Giúp tránh việc quá giống nhau về
                            content của các website!<br/>
                            - API được cung cấp miễn phí: <a href="<?php echo esc_url( PHIMTOP1_SITE_URL ); ?>" target="_blank"><?php echo esc_html( PHIMTOP1_SITE_URL ); ?></a>
                            <br/>
                            - Tham gia trao đổi tại: <a href="https://t.me/hotrokhmovie" target="_blank">https://t.me/hotrokhmovie</a>
                            <br/>
                        </div>
                    </div>
                </div>
                <?php
                break;
            default:
                $crawl_movie_settings = json_decode(get_option(CRAWL_PHIMTOP1_OPTION_SETTINGS, '[]'));
                ?>
                <div class="crawl_main">
                    <div class="crawl_filter notice notice-info">
                        <div class="filter_title"><strong>Ưu tiên tiếng</strong></div>
                        <div class="filter_item">
                            <label><input type="radio" class="" name="filter_language" value="vi" checked> Tiếng việt</label>
                            <label><input type="radio" class="" name="filter_language" value="en"> Tiếng anh</label>
                        </div>
                        <div class="filter_title"><strong>Bỏ qua thể loại (PhimTop1)</strong></div>
                        <div class="filter_item">
                            <?php
                            $saved_genres_phimtop1 = array();
                            if (defined('OFIM_CACHE_FILTER_GENRE_PHIMTOP1') && file_exists(OFIM_CACHE_FILTER_GENRE_PHIMTOP1)) {
                                $raw = @file_get_contents(OFIM_CACHE_FILTER_GENRE_PHIMTOP1);
                                if ($raw !== false) {
                                    $dec = json_decode($raw, true);
                                    if (!empty($dec['filterGenrePhimTop1']) && is_array($dec['filterGenrePhimTop1'])) {
                                        $saved_genres_phimtop1 = $dec['filterGenrePhimTop1'];
                                    }
                                }
                            }
                            if (empty($saved_genres_phimtop1) && isset($crawl_movie_settings->filterGenrePhimTop1) && is_array($crawl_movie_settings->filterGenrePhimTop1)) {
                                $saved_genres_phimtop1 = $crawl_movie_settings->filterGenrePhimTop1;
                            }
                            if (!empty($genres) && is_array($genres)) {
                                foreach ($genres as $g) {
                                    $name = isset($g['name_vi']) ? $g['name_vi'] : $g['name_en'];
                                    $checked = in_array($g['code'], $saved_genres_phimtop1) ? ' checked' : '';
                                    echo '<label><input type="checkbox" class="" name="filter_genre_phimtop1[]" value="' . esc_attr($g['code']) . '"' . $checked . '> ' . esc_html($name) . '</label>';
                                }
                            }
                            ?>
                        </div>
                        <div class="filter_title"><strong>Bỏ qua quốc gia (PhimTop1)</strong></div>
                        <div class="filter_item">
                            <?php
                            $saved_regions_phimtop1 = array();
                            if (defined('OFIM_CACHE_FILTER_GENRE_PHIMTOP1') && file_exists(OFIM_CACHE_FILTER_GENRE_PHIMTOP1)) {
                                $raw = @file_get_contents(OFIM_CACHE_FILTER_GENRE_PHIMTOP1);
                                if ($raw !== false) {
                                    $dec = json_decode($raw, true);
                                    if (!empty($dec['filterRegionPhimTop1']) && is_array($dec['filterRegionPhimTop1'])) {
                                        $saved_regions_phimtop1 = $dec['filterRegionPhimTop1'];
                                    }
                                }
                            }
                            if (empty($saved_regions_phimtop1) && isset($crawl_movie_settings->filterRegionPhimTop1) && is_array($crawl_movie_settings->filterRegionPhimTop1)) {
                                $saved_regions_phimtop1 = $crawl_movie_settings->filterRegionPhimTop1;
                            }
                            if (!empty($regions) && is_array($regions)) {
                                foreach ($regions as $r) {
                                    $name = isset($r['name_vi']) ? $r['name_vi'] : $r['name_en'];
                                    $checked = in_array($r['code'], $saved_regions_phimtop1) ? ' checked' : '';
                                    echo '<label><input type="checkbox" class="" name="filter_region_phimtop1[]" value="' . esc_attr($r['code']) . '"' . $checked . '> ' . esc_html($name) . '</label>';
                                }
                            }
                            ?>
                        </div>
                        <div class="filter_title"><strong>Hình ảnh</strong></div>
                        <div>
                            <label> <input type="checkbox"
                                           name="crawl_resize_size_thumb" <?php if(oIsset($crawl_movie_settings, 'crawl_resize_size_thumb', 'off') == 'on') {
                                    echo 'checked';
                                } ?> />Tải & Resize Thumb => </label>
                            <label> Width (px): <input style="max-width: 80px" type="number" name="crawl_resize_size_thumb_w" value="<?php echo oIsset($crawl_movie_settings, 'crawl_resize_size_thumb_w', 0); ?>"/></label>
                            <label> Height (px): <input style="max-width: 80px" type="number" name="crawl_resize_size_thumb_h" value="<?php echo oIsset($crawl_movie_settings, 'crawl_resize_size_thumb_h', 0); ?>"/></label>
                        </div>
                        <div style="margin-top: 5px">
                            <label> <input type="checkbox"
                                           name="crawl_resize_size_poster" <?php if(oIsset($crawl_movie_settings, 'crawl_resize_size_poster', 'off') == 'on') {
                                    echo 'checked';
                                } ?> />Tải & Resize Poster =></label>
                            <label> Width (px): <input style="max-width: 80px" type="number" name="crawl_resize_size_poster_w" value="<?php echo oIsset($crawl_movie_settings, 'crawl_resize_size_poster_w', 0); ?>"/></label>
                            <label> Height (px): <input style="max-width: 80px" type="number" name="crawl_resize_size_poster_h" value="<?php echo oIsset($crawl_movie_settings, 'crawl_resize_size_poster_h', 0); ?>"/></label>
                        </div>
                        <div style="margin-top: 5px">
                            <label> <input type="checkbox"
                                           name="crawl_convert_webp" <?php if(oIsset($crawl_movie_settings, 'crawl_convert_webp', 'off') == 'on') {
                                    echo 'checked';
                                } ?> />Lưu định dạng webp</label>
                        </div>
                        <p>

                        <div id="save_crawl_movie_schedule" class="button">Lưu cấu hình</div>
                        </p>
                    </div>

                    <div class="crawl_page">
                        Page Crawl: From <input type="number" name="page_from" value="">
                        To <input type="number" name="page_to" value="">
                    </div>

                    <div class="crawl_page">
                        Url API <input type="text" id="url_api"
                                       value="<?php echo esc_attr( API_DOMAIN . '/danh-sach' ); ?>" style="width: 70%">
                        <div id="get_list_movies" class="primary">Get List Movies</div>
                    </div>

                    <div class="crawl_page">
                        Wait Timeout Random: From <input type="number" name="timeout_from" value="">(ms) -
                        To <input type="number" name="timeout_to" value=""> (ms)
                    </div>

                    <div class="crawl_page">
                        <div style="display: none" id="msg" class="notice notice-success">
                            <p id="msg_text"></p>
                        </div>
                        <textarea style="width: 100%" rows="10" id="result_list_movies" class="list_movies"></textarea>
                        <div id="roll_movies" class="roll">Trộn Link</div>
                        <div id="crawl_movies" class="primary">Crawl Movies</div>

                        <div style="display: none;" id="result_success" class="notice notice-success">
                            <p><strong>Phim mới (thành công)</strong> — chỉ các bộ vừa được tạo bài mới trên site.</p>
                            <textarea style="width: 100%" rows="10" id="list_crawl_success"></textarea>
                        </div>

                        <div style="display: none;" id="result_error" class="notice notice-error">
                            <p><strong>Bỏ qua / đã có / lỗi</strong> — phim đã tồn tại (không đổi hoặc đã cập nhật), thể loại loại trừ, hoặc lỗi kết nối/API.</p>
                            <textarea style="width: 100%" rows="10" id="list_crawl_error"></textarea>
                        </div>
                    </div>
                </div>


                <?php
                break;
        endswitch;
        ?>
    </div>
</div>