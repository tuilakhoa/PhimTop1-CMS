<?php include __DIR__ . '/header.php'; ?>

<!-- Hero Section -->
<?php if (!empty($movies)): $featured = $movies[0]; ?>
<div class="hero">
    <div class="hero-bg">
        <img src="<?= htmlspecialchars(getPhimImgUrl(!empty($featured['poster_url']) ? $featured['poster_url'] : ($featured['thumb_url'] ?? ''))) ?>" alt="Hero Background">
    </div>
    <div class="hero-overlay"></div>
    <div class="container relative z-10">
        <div class="hero-content">
            <h1 class="hero-title"><?= htmlspecialchars($featured['name'] ?? '') ?></h1>
            <p class="hero-desc">
                <?= htmlspecialchars(strip_tags(!empty($featured['content']) ? $featured['content'] : 'Phim chất lượng cao, cập nhật mới nhất. Khám phá ngay.')) ?>
            </p>
            <div class="hero-actions">
                <a href="/<?= $settings["slugMovie"] ?? "phim" ?>/<?= urlencode($featured['slug']) ?>" class="btn-play">
                    <i data-lucide="play" style="fill: black;"></i> Phát Ngay
                </a>
                <button class="btn-info">
                    <i data-lucide="info"></i> Chi tiết
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="container" style="margin-top: -60px; position: relative; z-index: 20;">
    
    <!-- Phim Đề Cử / Mới Nhất -->
    <section class="section">
        <div class="section-header">
            <h2 class="section-title">Phim Mới Nổi Bật</h2>
            <a href="/<?= $settings["slugList"] ?? "danh-sach" ?>/phim-moi" class="section-link">Xem tất cả <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i></a>
        </div>
        
        <div class="movie-grid">
            <?php foreach (array_slice($movies, 0, 12) as $movie): 
                $thumb = getPhimImgUrl(!empty($movie['thumb_url']) ? $movie['thumb_url'] : ($movie['poster_url'] ?? ''));
            ?>
            <div class="movie-card" onclick="window.location.href='/<?= $settings["slugMovie"] ?? "phim" ?>/<?= htmlspecialchars($movie['slug']) ?>'">
                <div class="movie-poster-wrap">
                    <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($movie['name']) ?>" class="movie-poster" loading="lazy">
                    <div class="movie-overlay"></div>
                    <div class="movie-play-btn"><i data-lucide="play" style="fill: black;"></i></div>
                    
                    <div class="movie-tags">
                        <?php if (!empty($movie['quality'])): ?>
                            <span class="tag"><?= htmlspecialchars($movie['quality']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($movie['lang'])): ?>
                            <span class="tag lang"><?= htmlspecialchars($movie['lang']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($movie['episode_current'])): ?>
                        <div class="movie-ep"><?= htmlspecialchars($movie['episode_current']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="movie-info">
                    <h3 class="movie-name" title="<?= htmlspecialchars($movie['name']) ?>"><?= htmlspecialchars($movie['name']) ?></h3>
                    <p class="movie-origin">
                        <?= htmlspecialchars($movie['origin_name'] ?? '') ?> 
                        <?= !empty($movie['year']) ? '(' . $movie['year'] . ')' : '' ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<?php include __DIR__ . '/footer.php'; ?>
