import 'package:flutter/material.dart';

class TvVirtualKeyboard extends StatefulWidget {
  final String text;
  final Function(String) onTextChanged;
  final VoidCallback onSearch;

  const TvVirtualKeyboard({
    super.key,
    required this.text,
    required this.onTextChanged,
    required this.onSearch,
  });

  @override
  State<TvVirtualKeyboard> createState() => _TvVirtualKeyboardState();
}

class _TvVirtualKeyboardState extends State<TvVirtualKeyboard> {
  bool _isVietnamese = true;

  static const Map<String, List<String>> _variants = {
    'a': ['a', 'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'],
    'e': ['e', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'],
    'i': ['i', 'í', 'ì', 'ỉ', 'ĩ', 'ị'],
    'o': ['o', 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'],
    'u': ['u', 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'],
    'y': ['y', 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'],
    'd': ['d', 'đ'],
  };

  void _handleKeyPress(String key) {
    widget.onTextChanged(widget.text + key);
  }

  void _handleBackspace() {
    if (widget.text.isNotEmpty) {
      widget.onTextChanged(widget.text.substring(0, widget.text.length - 1));
    }
  }

  void _handleClear() {
    widget.onTextChanged("");
  }

  void _showVariantsDialog(BuildContext context, String key) {
    final variants = _variants[key]!;
    
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: Colors.grey[900],
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          content: Wrap(
            spacing: 8,
            runSpacing: 8,
            children: variants.map((v) {
              return _buildVariantKey(context, v, () {
                _handleKeyPress(v);
                Navigator.pop(context); // Close dialog
              });
            }).toList(),
          ),
        );
      },
    );
  }

  Widget _buildVariantKey(BuildContext context, String label, VoidCallback onPressed) {
    return Focus(
      child: Builder(
        builder: (context) {
          final hasFocus = Focus.of(context).hasFocus;
          return InkWell(
            onTap: onPressed,
            borderRadius: BorderRadius.circular(24),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 150),
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: hasFocus ? Colors.white : Colors.white.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              alignment: Alignment.center,
              child: Text(
                label,
                style: TextStyle(
                  color: hasFocus ? Colors.black : Colors.white,
                  fontSize: 20,
                  fontWeight: hasFocus ? FontWeight.bold : FontWeight.normal,
                ),
              ),
            ),
          );
        }
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final List<List<String>> rowsEn = [
      ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
      ['h', 'i', 'j', 'k', 'l', 'm', 'n'],
      ['o', 'p', 'q', 'r', 's', 't', 'u'],
      ['v', 'w', 'x', 'y', 'z', '-', '\''],
      ['1', '2', '3', '4', '5', '6', '7'],
      ['8', '9', '0', '', '', '', ''],
    ];

    final rows = rowsEn; // We just use Latin layout, and long-press for Vietnamese

    return Container(
      width: 400, // Fixed width for keyboard
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          for (var row in rows)
            Row(
              mainAxisAlignment: MainAxisAlignment.start,
              children: row.map((key) {
                if (key.isEmpty) return const SizedBox(width: 40, height: 40);
                return _buildKey(context, key, onPressed: () => _handleKeyPress(key));
              }).toList(),
            ),
          const SizedBox(height: 16),
          // Action Buttons Row
          Row(
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              _buildActionButton(context, "CÁCH", onPressed: () => _handleKeyPress(' ')),
              const SizedBox(width: 8),
              _buildActionButton(context, "XÓA HẾT", onPressed: _handleClear),
              const SizedBox(width: 8),
              _buildActionButton(context, "XÓA", icon: Icons.backspace, onPressed: _handleBackspace),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildKey(BuildContext context, String label, {required VoidCallback onPressed}) {
    return Container(
      margin: const EdgeInsets.all(4),
      width: 40,
      height: 40,
      child: Focus(
        child: Builder(
          builder: (context) {
            final hasFocus = Focus.of(context).hasFocus;
            return InkWell(
              onTap: onPressed,
              onLongPress: () {
                if (_variants.containsKey(label)) {
                  _showVariantsDialog(context, label);
                }
              },
              borderRadius: BorderRadius.circular(20),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 150),
                decoration: BoxDecoration(
                  color: hasFocus ? Colors.white : Colors.white.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                alignment: Alignment.center,
                child: Text(
                  label,
                  style: TextStyle(
                    color: hasFocus ? Colors.black : Colors.white,
                    fontSize: 18,
                    fontWeight: hasFocus ? FontWeight.bold : FontWeight.normal,
                  ),
                ),
              ),
            );
          }
        ),
      ),
    );
  }

  Widget _buildActionButton(BuildContext context, String label, {IconData? icon, required VoidCallback onPressed}) {
    return Focus(
      child: Builder(
        builder: (context) {
          final hasFocus = Focus.of(context).hasFocus;
          return InkWell(
            onTap: onPressed,
            borderRadius: BorderRadius.circular(8),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 150),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: hasFocus ? Colors.white : Colors.white.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (icon != null) ...[
                    Icon(icon, color: hasFocus ? Colors.black : Colors.white, size: 18),
                    const SizedBox(width: 6),
                  ],
                  Text(
                    label,
                    style: TextStyle(
                      color: hasFocus ? Colors.black : Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          );
        }
      ),
    );
  }
}
