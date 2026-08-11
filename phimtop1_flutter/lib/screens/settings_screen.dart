import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:share_plus/share_plus.dart';
import '../core/config.dart';
import '../services/tv_remote_service.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  final TvRemoteService _tvService = TvRemoteService();

  bool _isTvMode(BuildContext context) {
    if (appFlavor == 'mobile') return false;
    if (appFlavor == 'tv') return true;
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Cài đặt', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
                                Text(_tvService.currentPin, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold, letterSpacing: 2)),
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
                        title: const Text("Bật Chế độ nhận lệnh", style: TextStyle(color: Colors.white)),
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
            leading: const Icon(Icons.policy_outlined, color: Colors.grey),
            title: const Text("Điều khoản & Chính sách", style: TextStyle(color: Colors.white)),
            onTap: () {
              context.push('/policy');
            },
          ),
          ListTile(
            leading: const Icon(Icons.info_outline, color: Colors.grey),
            title: const Text("Phiên bản", style: TextStyle(color: Colors.white)),
            trailing: const Text("1.0.0", style: TextStyle(color: Colors.grey)),
          ),

          const Divider(color: Colors.grey),
          _buildSectionHeader("Khác"),
          ListTile(
            leading: const Icon(Icons.cleaning_services_outlined, color: Colors.grey),
            title: const Text("Xóa bộ nhớ đệm", style: TextStyle(color: Colors.white)),
            onTap: () {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đã xóa bộ nhớ đệm ứng dụng')));
            },
          ),
          ListTile(
            leading: const Icon(Icons.share_outlined, color: Colors.grey),
            title: const Text("Chia sẻ ứng dụng", style: TextStyle(color: Colors.white)),
            onTap: () {
              Share.share('Cùng xem phim trên PhimTop1 nhé: ${AppConfig.baseUrl}');
            },
          ),
          ListTile(
            leading: const Icon(Icons.star_outline, color: Colors.grey),
            title: const Text("Đánh giá ứng dụng", style: TextStyle(color: Colors.white)),
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
