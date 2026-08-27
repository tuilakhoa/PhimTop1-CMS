import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:share_plus/share_plus.dart';
import '../core/config.dart';
import '../services/tv_remote_service.dart';
import 'dart:io';
import 'package:package_info_plus/package_info_plus.dart';
import '../providers/home_provider.dart';
import '../widgets/update_dialog.dart';
import '../api/cms_api.dart';

import 'dart:math';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:path_provider/path_provider.dart';
import 'package:flutter_cache_manager/flutter_cache_manager.dart';
import '../widgets/menu_row_tile.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final TvRemoteService _tvService = TvRemoteService();
  String _version = "Đang tải...";
  int _buildNumber = 0;
  bool _hasAppLock = false;
  String _cacheSize = "Đang tính...";
  int _autoClearDays = 0;

  @override
  void initState() {
    super.initState();
    _loadVersion();
    _calculateCacheSize();
  }

  Future<void> _calculateCacheSize() async {
    try {
      final tempDir = await getTemporaryDirectory();
      int size = await _getDirSize(tempDir);
      if (mounted) {
        setState(() {
          _cacheSize = _formatBytes(size);
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _cacheSize = "Không rõ";
        });
      }
    }
  }

  Future<int> _getDirSize(Directory dir) async {
    int size = 0;
    try {
      if (await dir.exists()) {
        await for (var entity in dir.list(recursive: true, followLinks: false)) {
          if (entity is File) {
            size += await entity.length();
          }
        }
      }
    } catch (e) {}
    return size;
  }

  String _formatBytes(int bytes) {
    if (bytes <= 0) return "0 B";
    const suffixes = ["B", "KB", "MB", "GB", "TB"];
    int i = (log(bytes) / log(1024)).floor();
    return '${(bytes / pow(1024, i)).toStringAsFixed(1)} ${suffixes[i]}';
  }

  Future<void> _loadVersion() async {
    final info = await PackageInfo.fromPlatform();
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _version = info.version;
        _buildNumber = int.tryParse(info.buildNumber) ?? 0;
        _hasAppLock = prefs.getString('app_lock_pin') != null;
        _autoClearDays = prefs.getInt('auto_clear_cache_days') ?? 0;
      });
    }
  }

  Future<void> _checkUpdate() async {
    final provider = context.read<HomeProvider>();
    final bool isIOS = Platform.isIOS;
    final int targetBuild = isIOS ? provider.appBuildNumberIos : provider.appBuildNumber;
    final String targetVersion = isIOS ? provider.appLatestVersionIos : provider.appLatestVersion;
    final bool forceUpdate = isIOS ? provider.appForceUpdateIos : provider.appForceUpdate;
    final String downloadUrl = isIOS ? provider.appDownloadUrlIos : (provider.appInAppUpdateUrl.isNotEmpty ? provider.appInAppUpdateUrl : provider.appDownloadUrl);

    if (downloadUrl.isEmpty) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không có thông tin cập nhật')));
      }
      return;
    }

    if (targetBuild > _buildNumber) {
      if (mounted) {
        showDialog(
          context: context,
          barrierDismissible: !forceUpdate,
          builder: (context) {
            return UpdateDialog(
              version: targetVersion,
              message: provider.appUpdateMessage,
              downloadUrl: downloadUrl,
              forceUpdate: forceUpdate,
            );
          },
        );
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Bạn đang dùng phiên bản mới nhất')));
      }
    }
  }

  bool _isTvMode(BuildContext context) {
    if (appFlavor == 'mobile') return false;
    if (appFlavor == 'tv') return true;
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800 && size.shortestSide >= 500;
  }

  void _showFeedbackDialog() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;
    final bgColor = Theme.of(context).scaffoldBackgroundColor;
    final cardColor = isDark ? Colors.grey[900] : Colors.white;

    final token = context.read<AuthProvider>().token;
    if (token == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Vui lòng đăng nhập để gửi phản hồi')));
      return;
    }

    final TextEditingController controller = TextEditingController();
    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
              ),
              child: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [cardColor!, isDark ? Colors.black : Colors.grey[100]!],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                  boxShadow: [
                    BoxShadow(color: Theme.of(context).primaryColor.withOpacity(0.2), blurRadius: 20, spreadRadius: 2)
                  ],
                ),
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey[600], borderRadius: BorderRadius.circular(2))),
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(color: Theme.of(context).primaryColor.withOpacity(0.2), shape: BoxShape.circle),
                          child: Icon(Icons.feedback_rounded, color: Theme.of(context).primaryColor),
                        ),
                        const SizedBox(width: 16),
                        Text('Góp ý / Phản hồi', style: TextStyle(color: textColor, fontSize: 20, fontWeight: FontWeight.bold)),
                      ],
                    ),
                    const SizedBox(height: 24),
                    TextField(
                      controller: controller,
                      maxLines: 5,
                      style: TextStyle(color: textColor, fontSize: 16),
                      decoration: InputDecoration(
                        hintText: 'Nhập nội dung phản hồi (báo lỗi, góp ý, yêu cầu phim...)',
                        hintStyle: TextStyle(color: isDark ? Colors.grey[500] : Colors.grey[600]),
                        filled: true,
                        fillColor: isDark ? Colors.black45 : Colors.grey[200],
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide.none,
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide(color: Theme.of(context).primaryColor, width: 1.5),
                        ),
                        contentPadding: const EdgeInsets.all(16),
                      ),
                    ),
                    const SizedBox(height: 24),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              side: BorderSide(color: isDark ? Colors.grey[700]! : Colors.grey[400]!),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            onPressed: isSubmitting ? null : () => Navigator.pop(context),
                            child: Text('Hủy', style: TextStyle(color: isDark ? Colors.white70 : Colors.black54, fontSize: 16, fontWeight: FontWeight.bold)),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Theme.of(context).primaryColor,
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              elevation: 8,
                              shadowColor: Theme.of(context).primaryColor.withOpacity(0.5),
                            ),
                            onPressed: isSubmitting ? null : () async {
                              if (controller.text.trim().isEmpty) return;
                              setState(() => isSubmitting = true);
                              final success = await cmsApi.submitFeedback(token, controller.text.trim());
                              if (mounted) {
                                Navigator.pop(context);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(success ? 'Cảm ơn bạn đã gửi phản hồi!' : 'Có lỗi xảy ra, vui lòng thử lại sau.'),
                                    behavior: SnackBarBehavior.floating,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  )
                                );
                              }
                            },
                            child: isSubmitting 
                                ? SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: textColor, strokeWidth: 2))
                                : Text('Gửi', style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                  ],
                ),
              ),
            );
          }
        );
      },
    );
  }

  Future<void> _toggleAppLock() async {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;
    final bgColor = Theme.of(context).scaffoldBackgroundColor;
    final cardColor = isDark ? Colors.grey[900] : Colors.white;

    if (_hasAppLock) {
      // Bỏ khóa
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('app_lock_pin');
      setState(() { _hasAppLock = false; });
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã tắt khóa ứng dụng')));
    } else {
      // Cài PIN mới
      final controller = TextEditingController();
      final pin = await showDialog<String>(
        context: context,
        builder: (ctx) => AlertDialog(
          backgroundColor: cardColor,
          title: Text('Cài đặt mã PIN (4 số)', style: TextStyle(color: textColor, fontSize: 18)),
          content: TextField(
            controller: controller,
            obscureText: true,
            keyboardType: TextInputType.number,
            maxLength: 4,
            autofocus: true,
            textAlign: TextAlign.center,
            style: TextStyle(color: textColor, fontSize: 24, letterSpacing: 16),
            decoration: InputDecoration(
              counterText: "",
              enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: isDark ? Colors.white54 : Colors.black54)),
              focusedBorder: UnderlineInputBorder(borderSide: BorderSide(color: Theme.of(context).primaryColor)),
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy', style: TextStyle(color: Colors.grey))),
            TextButton(
              onPressed: () {
                if (controller.text.length == 4) Navigator.pop(ctx, controller.text);
              },
              child: Text('Xác nhận', style: TextStyle(color: Theme.of(context).primaryColor)),
            ),
          ],
        ),
      );

      if (pin != null && pin.length == 4) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('app_lock_pin', pin);
        setState(() { _hasAppLock = true; });
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã bật khóa ứng dụng')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;
    final bgColor = Theme.of(context).scaffoldBackgroundColor;
    final cardColor = isDark ? Colors.grey[900] : Colors.white;

    return Scaffold(

      appBar: AppBar(
        title: Text('Cài đặt', style: TextStyle(color: textColor, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
      ),
      body: ListView(
        padding: const EdgeInsets.symmetric(vertical: 12),
        children: [
          if (_isTvMode(context)) ...[
            _buildSectionHeader("Ghép nối thiết bị (Dành cho TV)"),
            _buildMenuGroup(context, [
              AnimatedBuilder(
                animation: _tvService,
                builder: (context, child) {
                  return Column(
                    children: [
                      if (_tvService.isServerRunning)
                        MenuRowTile(
                          icon: Icons.tv,
                          iconColor: Colors.green,
                          textColor: textColor,
                          title: "TV Đang chờ kết nối",
                          subtitle: "IP: ${_tvService.serverIp}\nMÃ PIN: ${_tvService.currentPin}",
                          trailing: IconButton(
                            icon: const Icon(Icons.stop, color: Colors.red),
                            onPressed: () => _tvService.stopServer(),
                          ),
                        )
                      else
                        MenuRowTile(
                          icon: Icons.tv,
                          iconColor: Colors.grey,
                          textColor: textColor,
                          title: "Bật Chế độ nhận lệnh",
                          subtitle: "Chỉ dành cho ứng dụng cài trên TV",
                          onTap: () => _tvService.startServer(),
                        ),
                    ],
                  );
                },
              ),
            ]),
            const SizedBox(height: 12),
          ],
          _buildSectionHeader("Tài khoản"),
          _buildMenuGroup(context, [
            MenuRowTile(
              icon: Icons.logout,
              iconColor: Colors.red,
              textColor: Colors.red,
              title: "Đăng xuất",
              showTrailing: false,
              onTap: () async {
                final confirm = await showDialog<bool>(
                  context: context,
                  builder: (context) => AlertDialog(
                    title: const Text("Đăng xuất"),
                    content: const Text("Bạn có chắc muốn đăng xuất khỏi ứng dụng?"),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(context, false), child: const Text("Hủy")),
                      TextButton(onPressed: () => Navigator.pop(context, true), child: const Text("Đăng xuất", style: TextStyle(color: Colors.red))),
                    ],
                  ),
                );
                
                if (confirm == true && context.mounted) {
                  await context.read<AuthProvider>().logout();
                  if (context.mounted) {
                    context.go('/profile');
                  }
                }
              },
            ),
          ]),
          const SizedBox(height: 12),
          _buildSectionHeader("Ứng dụng"),
          _buildMenuGroup(context, [
            MenuRowTile(
              icon: Icons.palette_outlined,
              iconColor: Colors.purple,
              textColor: textColor,
              title: "Màu nền / Giao diện",
              onTap: () {
                context.push('/appearance_settings');
              },
            ),
            MenuRowTile(
              icon: Icons.policy_outlined,
              iconColor: Colors.teal,
              textColor: textColor,
              title: "Điều khoản & Chính sách",
              onTap: () {
                context.push('/policy');
              },
            ),
            MenuRowTile(
              icon: Icons.info_outline,
              iconColor: Colors.blue,
              textColor: textColor,
              title: "Phiên bản",
              subtitle: "v$_version ($_buildNumber)",
              trailing: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Theme.of(context).primaryColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                ),
                onPressed: _checkUpdate,
                child: const Text("Kiểm tra cập nhật"),
              ),
            ),
            MenuRowTile(
              icon: Icons.verified_user_outlined,
              iconColor: Colors.green,
              textColor: textColor,
              title: "Giấy phép mã nguồn mở",
              onTap: () {
                showLicensePage(
                  context: context,
                  applicationName: 'PhimTop1',
                  applicationVersion: 'v$_version',
                  applicationIcon: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Icon(Icons.movie, size: 64, color: Theme.of(context).primaryColor),
                  ),
                  applicationLegalese: '© 2026 PhimTop1. All rights reserved.',
                );
              },
            ),
            MenuRowTile(
              icon: Icons.feedback_outlined,
              iconColor: Colors.orange,
              textColor: textColor,
              title: "Góp ý / Phản hồi",
              onTap: _showFeedbackDialog,
            ),
            MenuRowTile(
              icon: Icons.lock_outline,
              iconColor: Colors.brown,
              textColor: textColor,
              title: "Khóa ứng dụng",
              subtitle: "Mã PIN, Vân tay, Khuôn mặt",
              trailing: Switch(
                value: _hasAppLock,
                activeColor: Theme.of(context).primaryColor,
                onChanged: (val) => _toggleAppLock(),
              ),
            ),
            MenuRowTile(
              icon: Icons.download_for_offline_outlined,
              iconColor: Colors.indigo,
              textColor: textColor,
              title: "Cài đặt tải xuống",
              onTap: () {
                context.push('/download_settings');
              },
            ),
          ]),
          const SizedBox(height: 12),
          _buildSectionHeader("Khác"),
          _buildMenuGroup(context, [
            MenuRowTile(
              icon: Icons.cleaning_services_outlined,
              iconColor: Colors.deepOrange,
              textColor: textColor,
              title: "Xóa bộ nhớ đệm",
              trailing: Text(_cacheSize, style: const TextStyle(color: Colors.grey, fontSize: 13)),
              onTap: () {
                showDialog(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    backgroundColor: cardColor,
                    title: Text('Xóa bộ nhớ đệm', style: TextStyle(color: textColor)),
                    content: Text('Hành động này sẽ xóa dữ liệu tạm và bộ nhớ đệm hình ảnh/video ($_cacheSize). Bạn có chắc chắn không?', style: TextStyle(color: isDark ? Colors.grey[300] : Colors.grey[800])),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy', style: TextStyle(color: Colors.grey))),
                      TextButton(
                        onPressed: () async {
                          Navigator.pop(ctx);
                          try {
                             final tempDir = await getTemporaryDirectory();
                             if (await tempDir.exists()) {
                               for (var entity in tempDir.listSync()) {
                                 if (entity is File) await entity.delete();
                                 else if (entity is Directory) await entity.delete(recursive: true);
                               }
                             }
                             await DefaultCacheManager().emptyCache();
                             
                             // Update last clear time
                             final prefs = await SharedPreferences.getInstance();
                             await prefs.setInt('last_cache_clear_time', DateTime.now().millisecondsSinceEpoch);

                             if (mounted) {
                               setState(() { _cacheSize = "0 B"; });
                               ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã xóa bộ nhớ đệm ứng dụng')));
                             }
                          } catch (e) {
                             if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Lỗi khi xóa bộ nhớ đệm: $e')));
                          }
                        },
                        child: const Text('Xóa', style: TextStyle(color: Colors.redAccent, fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                );
              },
            ),
            MenuRowTile(
              icon: Icons.auto_delete_outlined,
              iconColor: Colors.deepPurple,
              textColor: textColor,
              title: "Tự động dọn rác",
              subtitle: "Xóa bộ nhớ đệm định kỳ",
              trailing: DropdownButton<int>(
                value: _autoClearDays,
                dropdownColor: cardColor,
                underline: const SizedBox(),
                icon: const Icon(Icons.arrow_drop_down, color: Colors.grey),
                style: TextStyle(color: textColor, fontSize: 14),
                items: const [
                  DropdownMenuItem(value: 0, child: Text("Không")),
                  DropdownMenuItem(value: 3, child: Text("Mỗi 3 ngày")),
                  DropdownMenuItem(value: 7, child: Text("Mỗi 7 ngày")),
                  DropdownMenuItem(value: 30, child: Text("Mỗi 30 ngày")),
                ],
                onChanged: (val) async {
                  if (val != null) {
                    final prefs = await SharedPreferences.getInstance();
                    await prefs.setInt('auto_clear_cache_days', val);
                    setState(() {
                      _autoClearDays = val;
                    });
                    if (val > 0) {
                       // Set initial clear time if not set
                       if (prefs.getInt('last_cache_clear_time') == null) {
                          await prefs.setInt('last_cache_clear_time', DateTime.now().millisecondsSinceEpoch);
                       }
                    }
                  }
                },
              ),
            ),
            MenuRowTile(
              icon: Icons.share_outlined,
              iconColor: Colors.cyan,
              textColor: textColor,
              title: "Chia sẻ ứng dụng",
              onTap: () {
                Share.share('Cùng xem phim trên PhimTop1 nhé: ${AppConfig.baseUrl}');
              },
            ),
            MenuRowTile(
              icon: Icons.star_outline,
              iconColor: Colors.amber,
              textColor: textColor,
              title: "Đánh giá ứng dụng",
              onTap: () {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cảm ơn bạn đã đánh giá!')));
              },
            ),
          ]),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildMenuGroup(BuildContext context, List<Widget> children) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? Colors.grey[900] : Colors.white;
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          if (!isDark) BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 2))
        ]
      ),
      child: Column(
        children: children,
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Text(
        title,
        style: const TextStyle(
          color: Colors.grey,
          fontSize: 14,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
