    <!-- Documentation Section -->
    <section id="docs" class="section" style="background: #070707; border-top: 1px solid var(--color-border);">
        <div class="container">
            <div class="text-center" style="margin-bottom: 64px;">
                <h2 class="title-lg">Tài Liệu Hướng Dẫn</h2>
                <p class="subtitle">Khám phá các hướng dẫn cài đặt và tích hợp tính năng cho hệ thống của bạn.</p>
            </div>
            
            <div class="grid-3 docs-grid">
                <?php
                $guidesDir = __DIR__ . '/../guides/';
                if (is_dir($guidesDir)) {
                    $files = array_diff(scandir($guidesDir), array('.', '..'));
                    $count = 1;
                    foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                            $slug = pathinfo($file, PATHINFO_FILENAME);
                            $content = file_get_contents($guidesDir . $file);
                            
                            // Extract title from h2 tag
                            $title = 'Bài viết hướng dẫn';
                            if (preg_match('/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches)) {
                                $title = strip_tags($matches[1]);
                            }
                            
                            // Extract description from subtitle p tag
                            $desc = 'Xem chi tiết hướng dẫn cấu hình và cài đặt.';
                            if (preg_match('/<p class="subtitle"[^>]*>(.*?)<\/p>/is', $content, $matches)) {
                                $desc = strip_tags($matches[1]);
                            }
                            
                            echo '<div class="doc-card step-' . $count . '">';
                            echo '<div class="doc-card-glow"></div>';
                            echo '<div class="doc-number">' . $count . '</div>';
                            echo '<h3 class="title-md">' . htmlspecialchars($title) . '</h3>';
                            echo '<p class="text-gray-400" style="margin-bottom: 1.5rem;">' . htmlspecialchars($desc) . '</p>';
                            echo '<a href="guide.php?slug=' . urlencode($slug) . '" class="btn btn-outline" style="width: 100%; justify-content: center; padding: 10px;">Đọc hướng dẫn <i data-lucide="arrow-right"></i></a>';
                            echo '</div>';
                            
                            $count++;
                        }
                    }
                }
                ?>
            </div>
        </div>
    </section>
