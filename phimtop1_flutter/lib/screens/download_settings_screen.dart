import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:provider/provider.dart';
import 'package:path_provider/path_provider.dart';
import 'dart:io';
import '../providers/download_provider.dart';

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
              ListTile(
                leading: const Icon(Icons.phone_android),
                title: const Text('Bộ nhớ trong (Khuyên dùng)'),
                subtitle: const Text('Bảo mật hơn, tốc độ cao hơn'),
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
                  return ListTile(
                    leading: Icon(isSDCard ? Icons.sd_storage : Icons.folder_shared),
                    title: Text(isSDCard ? 'Thẻ nhớ SD / USB' : 'Bộ nhớ dùng chung'),
                    subtitle: Text(dir.path, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 11)),
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
                ListTile(
                  leading: Icon(Icons.wifi, color: Theme.of(context).primaryColor),
                  title: Text('Chỉ tải qua Wi-Fi', style: TextStyle(color: textColor, fontWeight: FontWeight.bold)),
                  subtitle: Text('Tạm dừng tải phim nếu dùng mạng di động (3G/4G/5G)', style: TextStyle(color: isDark ? Colors.grey[400] : Colors.grey[600], fontSize: 12)),
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
                ListTile(
                  leading: const Icon(Icons.delete_outline, color: Colors.redAccent),
                  title: const Text('Xóa tất cả phim đã tải', style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold)),
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
            child: ListTile(
              leading: Icon(Icons.storage, color: Theme.of(context).primaryColor),
              title: Text('Vị trí lưu trữ', style: TextStyle(color: textColor, fontWeight: FontWeight.bold)),
              subtitle: Text(_downloadPathName, style: TextStyle(color: isDark ? Colors.grey[400] : Colors.grey[600], fontSize: 12)),
              trailing: const Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey),
              onTap: _changeStorageLocation,
            ),
          ),
        ],
      ),
    );
  }
}
