# Hướng dẫn Triển khai lên CPanel / Hosting

Đây là ứng dụng **PHP Thuần 100%**. Nó cực kỳ đơn giản để cài đặt trên CPanel hoặc bất kỳ dịch vụ Hosting nào hỗ trợ PHP.

## Cách Cài Đặt
1. **Nén Mã Nguồn**: Trên máy tính của bạn, hãy chọn tất cả các file trong thư mục này (bao gồm `.htaccess`, `index.php`, thư mục `includes/`, `assets/`, v.v.) và nén lại thành 1 file `.zip`.
2. **Upload lên Hosting**:
   - Đăng nhập vào CPanel.
   - Mở **File Manager** (Trình Quản lý Tệp).
   - Truy cập vào thư mục `public_html` (hoặc thư mục gốc của domain bạn muốn cài).
   - Tải file `.zip` vừa nén lên và nhấn **Extract** (Giải nén).
3. **Cài Đặt CSDL (MySQL)**:
   - Truy cập vào tên miền của bạn (ví dụ: `https://phim.domain.com`).
   - Hệ thống sẽ nhận thấy chưa có file `config.json` nên sẽ tự động chuyển hướng bạn tới trang cài đặt (`/setup.php`).
   - Hãy nhập thông tin Database MySQL (Host, Tên DB, User, Pass) mà bạn đã tạo trên Hosting.
   - Nhấn Cài Đặt. Hệ thống sẽ tự động tạo cấu trúc bảng và file cấu hình `config.json`.

*(Không cần quan tâm về Node.js, PM2 hay npm build nữa!)*
