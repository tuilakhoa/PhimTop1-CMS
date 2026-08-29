import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';

class ReportErrorDialog extends StatefulWidget {
  final String movieName;
  final String movieSlug;
  final String episodeName;

  const ReportErrorDialog({
    Key? key,
    required this.movieName,
    required this.movieSlug,
    required this.episodeName,
  }) : super(key: key);

  @override
  State<ReportErrorDialog> createState() => _ReportErrorDialogState();
}

class _ReportErrorDialogState extends State<ReportErrorDialog> {
  final TextEditingController _messageController = TextEditingController();
  bool _isSubmitting = false;
  String? _selectedOption;

  final List<String> _options = [
    'Phim không phát được / Đứng hình',
    'Lỗi phụ đề / Thuyết minh',
    'Âm thanh bị lệch / Không có tiếng',
    'Chất lượng hình ảnh kém',
    'Tập phim bị trùng / Thiếu tập',
    'Khác'
  ];

  @override
  void dispose() {
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _submitFeedback() async {
    if (_selectedOption == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Vui lòng chọn một loại lỗi!'), backgroundColor: Colors.redAccent),
      );
      return;
    }

    String errorDetail = '';
    if (_selectedOption == 'Khác') {
      errorDetail = _messageController.text.trim();
      if (errorDetail.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Vui lòng nhập chi tiết lỗi!'), backgroundColor: Colors.redAccent),
        );
        return;
      }
    }

    final authProvider = context.read<AuthProvider>();
    if (authProvider.token == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Bạn cần đăng nhập để gửi báo lỗi!'), backgroundColor: Colors.redAccent),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    final message = "Phim: ${widget.movieName} (${widget.movieSlug}) - Tập: ${widget.episodeName} - Lỗi: $_selectedOption${errorDetail.isNotEmpty ? " - Chi tiết: $errorDetail" : ""}";
    
    final success = await CmsApiService().submitFeedback(authProvider.token!, message);

    if (!mounted) return;

    setState(() {
      _isSubmitting = false;
    });

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Cảm ơn bạn đã báo lỗi. Admin sẽ kiểm tra sớm nhất!'), backgroundColor: Colors.green),
      );
      Navigator.of(context).pop();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Có lỗi xảy ra, vui lòng thử lại sau!'), backgroundColor: Colors.red),
      );
    }
  }

  Widget _buildOption(String option) {
    bool isSelected = _selectedOption == option;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedOption = option;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: isSelected ? Colors.amber.withOpacity(0.1) : const Color(0xFF141414),
          border: Border.all(
            color: isSelected ? Colors.amber : const Color(0xFF333333),
            width: 1,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          children: [
            Container(
              width: 16,
              height: 16,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: isSelected ? Colors.amber : Colors.grey[600]!,
                  width: 1.5,
                ),
              ),
              child: isSelected 
                ? Center(
                    child: Container(
                      width: 8,
                      height: 8,
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.amber,
                      ),
                    ),
                  ) 
                : null,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                option == 'Khác' ? 'Lỗi khác (Nhập chi tiết)' : option,
                style: TextStyle(
                  color: isSelected ? Colors.amber : Colors.grey[300],
                  fontSize: 14,
                  fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: const Color(0xFF111111),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Báo lỗi phim',
              style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            ..._options.map((opt) => _buildOption(opt)).toList(),
            if (_selectedOption == 'Khác')
              Padding(
                padding: const EdgeInsets.only(top: 4.0, bottom: 8.0),
                child: TextField(
                  controller: _messageController,
                  maxLines: 3,
                  style: const TextStyle(color: Colors.white, fontSize: 14),
                  decoration: InputDecoration(
                    hintText: 'Nhập nội dung báo lỗi chi tiết...',
                    hintStyle: const TextStyle(color: Colors.white38),
                    filled: true,
                    fillColor: const Color(0xFF141414),
                    contentPadding: const EdgeInsets.all(12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFF333333)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFF333333)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Colors.amber),
                    ),
                  ),
                ),
              ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: TextButton(
                    onPressed: () => Navigator.of(context).pop(),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Hủy', style: TextStyle(color: Colors.white54, fontSize: 16, fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _submitFeedback,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.amber,
                      foregroundColor: Colors.black,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    child: _isSubmitting 
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.black, strokeWidth: 2))
                      : const Text('Gửi báo cáo', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
