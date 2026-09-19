<?php get_header(); ?>
    <div class="container">
        <h2 style="margin-bottom: 24px; color: #fff;">Kho Phim</h2>
        <div class="movie-grid">
        <?php
        if (have_posts()) : while (have_posts()) : the_post();
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
        <?php endwhile; else: ?>
            <p style="color: #8b949e;">Chưa có dữ liệu phim nào trong kho.</p>
        <?php endif; ?>
        </div>
        
        <div style="margin-top: 40px; text-align: center; color: #fff;">
            <?php 
                echo paginate_links(array(
                    'prev_text' => '« Trước',
                    'next_text' => 'Sau »'
                ));
            ?>
        </div>
    </div>
<?php get_footer(); ?>
