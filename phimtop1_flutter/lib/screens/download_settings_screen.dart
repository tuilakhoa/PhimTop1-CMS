import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:provider/provider.dart';
import 'package:path_provider/path_provider.dart';
import 'dart:io';
import '../providers/download_provider.dart';
import '../widgets/menu_row_tile.dart';

class DownloadSettingsScreen extends StatefulWidget {
  const DownloadSettingsScreen({super.key});

  @override
  State<DownloadSettingsScreen> createState() => _DownloadSettingsScreenState();
}

class _DownloadSettingsScreenState extends State<DownloadSettingsScreen> {
  bool _wifiOnlyDownload = true;
  String _downloadPathName = 'Bộ nhớ trong';
  String? _customPath;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    final custom = prefs.getString('custom_download_path');
    String pathName = 'Bộ nhớ trong';
    if (custom != null && custom.isNotEmpty) {
      if (custom.contains('emulated/0') || custom.contains('sdcard')) {
         pathName = 'Bộ nhớ dùng chung';
      } else {
         pathName = 'Thẻ nhớ / Khác';
      }
    }
    
    setState(() {
      _wifiOnlyDownload = prefs.getBool('wifi_only_download') ?? true;
      _customPath = custom;
      _downloadPathName = pathName;
    });
  }

  Future<void> _changeStorageLocation() async {
    final internalDir = await getApplicationDocumentsDirectory();
    final List<Directory>? extDirs = await getExternalStorageDirectories(type: StorageDirectory.movies);
    
    if (!mounted) return;
    
    showModalBottomSheet(
      context: context,
      backgroundColor: Theme.of(context).brightness == Brightness.dark ? Colors.grey[900] : Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(margin: const EdgeInsets.symmetric(vertical: 12), width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey[600], borderRadius: BorderRadius.circular(2))),
              const Padding(
                padding: EdgeInsets.all(16.0),
                child: Text('Chọn vị trí lưu trữ', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
              MenuRowTile(
                icon: Icons.phone_android,
                iconColor: Colors.blueAccent,
                textColor: Theme.of(ctx).brightness == Brightness.dark ? Colors.white : Colors.black,
                title: 'Bộ nhớ trong (Khuyên dùng)',
                subtitle: 'Bảo mật hơn, tốc độ cao hơn',
                trailing: _customPath == null ? const Icon(Icons.check, color: Colors.green) : null,
                onTap: () async {
                  final prefs = await SharedPreferences.getInstance();
                  await prefs.remove('custom_download_path');
                  if (mounted) {
                    setState(() { _customPath = null; _downloadPathName = 'Bộ nhớ trong'; });
                    Navigator.pop(ctx);
                  }
                },
              ),
              if (extDirs != null && extDirs.isNotEmpty)
                ...extDirs.map((dir) {
                  final isCurrent = _customPath == dir.path;
                  bool isSDCard = !dir.path.contains('emulated/0');
                  return MenuRowTile(
                    icon: isSDCard ? Icons.sd_storage : Icons.folder_shared,
                    iconColor: isSDCard ? Colors.orange : Colors.purple,
                    textColor: Theme.of(ctx).brightness == Brightness.dark ? Colors.white : Colors.black,
                    title: isSDCard ? 'Thẻ nhớ SD / USB' : 'Bộ nhớ dùng chung',
                    subtitle: dir.path,
                    trailing: isCurrent ? const Icon(Icons.check, color: Colors.green) : null,
                    onTap: () async {
                      final prefs = await SharedPreferences.getInstance();
                      await prefs.setString('custom_download_path', dir.path);
                      if (mounted) {
                        setState(() { _customPath = dir.path; _downloadPathName = isSDCard ? 'Thẻ nhớ / Khác' : 'Bộ nhớ dùng chung'; });
                        Navigator.pop(ctx);
                      }
                    },
                  );
                }),
              const SizedBox(height: 16),
            ],
          ),
        );
      }
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;
    final cardColor = isDark ? Colors.grey[900] : Colors.white;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text('Cài đặt tải xuống', style: TextStyle(fontWeight: FontWeight.bold, color: textColor)),
        backgroundColor: Colors.transparent,
        iconTheme: IconThemeData(color: textColor),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16.0),
        children: [
          Container(
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                MenuRowTile(
                  icon: Icons.wifi,
                  iconColor: Colors.blue,
                  textColor: textColor,
                  title: 'Chỉ tải qua Wi-Fi',
                  subtitle: 'Tạm dừng tải phim nếu dùng mạng di động (3G/4G/5G)',
                  trailing: Switch(
                    value: _wifiOnlyDownload,
                    activeColor: Theme.of(context).primaryColor,
                    onChanged: (val) async {
                      final prefs = await SharedPreferences.getInstance();
                      await prefs.setBool('wifi_only_download', val);
                      setState(() {
                        _wifiOnlyDownload = val;
                      });
                    },
                  ),
                ),
                Divider(color: isDark ? Colors.grey[800] : Colors.grey[300], height: 1),
                FutureBuilder<int>(
                  future: SharedPreferences.getInstance().then((prefs) {
                     int level = prefs.getInt('download_thread_level') ?? 0;
                     if (level == 0) {
                        bool oldBool = prefs.getBool('multi_thread_download') ?? false;
                        level = oldBool ? 5 : 1;
                     }
                     return level;
                  }),
                  builder: (context, snapshot) {
                    int threadLevel = snapshot.data ?? 1;
                    return MenuRowTile(
                      icon: threadLevel == 15 ? Icons.rocket_launch : Icons.speed, 
                      iconColor: threadLevel == 15 ? Colors.redAccent : Colors.amber,
                      textColor: textColor,
                      title: 'Chế độ tải phim',
                      subtitle: threadLevel == 15 ? 'DỒN TOÀN LỰC: Max băng thông, có thể nóng máy' : 
                        threadLevel == 5 ? 'TỐC ĐỘ CAO: Nhanh nhưng vẫn ổn định' : 
                        'BÌNH THƯỜNG: Tiết kiệm pin và RAM', 
                      trailing: DropdownButton<int>(
                        value: threadLevel,
                        dropdownColor: cardColor,
                        underline: const SizedBox(),
                        icon: Icon(Icons.arrow_drop_down, color: textColor),
                        items: [
                          DropdownMenuItem(value: 1, child: Text('Bình thường', style: TextStyle(color: textColor, fontSize: 13))),
                          DropdownMenuItem(value: 5, child: Text('Tốc độ cao', style: TextStyle(color: textColor, fontSize: 13))),
                          DropdownMenuItem(value: 15, child: Text('Max băng thông', style: TextStyle(color: Colors.redAccent, fontSize: 13, fontWeight: FontWeight.bold))),
                        ],
                        onChanged: (val) async {
                          if (val != null) {
                            final prefs = await SharedPreferences.getInstance();
                            await prefs.setInt('download_thread_level', val);
                            setState(() {}); // trigger rebuild
                          }
                        },
                      ),
                    );
                  },
                ),
                Divider(color: isDark ? Colors.grey[800] : Colors.grey[300], height: 1),
                MenuRowTile(
                  icon: Icons.delete_outline,
                  iconColor: Colors.redAccent,
                  textColor: Colors.redAccent,
                  title: 'Xóa tất cả phim đã tải',
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (context) => AlertDialog(
                        backgroundColor: cardColor,
                        title: Text('Xác nhận xóa', style: TextStyle(color: textColor)),
                        content: Text('Bạn có chắc chắn muốn xóa tất cả phim đã tải xuống không?', style: TextStyle(color: isDark ? Colors.grey[300] : Colors.grey[800])),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(context),
                            child: const Text('Hủy', style: TextStyle(color: Colors.grey)),
                          ),
                          TextButton(
                            onPressed: () {
                              Navigator.pop(context);
                              context.read<DownloadProvider>().deleteAllDownloads();
                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã xóa tất cả phim đã tải')));
                            },
                            child: const Text('Xóa tất cả', style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          Text('Lưu trữ', style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: cardColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: MenuRowTile(
              icon: Icons.storage,
              iconColor: Colors.deepPurple,
              textColor: textColor,
              title: 'Vị trí lưu trữ',
              subtitle: _downloadPathName,
              trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey),
              onTap: _changeStorageLocation,
            ),
          ),
        ],
      ),
    );
  }
}
