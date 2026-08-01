# PhimTop1 CMS Landing Page

Đây là bộ mã nguồn HTML/CSS/JS tĩnh dành cho trang giới thiệu (Landing Page) của hệ thống PhimTop1 CMS. Bạn có thể sử dụng mã nguồn này để deploy lên domain như `cms.phimtop1.asia`.

## Đặc điểm nổi bật
- Giao diện cao cấp, phong cách Dark Mode hiện đại (màu Đen/Đỏ Netflix).
- Không yêu cầu framework nặng nề, tốc độ tải trang siêu tốc.
- Sử dụng **Tailwind CSS** (qua CDN) và **Lucide Icons** cho các icon SVG mượt mà.
- Phản hồi tốt trên mọi kích thước màn hình (Responsive Design).

## Cấu trúc thư mục
- `index.html`: Giao diện chính của Landing Page.
- `assets/css/style.css`: File định dạng CSS tuỳ chỉnh (hiệu ứng hover, glowing, animation, v.v.).

## Hướng dẫn cài đặt & Deploy
Bản chất của mã nguồn này là các file tĩnh (Static Files). Việc deploy vô cùng đơn giản:

### Cách 1: Sử dụng CPanel/DirectAdmin
1. Nén toàn bộ file trong thư mục này thành `landing.zip`.
2. Truy cập File Manager trên Cpanel.
3. Upload `landing.zip` vào thư mục gốc của tên miền `cms.phimtop1.asia` (ví dụ: `/public_html/cms.phimtop1.asia/`).
4. Giải nén (Extract) file `landing.zip`.
5. Truy cập `https://cms.phimtop1.asia` để kiểm tra kết quả.

### Cách 2: Sử dụng VPS (Nginx)
Nếu bạn dùng aapanel hoặc cấu hình Nginx thủ công, chỉ cần copy thư mục này vào đường dẫn web root (ví dụ `/www/wwwroot/cms.phimtop1.asia`) và trỏ config Nginx về thư mục đó.

## Tuỳ chỉnh thêm
- Để thay đổi **Ảnh minh hoạ Dashboard**, bạn tìm đến đoạn `<div class="aspect-[16/9]...` trong `index.html` và thêm tag `<img src="link_anh.png" />`.
- Để thay đổi **Link Telegram**, tìm `href="https://t.me/your_telegram_group"` trong thẻ `<a>` ở gần phần cuối trang `index.html` và sửa lại thành group của bạn.
