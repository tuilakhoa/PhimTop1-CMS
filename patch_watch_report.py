import re

with open('themes/phimhayok/watch.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the button to add onclick and an ID if needed
old_btn = """<button class="flex items-center px-4 py-2 bg-[#1a1a1a] hover:bg-[#252525] text-gray-300 hover:text-white text-sm font-medium rounded  border border-gray-800">
                    <i data-lucide="flag" class="w-4 h-4 mr-2 text-red-500"></i> Báo lỗi
                </button>"""
new_btn = """<button onclick="reportMovieError()" class="flex items-center px-4 py-2 bg-[#1a1a1a] hover:bg-[#252525] text-gray-300 hover:text-white text-sm font-medium rounded  border border-gray-800">
                    <i data-lucide="flag" class="w-4 h-4 mr-2 text-red-500"></i> Báo lỗi
                </button>"""

if old_btn in content:
    content = content.replace(old_btn, new_btn)

# Add the reportMovieError() Javascript function
js_func = """function reportMovieError() {
    <?php if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])): ?>
        Swal.fire({
            title: 'Yêu cầu đăng nhập',
            text: 'Bạn cần đăng nhập để gửi báo lỗi!',
            icon: 'warning',
            background: '#111',
            color: '#fff',
            confirmButtonColor: '#eab308'
        });
        return;
    <?php endif; ?>

    Swal.fire({
        title: 'Báo lỗi phim',
        input: 'textarea',
        inputLabel: 'Hãy mô tả lỗi bạn gặp phải (mất tiếng, sai sub, video hỏng...)',
        inputPlaceholder: 'Nhập nội dung báo lỗi...',
        background: '#111',
        color: '#fff',
        showCancelButton: true,
        confirmButtonText: 'Gửi báo lỗi',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        inputValidator: (value) => {
            if (!value) {
                return 'Nội dung không được để trống!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var msg = "Phim: <?= htmlspecialchars($movie['name']) ?> (<?= htmlspecialchars($movie['slug']) ?>) - Tập: <?= htmlspecialchars($currentEpName) ?> - Lỗi: " + result.value;
            fetch('/api/v1/feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Thành công',
                        text: 'Cảm ơn bạn đã báo lỗi. Admin sẽ kiểm tra và khắc phục sớm nhất!',
                        icon: 'success',
                        background: '#111',
                        color: '#fff',
                        confirmButtonColor: '#eab308'
                    });
                } else {
                    Swal.fire('Lỗi!', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Lỗi!', 'Có sự cố xảy ra, vui lòng thử lại sau.', 'error');
            });
        }
    });
}"""

# Insert the JS function before the closing </script>
# Actually, watch.php has a large <script> block at the end.
if "function reportMovieError" not in content:
    content = content.replace("function toggleWatchPartyDialog", js_func + "\n\nfunction toggleWatchPartyDialog")

with open('themes/phimhayok/watch.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Patched watch.php")
