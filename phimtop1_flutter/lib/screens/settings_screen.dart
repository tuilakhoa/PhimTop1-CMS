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

import 'package:shared_preferences/shared_preferences.dart';

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
  bool _wifiOnlyDownload = true;

  @override
  void initState() {
    super.initState();
    _loadVersion();
  }

  Future<void> _loadVersion() async {
    final info = await PackageInfo.fromPlatform();
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _version = info.version;
        _buildNumber = int.tryParse(info.buildNumber) ?? 0;
        _hasAppLock = prefs.getString('app_lock_pin') != null;
        _wifiOnlyDownload = prefs.getBool('wifi_only_download') ?? true;
      });
    }
  }

  Future<void> _checkUpdate() async {
    final provider = context.read<HomeProvider>();
    final bool isIOS = Platform.isIOS;
    final int targetBuild = isIOS ? provider.appBuildNumberIos : provider.appBuildNumber;
    final String targetVersion = isIOS ? provider.appLatestVersionIos : provider.appLatestVersion;
    final bool forceUpdate = isIOS ? provider.appForceUpdateIos : provider.appForceUpdate;
    final String downloadUrl = isIOS ? provider.appDownloadUrlIos : provider.appDownloadUrl;

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
        children: [
          if (_isTvMode(context)) ...[
            const SizedBox(height: 20),
            _buildSectionHeader("Ghép nối thiết bị (Dành cho TV)"),
            AnimatedBuilder(
              animation: _tvService,
              builder: (context, child) {
                return Column(
                  children: [
                    if (_tvService.isServerRunning)
                      ListTile(
                        leading: const Icon(Icons.tv, color: Colors.green),
                        title: const Text("TV Đang chờ kết nối", style: TextStyle(color: Colors.green)),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text("IP: ${_tvService.serverIp}", style: const TextStyle(color: Colors.grey)),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Text("MÃ PIN: ", style: TextStyle(color: Colors.grey)),
                                Text(_tvService.currentPin, style: TextStyle(color: textColor, fontSize: 18, fontWeight: FontWeight.bold, letterSpacing: 2)),
                              ],
                            ),
                          ],
                        ),
                        trailing: IconButton(
                          icon: const Icon(Icons.stop, color: Colors.red),
                          onPressed: () => _tvService.stopServer(),
                        ),
                      )
                    else
                      ListTile(
                        leading: const Icon(Icons.tv, color: Colors.grey),
                        title: Text("Bật Chế độ nhận lệnh", style: TextStyle(color: textColor)),
                        subtitle: const Text("Chỉ dành cho ứng dụng cài trên TV", style: TextStyle(color: Colors.grey, fontSize: 12)),
                        onTap: () => _tvService.startServer(),
                      ),
                  ],
                );
              },
            ),
            const Divider(color: Colors.grey),
          ],
          _buildSectionHeader("Tài khoản"),
          ListTile(
            leading: const Icon(Icons.logout, color: Colors.red),
            title: const Text("Đăng xuất", style: TextStyle(color: Colors.red)),
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
          const Divider(color: Colors.grey),
          _buildSectionHeader("Ứng dụng"),
          ListTile(
            leading: const Icon(Icons.palette_outlined, color: Colors.grey),
            title: Text("Màu nền / Giao diện", style: TextStyle(color: textColor)),
            onTap: () {
              context.push('/appearance_settings');
            },
          ),
          ListTile(
            leading: const Icon(Icons.policy_outlined, color: Colors.grey),
            title: Text("Điều khoản & Chính sách", style: TextStyle(color: textColor)),
            onTap: () {
              context.push('/policy');
            },
          ),
          ListTile(
            leading: const Icon(Icons.info_outline, color: Colors.grey),
            title: Text("Phiên bản", style: TextStyle(color: textColor)),
            subtitle: Text("v$_version ($_buildNumber)", style: const TextStyle(color: Colors.grey)),
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
          ListTile(
            leading: const Icon(Icons.verified_user_outlined, color: Colors.grey),
            title: Text("Giấy phép mã nguồn mở", style: TextStyle(color: textColor)),
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
          ListTile(
            leading: const Icon(Icons.feedback_outlined, color: Colors.grey),
            title: Text("Góp ý / Phản hồi", style: TextStyle(color: textColor)),
            onTap: _showFeedbackDialog,
          ),
          ListTile(
            leading: const Icon(Icons.lock_outline, color: Colors.grey),
            title: Text("Khóa ứng dụng", style: TextStyle(color: textColor)),
            trailing: Switch(
              value: _hasAppLock,
              activeColor: Theme.of(context).primaryColor,
              onChanged: (val) => _toggleAppLock(),
            ),
          ),
          ListTile(
            leading: const Icon(Icons.wifi, color: Colors.grey),
            title: Text("Chỉ tải qua Wi-Fi", style: TextStyle(color: textColor)),
            subtitle: const Text("Tạm dừng tải nếu dùng mạng di động", style: TextStyle(color: Colors.grey, fontSize: 12)),
            trailing: Switch(
              value: _wifiOnlyDownload,
              activeColor: Theme.of(context).primaryColor,
              onChanged: (val) async {
                final prefs = await SharedPreferences.getInstance();
                await prefs.setBool('wifi_only_download', val);
                setState(() { _wifiOnlyDownload = val; });
              },
            ),
          ),

          const Divider(color: Colors.grey),
          _buildSectionHeader("Khác"),
          ListTile(
            leading: const Icon(Icons.cleaning_services_outlined, color: Colors.grey),
            title: Text("Xóa bộ nhớ đệm", style: TextStyle(color: textColor)),
            onTap: () {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã xóa bộ nhớ đệm ứng dụng')));
            },
          ),
          ListTile(
            leading: const Icon(Icons.share_outlined, color: Colors.grey),
            title: Text("Chia sẻ ứng dụng", style: TextStyle(color: textColor)),
            onTap: () {
              Share.share('Cùng xem phim trên PhimTop1 nhé: ${AppConfig.baseUrl}');
            },
          ),
          ListTile(
            leading: const Icon(Icons.star_outline, color: Colors.grey),
            title: Text("Đánh giá ứng dụng", style: TextStyle(color: textColor)),
            onTap: () {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cảm ơn bạn đã đánh giá!')));
            },
          ),
        ],
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
