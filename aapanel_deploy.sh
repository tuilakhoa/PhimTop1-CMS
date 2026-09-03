#!/bin/bash
# Script dọn dẹp chạy tự động trên aaPanel / cPanel sau khi Git Pull
# Bạn có thể copy nội dung này dán vào phần "Shell Script" / "Post-pull Hook" của aaPanel

echo "Bắt đầu dọn dẹp mã nguồn App khỏi Web Server..."

# Xoá toàn bộ thư mục Flutter App vì Web Server không cần dùng tới
if [ -d "phimtop1_flutter" ]; then
    rm -rf phimtop1_flutter
    echo "Đã xoá thư mục phimtop1_flutter"
fi

# Xoá các file log và file rác khác
rm -f clean_movies.php
rm -f GEMINI.md
rm -f README.md

# Chặn Git theo dõi lại sự thay đổi của các file đã xoá (Ngăn Git khôi phục lại khi pull)
# Lệnh sparse-checkout giúp Git chỉ pull những file cần thiết cho Web
git sparse-checkout init --cone
git sparse-checkout set "/*" "!phimtop1_flutter" "!GEMINI.md"

echo "Dọn dẹp hoàn tất. Web Server đã được tối ưu dung lượng!"
