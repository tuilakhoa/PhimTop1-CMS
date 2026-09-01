# Tài Liệu: Vị trí các tệp Build (Windows, APK, Linux, Fedora)

Dưới đây là danh sách và vị trí lưu trữ các tập tin sau khi được biên dịch (build) cho các nền tảng khác nhau của dự án **PhimTop1 Flutter**.

---

### 1. 📱 Ứng Dụng Android (APK)
Theo quy tắc cấu hình của dự án, file APK sau khi build sẽ luôn được lưu với định dạng tên `<app_name>-<platform>.apk` (ví dụ: `phimtop1-mobile.apk`).

- **Thư mục chứa file:**
  `/home/khoa/Bản tải về/phimtop1cms/phimtop1_flutter/build/app/outputs/flutter-apk/`
- **Tên file thành phẩm:**
  `phimtop1-mobile.apk`

---

### 2. 🐧 Ứng Dụng Linux (Bản Build nhị phân chuẩn)
Đây là bản build gốc dạng nhị phân (có thể chạy trực tiếp trên hầu hết các bản phân phối Linux mà không cần cài đặt qua trình quản lý gói).

- **Thư mục chứa bản build:**
  `/home/khoa/Bản tải về/phimtop1cms/phimtop1_flutter/build/linux/x64/release/bundle/`
- **Tên file thực thi (Executable):**
  `phimtop1_flutter` (Nằm trong thư mục `bundle`)

---

### 3. 🎩 Ứng Dụng Fedora (Gói cài đặt RPM)
Các gói cài đặt dành riêng cho Fedora (.rpm) mà hệ thống vừa tạo ra để cài đặt thông qua `dnf`. Chúng ta có 2 phiên bản:

- **Bản sử dụng giao diện Fluent UI (Bản Premium V5):**
  `/home/khoa/Bản tải về/phimtop1cms/phimtop1_flutter/phimtop1-fluent-v5-premium.rpm`

---

### 4. 🪟 Ứng Dụng Windows
Nếu ứng dụng được build cho Windows (trong tương lai hoặc đã build trên máy tính Windows), các file thành phẩm thường sẽ nằm ở đường dẫn mặc định sau:

- **Thư mục chứa bản build:**
  `/home/khoa/Bản tải về/phimtop1cms/phimtop1_flutter/build/windows/x64/runner/Release/`
- **Tên file thực thi:**
  `phimtop1_flutter.exe` (kèm theo các file `.dll` và thư mục `data`)

> **Lưu ý:** Việc build tệp `.exe` cho Windows thông thường yêu cầu phải chạy lệnh `flutter build windows` trên một máy tính sử dụng hệ điều hành Windows thật (do cần công cụ Visual Studio Build Tools).
