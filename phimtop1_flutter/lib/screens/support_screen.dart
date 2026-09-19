import 'package:flutter/material.dart';

class SupportScreen extends StatefulWidget {
  const SupportScreen({super.key});

  @override
  State<SupportScreen> createState() => _SupportScreenState();
}

class _SupportScreenState extends State<SupportScreen> {
  int _selectedIndex = 0;

  final List<Map<String, String>> _pages = [
    {
      'title': 'Giới thiệu',
      'content': 'Chào mừng bạn đến với PhimTop1 - Nền tảng xem phim trực tuyến hàng đầu.\n\nChúng tôi tự hào mang đến cho bạn trải nghiệm giải trí tuyệt vời nhất với hàng ngàn bộ phim chất lượng cao, đa dạng thể loại từ hành động, tình cảm, hài hước đến khoa học viễn tưởng.\n\nTầm nhìn & Sứ mệnh\nSứ mệnh của chúng tôi là kết nối cảm xúc qua từng thước phim, xây dựng một cộng đồng yêu phim văn minh và thân thiện. Chúng tôi không ngừng cập nhật công nghệ mới nhất để đảm bảo chất lượng hình ảnh sắc nét và tốc độ truyền tải mượt mà nhất.'
    },
    {
      'title': 'Liên hệ',
      'content': 'Chúng tôi luôn lắng nghe và sẵn sàng hỗ trợ bạn. Nếu có bất kỳ câu hỏi, góp ý hay yêu cầu nào, xin vui lòng liên hệ với chúng tôi qua các kênh sau:\n\n- Email: support@phimtop1.com\n- Hotline: 1900 xxxx (8:00 - 22:00 hàng ngày)\n- Địa chỉ: Tòa nhà PhimTop1, Quận 1, TP.HCM\n\nĐội ngũ chăm sóc khách hàng của chúng tôi sẽ phản hồi bạn trong thời gian sớm nhất, thường là trong vòng 24 giờ làm việc.'
    },
    {
      'title': 'Điều khoản dịch vụ',
      'content': 'Việc sử dụng dịch vụ của PhimTop1 đồng nghĩa với việc bạn chấp nhận các điều khoản sau:\n\n1. Trách nhiệm người dùng\nBạn đồng ý sử dụng nền tảng của chúng tôi cho mục đích giải trí cá nhân và phi thương mại. Mọi hành vi sao chép, phát tán nội dung khi chưa được sự cho phép đều bị nghiêm cấm.\n\n2. Tài khoản\nBạn tự chịu trách nhiệm bảo mật thông tin tài khoản và mật khẩu của mình. Chúng tôi có quyền khóa tài khoản nếu phát hiện dấu hiệu vi phạm điều khoản dịch vụ hoặc các hoạt động gian lận.\n\n3. Thay đổi điều khoản\nChúng tôi bảo lưu quyền thay đổi các điều khoản này vào bất kỳ lúc nào. Những thay đổi sẽ được cập nhật công khai trên trang web và ứng dụng.'
    },
    {
      'title': 'Chính sách bảo mật',
      'content': 'Chúng tôi cam kết bảo vệ thông tin cá nhân của bạn. Dưới đây là cách chúng tôi thu thập, sử dụng và bảo vệ dữ liệu:\n\n1. Thông tin thu thập\nKhi đăng ký, chúng tôi có thể thu thập email, tên hiển thị. Trong quá trình sử dụng, chúng tôi lưu lại lịch sử xem phim và danh sách yêu thích để cá nhân hóa trải nghiệm.\n\n2. Sử dụng thông tin\nDữ liệu của bạn được dùng để duy trì tài khoản, đề xuất phim phù hợp và cải thiện chất lượng ứng dụng. Chúng tôi không bán dữ liệu của bạn cho bên thứ ba.\n\n3. Bảo mật\nChúng tôi áp dụng các biện pháp bảo mật tiêu chuẩn để mã hóa và bảo vệ thông tin của bạn khỏi các truy cập trái phép.'
    },
    {
      'title': 'Khiếu nại bản quyền',
      'content': 'Chúng tôi tôn trọng quyền sở hữu trí tuệ của người khác và tuân thủ Đạo luật Bản quyền Thiên niên kỷ Kỹ thuật số (DMCA).\n\nHầu hết các nội dung trên trang web này được lấy từ các nguồn chia sẻ công khai trên internet. Nếu bạn là chủ sở hữu bản quyền của bất kỳ tài liệu nào xuất hiện trên trang web của chúng tôi và muốn gỡ bỏ nó, vui lòng cung cấp các thông tin sau:\n- Chữ ký (vật lý hoặc điện tử) của người được ủy quyền hành động thay mặt cho chủ sở hữu bản quyền.\n- Thông tin nhận dạng tác phẩm có bản quyền bị vi phạm.\n- Thông tin nhận dạng tài liệu vi phạm cần gỡ bỏ, bao gồm URL trỏ tới tài liệu đó.\n- Thông tin liên hệ của bạn (Email, số điện thoại).\n\nGửi yêu cầu khiếu nại tới: dmca@phimtop1.com. Chúng tôi sẽ xem xét và gỡ bỏ nội dung vi phạm trong vòng 24-48 giờ làm việc.'
    }
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;
    final cardColor = isDark ? Colors.grey[900] : Colors.white;
    final primaryColor = Theme.of(context).primaryColor;

    return Scaffold(
      appBar: AppBar(
        title: Text("Trung tâm Hỗ trợ", style: TextStyle(color: textColor, fontSize: 18)),
        backgroundColor: Colors.transparent,
        iconTheme: IconThemeData(color: textColor),
      ),
      body: SafeArea(
        child: Column(
          children: [
            SizedBox(
              height: 50,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: _pages.length,
                itemBuilder: (context, index) {
                  final isSelected = _selectedIndex == index;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8.0),
                    child: ChoiceChip(
                      label: Text(_pages[index]['title']!),
                      selected: isSelected,
                      onSelected: (bool selected) {
                        if (selected) {
                          setState(() {
                            _selectedIndex = index;
                          });
                        }
                      },
                      selectedColor: primaryColor.withOpacity(0.2),
                      labelStyle: TextStyle(
                        color: isSelected ? primaryColor : (isDark ? Colors.white70 : Colors.black87),
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                      ),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    ),
                  );
                },
              ),
            ),
            Expanded(
              child: Container(
                margin: const EdgeInsets.all(16.0),
                padding: const EdgeInsets.all(20.0),
                width: double.infinity,
                decoration: BoxDecoration(
                  color: cardColor,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    if (!isDark) BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 2))
                  ],
                ),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _pages[_selectedIndex]['title']!,
                        style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: primaryColor),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        _pages[_selectedIndex]['content']!,
                        style: TextStyle(
                          fontSize: 15,
                          height: 1.6,
                          color: isDark ? Colors.white70 : Colors.black87,
                        ),
                      ),
                    ],
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
