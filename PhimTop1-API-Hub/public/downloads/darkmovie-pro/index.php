<?php get_header(); ?>
    <div class="container">
        <h2 style="margin-bottom: 24px; color: #fff;">Phim Mới Cập Nhật</h2>
        <div class="movie-grid">
        <?php
        // Query custom post type 'movie'
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array('post_type' => 'movie', 'posts_per_page' => 24, 'paged' => $paged);
        $query = new WP_Query($args);

        if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
            $thumb = get_post_meta(get_the_ID(), 'thumb_url', true);
            $year = get_post_meta(get_the_ID(), 'year', true);
        ?>
            <div class="movie-item">
                <a href="<?php the_permalink(); ?>">
                    <?php if ($thumb): ?>
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title(); ?>" loading="lazy" />
                    <?php else: ?>
                        <div style="width:100%; aspect-ratio:2/3; background:#30363d; border-radius:8px; margin-bottom:12px;"></div>
                    <?php endif; ?>
                    <h3><?php the_title(); ?></h3>
                </a>
                <span class="meta-tag"><?php echo esc_html($year); ?></span>
            </div>
        <?php endwhile; wp_reset_postdata(); else: ?>
            <p style="color: #8b949e;">Chưa có dữ liệu phim nào. Hãy dùng Plugin PhimTop1 Crawler để cào phim.</p>
        <?php endif; ?>
        </div>
        
        <div style="margin-top: 40px; text-align: center; color: #fff;">
            <?php 
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'prev_text' => '« Trước',
                    'next_text' => 'Sau »'
                ));
            ?>
        </div>
    </div>
<?php get_footer(); ?>
