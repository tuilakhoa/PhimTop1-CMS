import 'package:flutter/material.dart' show ThemeMode, Material, Brightness, CrossAxisAlignment, MainAxisAlignment, BorderRadius;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/theme_provider.dart';

class WindowsSettingsScreen extends StatelessWidget {
  const WindowsSettingsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final themeProvider = context.watch<ThemeProvider>();
    final isLoggedIn = authProvider.token != null;

    return ScaffoldPage(
      header: const PageHeader(
        title: Text('Cài Đặt', style: TextStyle(color: Colors.white)),
      ),
      content: ListView(
        padding: const EdgeInsets.all(24.0),
        children: [
          Text('TÀI KHOẢN', style: TextStyle(color: Colors.grey[100], fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          Card(
            backgroundColor: const Color(0xFF161623),
            child: ListTile(
              leading: const Icon(FluentIcons.contact, size: 24, color: Colors.white),
              title: Text(isLoggedIn ? (authProvider.user?.name ?? 'Người dùng') : 'Đăng nhập', style: const TextStyle(color: Colors.white)),
              subtitle: Text(isLoggedIn ? (authProvider.user?.email ?? '') : 'Đăng nhập để đồng bộ dữ liệu', style: const TextStyle(color: Colors.grey)),
              trailing: isLoggedIn ? Button(
                onPressed: () {
                  authProvider.logout();
                },
                child: const Text('Đăng xuất'),
              ) : Button(
                onPressed: () {
                  // Navigate to login
                },
                child: const Text('Đăng nhập'),
              ),
            ),
          ),
          const SizedBox(height: 32),
          Text('GIAO DIỆN', style: TextStyle(color: Colors.grey[100], fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          Card(
            backgroundColor: const Color(0xFF161623),
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(FluentIcons.color, color: Colors.white),
                  title: const Text('Chế độ sáng/tối', style: TextStyle(color: Colors.white)),
                  trailing: ToggleSwitch(
                    checked: themeProvider.themeMode == ThemeMode.dark,
                    onChanged: (v) {
                      themeProvider.setThemeMode(v ? ThemeMode.dark : ThemeMode.light);
                    },
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
