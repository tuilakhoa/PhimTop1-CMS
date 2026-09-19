<?php get_header(); ?>
    <div class="max-w-[1440px] mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8 border-b border-gray-800 pb-4">
            <h2 class="text-2xl font-bold text-white border-l-4 border-kk-red pl-3 uppercase">
                <?php is_tax() ? single_term_title() : post_type_archive_title(); ?>
            </h2>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
        <?php
        if (have_posts()) : while (have_posts()) : the_post();
            $thumb = get_post_meta(get_the_ID(), 'thumb_url', true);
            $year = get_post_meta(get_the_ID(), 'year', true);
            $quality = get_post_meta(get_the_ID(), 'quality', true) ?: 'HD';
            $lang = get_post_meta(get_the_ID(), 'lang', true) ?: 'Vietsub';
            $episode_current = get_post_meta(get_the_ID(), 'episode_current', true) ?: '';
        ?>
            <div class="card-movie group bg-[#181a20] rounded-xl overflow-hidden border border-gray-800 hover:border-gray-600 flex flex-col h-full relative">
                <a href="<?php the_permalink(); ?>" class="block relative aspect-[2/3] overflow-hidden">
                    <?php if ($thumb): ?>
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title(); ?>" class="w-full h-full object-cover transition duration-300 group-hover:scale-110 group-hover:opacity-80" loading="lazy" />
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-800 flex items-center justify-center text-gray-500"><i class="fas fa-image text-3xl"></i></div>
                    <?php endif; ?>
                    
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        <span class="bg-kk-red text-white text-[10px] font-bold px-2 py-0.5 rounded shadow"><?php echo esc_html($quality); ?></span>
                    </div>
                    <?php if ($episode_current): ?>
                    <div class="absolute top-2 right-2">
                        <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow"><?php echo esc_html($episode_current); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black via-black/80 to-transparent">
                        <div class="text-xs text-gray-300 mb-1"><?php echo esc_html($year); ?> • <?php echo esc_html($lang); ?></div>
                    </div>
                    
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="w-12 h-12 rounded-full bg-kk-red/90 flex items-center justify-center text-white shadow-lg shadow-kk-red/50">
                            <i class="fas fa-play ml-1"></i>
                        </div>
                    </div>
                </a>
                <div class="p-3 flex-1 flex flex-col justify-between">
                    <h3 class="text-[15px] font-bold text-white group-hover:text-kk-red transition line-clamp-2 leading-snug"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="col-span-full py-20 text-center">
                <i class="fas fa-folder-open text-4xl text-gray-700 mb-4"></i>
                <p class="text-gray-500 text-lg">Chưa có dữ liệu phim nào trong danh mục này.</p>
            </div>
        <?php endif; ?>
        </div>
        
        <div class="mt-12 flex justify-center">
            <div class="inline-flex items-center gap-1 bg-[#181a20] p-1 rounded-lg border border-gray-800">
                <?php 
                    echo paginate_links(array(
                        'prev_text' => '<i class="fas fa-chevron-left"></i>',
                        'next_text' => '<i class="fas fa-chevron-right"></i>',
                        'type' => 'plain'
                    ));
                ?>
            </div>
        </div>
    </div>
<?php get_footer(); ?>
