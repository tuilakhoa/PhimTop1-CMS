# PhimTop1 CMS

Hệ thống quản trị và hiển thị website phim (Monolithic PHP), được thiết kế tối ưu tốc độ, SEO chuẩn Google và dễ dàng mở rộng.

## Yêu Cầu Hệ Thống (Prerequisites)
- PHP >= 8.0
- Extensions: `pdo_mysql`, `curl`, `gd`, `mbstring`
- Web Server: Apache hoặc Nginx
- Cơ sở dữ liệu: MySQL/MariaDB hoặc Firebase Firestore

---

## 1. Cấu Hình Nginx (Bắt buộc nếu dùng Nginx)
Nếu bạn sử dụng **Nginx** (ví dụ trên AAPanel, CyberPanel...), bạn cần thêm các cấu hình Rewrite Rules để các đường dẫn SEO (URL thân thiện) và Sitemap hoạt động trơn tru.

Dán đoạn cấu hình sau vào phần cấu hình `server {}` của domain (File Nginx Config):

```nginx
server {
    # Thay đổi port và server_name cho phù hợp
    listen 80;
    server_name phim.domain.com;
    root /www/wwwroot/phim.domain.com; 
    
    index index.html index.php;

    # Chặn truy cập trực tiếp vào file cấu hình và thư mục hệ thống
    location ~* (config\.json|includes/|temp/) {
        deny all;
    }

    location / {
        # SITEMAP PAGINATION RULE
        rewrite ^/sitemap-([0-9]+)\.xml$ /sitemap.php?page=$1 last;
        rewrite ^/sitemap\.xml$ /sitemap.php last;

        # SEO URL REWRITES
        rewrite ^/phim/([^/]+)/?$ /movie.php?slug=$1 last;
        rewrite ^/xem-phim/([^/]+)/([^/]+)/?$ /watch.php?slug=$1&ep=$2 last;
        rewrite ^/danh-sach/([^/]+)/?$ /category.php?type=$1 last;
        rewrite ^/the-loai/([^/]+)/?$ /category.php?slug=$1&type=the-loai last;
        rewrite ^/quoc-gia/([^/]+)/?$ /category.php?slug=$1&type=quoc-gia last;
        
        # ẨN ĐUÔI .PHP VÀ FALLBACK
        try_files $uri $uri/ $uri.php /index.php?$query_string;
    }

    # Cấu hình xử lý PHP (Tùy thuộc vào panel của bạn)
    location ~ \.php$ {
        include fastcgi_params;
        # AAPanel: include enable-php-81.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; 
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Chặn các file ẩn (e.g. .env, .git)
    location ~ /\. {
        deny all;
    }
}
```

---

## 2. Cấu Hình Apache (.htaccess)
Nếu bạn sử dụng **Apache** (Cpanel, DirectAdmin), file `.htaccess` đã được cung cấp sẵn ở thư mục gốc. Đảm bảo module `mod_rewrite` đã được bật.

---

## 3. Quy Tắc Bảo Mật Firebase Firestore (Firestore Rules)
Nếu bạn chọn cơ sở dữ liệu là Firestore thay vì MySQL, hệ thống PHP Backend sẽ sử dụng **Service Account** (Quyền Admin) để ghi dữ liệu, bỏ qua mọi rules.
Tuy nhiên, để bảo mật CSDL Firestore không bị ghi đè trái phép từ phía người dùng (Frontend/Client), bạn **phải** cấu hình Rules trên trang quản trị Firebase Console như sau:

Nội dung file `firestore.rules`:
```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    // Cho phép đọc tự do (hiển thị phim cho người dùng)
    // KHÔNG cho phép ghi từ Client (Chỉ PHP Backend có Service Account mới được ghi)
    match /{document=**} {
      allow read: if true;
      allow write: if false; 
    }
  }
}
```

**Cách áp dụng Rules:**
1. Truy cập [Firebase Console](https://console.firebase.google.com/)
2. Chọn dự án của bạn -> Firestore Database -> Thẻ **Rules** (Quy tắc)
3. Dán đoạn code trên vào và bấm **Publish** (Xuất bản).

---

## 4. Hướng Dẫn Cài Đặt (Setup)
1. Upload toàn bộ mã nguồn lên thư mục gốc (Root) của Web Server.
2. Cấp quyền ghi (Permissions `755` hoặc `777`) cho thư mục `/includes/`.
3. Truy cập vào domain của bạn: `http://domain.com/setup.php` để tiến hành cài đặt.
4. Làm theo hướng dẫn trên màn hình cài đặt. Sau khi thành công, đường dẫn trang quản trị (Admin) sẽ được **tạo ngẫu nhiên** để tăng cường bảo mật. Hãy lưu lại đường dẫn đó.

## 5. Nâng Cấp Hệ Thống (Auto Update)
CMS có tích hợp công cụ cập nhật (File Sync). 
- Vào trang **Quản Trị -> Cập Nhật**.
- Bạn có thể tải file lên ghi đè thủ công, hoặc để hệ thống tự động đồng bộ (Auto Sync) các tệp mới từ GitHub thông qua cấu hình `update.json`.
