<?php
// Tên Plugin: AI Content Rewriter
// Phiên bản: 1.0.0

add_filter('admin_menu_groups', function($groups) {
    if (!isset($groups['Hệ Thống'])) {
        $groups['Hệ Thống'] = [];
    }
    // Chèn menu Cấu Hình AI
    $groups['Hệ Thống']['plugin_ai_settings'] = [
        'icon' => 'bot',
        'title' => 'Cấu Hình AI'
    ];
    return $groups;
});

// Điều hướng trang admin
add_filter('admin_page_file', function($file, $page) {
    if ($page === 'plugin_ai_settings') {
        return __DIR__ . '/admin_page.php';
    }
    return $file;
});

// Thêm nút AI vào trang Edit Movie
add_action('admin_movie_content_buttons', function($movie) {
    echo '<button type="button" onclick="rewriteWithAI()" id="btn-rewrite-ai" class="text-xs bg-purple-600/20 text-purple-400 hover:bg-purple-600/30 px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-colors border border-purple-500/20" title="Viết lại bằng AI">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
          </button>';
});

// Thêm JS logic vào footer admin
add_action('admin_footer', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'edit_movie') {
        echo "<script>
        function rewriteWithAI() {
            const textarea = document.getElementById('movie-content');
            const btn = document.getElementById('btn-rewrite-ai');
            if (!textarea || !btn) return;
            
            const originalContent = textarea.value.trim();
            if (!originalContent) {
                alert('Vui lòng nhập nội dung trước khi viết lại.');
                return;
            }
            
            const originalBtnHTML = btn.innerHTML;
            btn.innerHTML = '<i data-lucide=\"loader\" class=\"w-4 h-4 animate-spin\"></i>';
            btn.disabled = true;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            fetch('/plugins/ai-rewriter/api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'content=' + encodeURIComponent(originalContent)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status && data.result) {
                    textarea.value = data.result;
                    textarea.classList.add('ring-2', 'ring-green-500', 'border-green-500');
                    setTimeout(() => {
                        textarea.classList.remove('ring-2', 'ring-green-500', 'border-green-500');
                    }, 1000);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể kết nối AI.'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối đến máy chủ.');
            })
            .finally(() => {
                btn.innerHTML = originalBtnHTML;
                btn.disabled = false;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }
        </script>";
    }
});
