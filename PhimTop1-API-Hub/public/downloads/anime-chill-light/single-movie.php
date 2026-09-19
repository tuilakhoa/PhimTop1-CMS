<?php get_header(); ?>
<?php 
    $is_watching = isset($_GET['watch']) && $_GET['watch'] == 'true';
    $ep = isset($_GET['ep']) ? $_GET['ep'] : '';
?>
    <div class="mt-4 mb-8">
        <?php if (have_posts()) : while (have_posts()) : the_post(); 
            $thumb = get_post_meta(get_the_ID(), 'thumb_url', true);
            $origin_name = get_post_meta(get_the_ID(), 'origin_name', true);
            $year = get_post_meta(get_the_ID(), 'year', true);
            $type = get_post_meta(get_the_ID(), 'type', true);
            $slug = get_post_field('post_name', get_post());
            $quality = get_post_meta(get_the_ID(), 'quality', true) ?: 'FHD';
            $lang = get_post_meta(get_the_ID(), 'lang', true) ?: 'Vietsub';
        ?>
            
            <?php if ($is_watching): ?>
                <!-- TRANG XEM PHIM -->
                <div class="bg-black border border-gray-800 rounded-xl overflow-hidden shadow-2xl mb-6">
                    <div class="aspect-video w-full bg-[#0a0a0a] flex items-center justify-center relative">
                        <div class="text-center p-8">
                            <i class="fas fa-play-circle text-6xl text-kk-red mb-4 opacity-80"></i>
                            <h3 class="text-xl text-white font-bold mb-2">Trình Phát Video (Demo)</h3>
                            <p class="text-gray-400 text-sm mb-4">Để lấy link stream m3u8 thực tế, theme sẽ gọi API chi tiết:</p>
                            <code class="text-blue-400 bg-blue-400/10 px-3 py-1.5 rounded-lg text-sm block">http://103.72.97.151:3000/api/phim/<?php echo esc_html($slug); ?></code>
                        </div>
                    </div>
                    <div class="p-4 bg-[#181a20] flex items-center justify-between border-t border-gray-800">
                        <h1 class="text-xl font-bold text-white"><?php the_title(); ?> <span class="text-gray-400 text-base font-normal"> - Tập Đang Xem</span></h1>
                        <a href="<?php the_permalink(); ?>" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-bold rounded-lg transition"><i class="fas fa-info-circle mr-2"></i>Thông tin phim</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TRANG THÔNG TIN -->
            <div class="bg-[#181a20] rounded-xl border border-gray-800 overflow-hidden shadow-xl">
                <div class="relative h-64 md:h-80 w-full overflow-hidden">
                    <div class="absolute inset-0 bg-black/60 z-10"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#181a20] via-transparent to-transparent z-20"></div>
                    <?php if ($thumb): ?>
                        <img src="<?php echo esc_url($thumb); ?>" class="w-full h-full object-cover blur-sm opacity-50" />
                    <?php endif; ?>
                </div>
                
                <div class="px-6 pb-8 relative z-30 -mt-32 md:-mt-40 flex flex-col md:flex-row gap-6 md:gap-8">
                    <div class="w-40 md:w-56 shrink-0 mx-auto md:mx-0">
                        <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>" class="w-full rounded-xl shadow-2xl border-4 border-[#181a20]" />
                        <?php endif; ?>
                        
                        <?php if (!$is_watching): ?>
                            <a href="?watch=true" class="mt-4 w-full block text-center bg-kk-red hover:bg-rose-600 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-lg shadow-rose-500/30">
                                <i class="fas fa-play mr-2"></i> XEM PHIM
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1 text-center md:text-left mt-4 md:mt-20">
                        <h1 class="text-3xl md:text-4xl font-black text-white mb-2"><?php the_title(); ?></h1>
                        <h2 class="text-lg text-gray-400 font-medium mb-6"><?php echo esc_html($origin_name); ?> (<?php echo esc_html($year); ?>)</h2>
                        
                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-6">
                            <span class="px-3 py-1 bg-gray-800 text-gray-200 text-sm font-semibold rounded-md border border-gray-700"><?php echo esc_html($quality); ?></span>
                            <span class="px-3 py-1 bg-gray-800 text-gray-200 text-sm font-semibold rounded-md border border-gray-700"><?php echo esc_html($lang); ?></span>
                            <span class="px-3 py-1 bg-gray-800 text-gray-200 text-sm font-semibold rounded-md border border-gray-700 uppercase"><?php echo esc_html($type); ?></span>
                        </div>
                        
                        <div class="text-gray-300 leading-relaxed text-sm md:text-base mb-6">
                            <h3 class="text-white font-bold mb-2 border-b border-gray-700 pb-2 inline-block">Nội Dung Phim</h3>
                            <div class="mt-2 text-justify">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php endwhile; else: ?>
            <div class="p-10 text-center text-gray-500">Phim không tồn tại.</div>
        <?php endif; ?>
    </div>
<?php get_footer(); ?>
