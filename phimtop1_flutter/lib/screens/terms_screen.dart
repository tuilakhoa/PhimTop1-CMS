import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';

class TermsScreen extends StatelessWidget {
  const TermsScreen({super.key});

  Future<void> _agreeAndContinue(BuildContext context) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('has_agreed_terms', true);
    if (context.mounted) {
      context.go('/');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Column(
          children: [
            const Padding(
              padding: EdgeInsets.all(20.0),
              child: Text(
                'Điều Khoản Sử Dụng & Chính Sách Bảo Mật',
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
                textAlign: TextAlign.center,
              ),
            ),
            Expanded(
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 16.0),
                padding: const EdgeInsets.all(16.0),
                decoration: BoxDecoration(
                  color: Colors.grey[900],
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const SingleChildScrollView(
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
- Không thực hiện các hành vi 리(reverse engineering) hoặc can thiệp vào mã nguồn của ứng dụng.

4. THAY ĐỔI ĐIỀU KHOẢN
- Chúng tôi có quyền thay đổi điều khoản dịch vụ mà không cần báo trước. Các thay đổi sẽ có hiệu lực ngay khi được cập nhật trên ứng dụng.

Vui lòng đọc kỹ các điều khoản này. Nếu bạn không đồng ý, xin vui lòng ngừng sử dụng ứng dụng và gỡ cài đặt.
''',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.white70,
                      height: 1.5,
                    ),
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(20.0),
              child: SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  onPressed: () => _agreeAndContinue(context),
                  child: const Text(
                    'Tôi Đã Đọc Và Đồng Ý',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
