import 'package:flutter/material.dart';

class PolicyScreen extends StatelessWidget {
  const PolicyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;
    final cardColor = isDark ? Colors.grey[900] : Colors.white;

    return Scaffold(
      appBar: AppBar(
        title: Text("Điều khoản & Chính sách", style: TextStyle(color: textColor, fontSize: 18)),
        backgroundColor: Colors.transparent,
        iconTheme: IconThemeData(color: textColor),
      ),
      body: SafeArea(
        child: Container(
          margin: const EdgeInsets.all(16.0),
          padding: const EdgeInsets.all(16.0),
          decoration: BoxDecoration(
            color: cardColor,
            borderRadius: BorderRadius.circular(12),
          ),
          child: SingleChildScrollView(
            child: Text(
              '''Chào mừng bạn đến với PhimTop1!

Bằng việc tiếp tục sử dụng ứng dụng này, bạn đồng ý với các Điều Khoản Sử Dụng và Chính Sách Bảo Mật của chúng tôi:

1. QUYỀN RIÊNG TƯ & BẢO MẬT
- Chúng tôi sử dụng các dịch vụ phân tích bên thứ ba (như Firebase Analytics) để thu thập dữ liệu sử dụng ứng dụng ẩn danh nhằm cải thiện trải nghiệm người dùng.
- Dữ liệu cá nhân (nếu có đăng nhập) chỉ được sử dụng cho mục đích cá nhân hóa trải nghiệm (lịch sử xem, theo dõi phim) và không được bán cho bên thứ ba.

2. TRÁCH NHIỆM VỀ NỘI DUNG
- Ứng dụng hoạt động như một công cụ tổng hợp nội dung giải trí. Các liên kết và dữ liệu video được cung cấp bởi các hệ thống máy chủ bên thứ ba trên mạng Internet.
- Chúng tôi không chịu trách nhiệm lưu trữ, phát tán nội dung trực tiếp trên máy chủ của ứng dụng.

3. QUY ĐỊNH SỬ DỤNG
- Không sử dụng ứng dụng vào mục đích thương mại khi chưa có sự cho phép.
- Không thực hiện các hành vi can thiệp vào mã nguồn của ứng dụng.

4. THAY ĐỔI ĐIỀU KHOẢN
- Chúng tôi có quyền thay đổi điều khoản dịch vụ mà không cần báo trước. Các thay đổi sẽ có hiệu lực ngay khi được cập nhật trên ứng dụng.

Cảm ơn bạn đã tin tưởng và sử dụng PhimTop1!
''',
              style: TextStyle(
                fontSize: 14,
                color: isDark ? Colors.white70 : Colors.black87,
                height: 1.5,
              ),
            ),
          ),
        ),
      ),
    );
  }
}
