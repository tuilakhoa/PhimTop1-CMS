import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/theme_provider.dart';

class AppearanceSettingsScreen extends StatelessWidget {
  const AppearanceSettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final themeProvider = context.watch<ThemeProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    // Text colors that adapt to current theme
    final textColor = Theme.of(context).textTheme.bodyLarge?.color ?? (isDark ? Colors.white : Colors.black);
    final subtitleColor = isDark ? Colors.grey[400] : Colors.grey[600];
    final cardColor = isDark ? Colors.grey[900] : Colors.white;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text('Màu nền', style: TextStyle(fontWeight: FontWeight.bold, color: textColor)),
        backgroundColor: Colors.transparent,
        iconTheme: IconThemeData(color: textColor),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16.0),
        children: [
          // System Setting Section
          Container(
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: ListTile(
              title: Text('Cài đặt theo hệ thống', style: TextStyle(color: textColor, fontWeight: FontWeight.bold)),
              subtitle: Text(
                'Khi mở ứng dụng, chế độ màu sẽ tự động điều chỉnh dựa trên cài đặt hệ thống của bạn.',
                style: TextStyle(color: subtitleColor, fontSize: 12),
              ),
              trailing: Switch(
                value: themeProvider.useSystem,
                activeColor: Theme.of(context).primaryColor,
                onChanged: (val) {
                  themeProvider.setUseSystem(val);
                },
              ),
            ),
          ),
          
          const SizedBox(height: 24),
          
          // Color Mode Section
          Text('Chế độ màu', style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                _buildRadioTile(
                  context: context,
                  title: 'Màu nhạt',
                  value: ThemeMode.light,
                  groupValue: themeProvider.useSystem ? ThemeMode.system : themeProvider.themeMode,
                  onChanged: (mode) {
                    themeProvider.setUseSystem(false);
                    themeProvider.setThemeMode(ThemeMode.light);
                  },
                  textColor: textColor,
                ),
                Divider(color: isDark ? Colors.grey[800] : Colors.grey[300], height: 1),
                _buildRadioTile(
                  context: context,
                  title: 'Màu sẫm',
                  value: ThemeMode.dark,
                  groupValue: themeProvider.useSystem ? ThemeMode.system : themeProvider.themeMode,
                  onChanged: (mode) {
                    themeProvider.setUseSystem(false);
                    themeProvider.setThemeMode(ThemeMode.dark);
                  },
                  textColor: textColor,
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 24),
          
          // Skins Section
          Text('Giao diện', style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 16,
            crossAxisSpacing: 16,
            childAspectRatio: 0.65,
            children: [
              _buildSkinCard(
                context: context,
                id: 'default',
                title: 'Mặc định',
                color: const Color(0xFFE50914),
                isSelected: themeProvider.currentSkin == 'default',
                onTap: () => themeProvider.setSkin('default'),
              ),
              _buildSkinCard(
                context: context,
                id: 'vip',
                title: 'VIP Độc Quyền',
                color: const Color(0xFFD4AF37),
                badgeText: 'VIP',
                badgeColor: const Color(0xFFD4AF37),
                badgeTextColor: Colors.black,
                isSelected: themeProvider.currentSkin == 'vip',
                onTap: () => themeProvider.setSkin('vip'),
              ),
              _buildSkinCard(
                context: context,
                id: 'futingyun',
                title: 'Đỏ Hoàng Cung',
                color: const Color(0xFFE06B5F),
                badgeText: 'HOT 🔥',
                badgeColor: Colors.redAccent,
                isSelected: themeProvider.currentSkin == 'futingyun',
                onTap: () => themeProvider.setSkin('futingyun'),
              ),
              _buildSkinCard(
                context: context,
                id: 'zhaoling',
                title: 'Nâu Trầm Ấm',
                color: const Color(0xFF6B4226),
                badgeText: 'MỚI ✨',
                badgeColor: Colors.orange,
                isSelected: themeProvider.currentSkin == 'zhaoling',
                onTap: () => themeProvider.setSkin('zhaoling'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRadioTile({
    required BuildContext context,
    required String title,
    required ThemeMode value,
    required ThemeMode groupValue,
    required Function(ThemeMode?) onChanged,
    required Color textColor,
  }) {
    return Theme(
      data: Theme.of(context).copyWith(
        unselectedWidgetColor: Colors.grey,
      ),
      child: RadioListTile<ThemeMode>(
        title: Text(title, style: TextStyle(color: textColor)),
        value: value,
        groupValue: groupValue,
        activeColor: Theme.of(context).primaryColor,
        onChanged: onChanged,
        controlAffinity: ListTileControlAffinity.trailing,
      ),
    );
  }

  Widget _buildSkinCard({
    required BuildContext context,
    required String id,
    required String title,
    required Color color,
    required bool isSelected,
    required VoidCallback onTap,
    String? badgeText,
    Color badgeColor = Colors.black54,
    Color badgeTextColor = Colors.white,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;

    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Expanded(
            child: Stack(
              children: [
                Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isSelected ? Theme.of(context).primaryColor : Colors.transparent,
                      width: 2,
                    ),
                    color: isDark ? Colors.grey[850] : Colors.grey[300],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(14),
                    child: Column(
                      children: [
                        Container(
                          height: 60,
                          color: color.withOpacity(0.3),
                          child: Center(
                            child: Icon(Icons.movie, color: color, size: 30),
                          ),
                        ),
                        Expanded(
                          child: Container(
                            color: isDark ? Colors.grey[900] : Colors.white,
                            padding: const EdgeInsets.all(8),
                            child: Column(
                              children: [
                                Container(height: 10, color: isDark ? Colors.grey[800] : Colors.grey[200], margin: const EdgeInsets.only(bottom: 8)),
                                Container(height: 10, color: isDark ? Colors.grey[800] : Colors.grey[200], margin: const EdgeInsets.only(bottom: 8)),
                                Container(height: 10, width: 50, color: isDark ? Colors.grey[800] : Colors.grey[200], alignment: Alignment.centerLeft),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                if (badgeText != null)
                  Positioned(
                    top: 0,
                    right: 0,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: badgeColor,
                        borderRadius: const BorderRadius.only(topRight: Radius.circular(14), bottomLeft: Radius.circular(10)),
                      ),
                      child: Text(badgeText, style: TextStyle(color: badgeTextColor, fontSize: 10, fontWeight: FontWeight.bold)),
                    ),
                  ),
                if (isSelected)
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: Theme.of(context).primaryColor,
                        borderRadius: const BorderRadius.only(topLeft: Radius.circular(14), bottomRight: Radius.circular(14)),
                      ),
                      child: const Icon(Icons.check, color: Colors.white, size: 16),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            title,
            style: TextStyle(color: textColor, fontSize: 14),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
