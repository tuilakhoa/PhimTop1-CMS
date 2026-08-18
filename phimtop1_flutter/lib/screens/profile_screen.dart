import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final textColor = Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black;
    final hintColor = Theme.of(context).brightness == Brightness.dark ? Colors.grey : Colors.grey[700];
    final dialogBg = Theme.of(context).brightness == Brightness.dark ? Colors.grey[900] : Colors.white;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        title: const Text("Cá nhân", style: TextStyle(fontWeight: FontWeight.bold)),
      ),
      body: Consumer<AuthProvider>(
        builder: (context, auth, child) {
          if (auth.user == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.account_circle, size: 100, color: hintColor),
                  const SizedBox(height: 16),
                  Text("Bạn chưa đăng nhập", style: TextStyle(color: textColor, fontSize: 18)),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: () => context.push('/login'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).primaryColor,
                      padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 12),
                    ),
                    child: const Text("Đăng nhập", style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(height: 32),
                  ListTile(
                    leading: Icon(Icons.download_done, color: textColor),
                    title: Text("Phim đã tải (Ngoại tuyến)", style: TextStyle(color: textColor, fontSize: 16)),
                    trailing: Icon(Icons.chevron_right, color: hintColor),
                    onTap: () => context.push('/downloads'),
                  ),
                ],
              ),
            );
          }

          final user = auth.user!;
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Row(
                children: [
                  GestureDetector(
                    onTap: () {
                      _showUpdateAvatarDialog(context, auth);
                    },
                    child: CircleAvatar(
                      radius: 40,
                      backgroundColor: Colors.grey[800],
                      backgroundImage: auth.currentProfile != null && auth.currentProfile!.avatarUrl.isNotEmpty
                          ? NetworkImage(auth.currentProfile!.avatarUrl)
                          : null,
                      child: (auth.currentProfile == null || auth.currentProfile!.avatarUrl.isEmpty)
                          ? Text(
                              auth.currentProfile?.profileName.isNotEmpty == true 
                                  ? auth.currentProfile!.profileName[0].toUpperCase() 
                                  : (user.name.isNotEmpty ? user.name[0].toUpperCase() : "?"),
                              style: const TextStyle(fontSize: 32, color: Colors.white, fontWeight: FontWeight.bold),
                            )
                          : null,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(auth.currentProfile?.profileName ?? user.name, style: TextStyle(fontSize: 22, color: textColor, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 4),
                        Text(user.email, style: TextStyle(fontSize: 16, color: hintColor)),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: Icon(Icons.expand_more, color: textColor),
                    onPressed: () {
                      context.push('/select_profile'); // We can navigate to profiles screen which already has switch and add profile logic.
                    },
                  ),
                ],
              ),
              const SizedBox(height: 40),
              _buildMenuItem(Icons.favorite, "Phim đã thích", () {
                context.push('/follow');
              }, textColor, hintColor),
              _buildMenuItem(Icons.playlist_play, "Danh sách phát", () {
                context.push('/playlists');
              }, textColor, hintColor),
              _buildMenuItem(Icons.notifications, "Thông báo", () {
                context.push('/notifications');
              }, textColor, hintColor),
              _buildMenuItem(Icons.history, "Lịch sử xem", () {
                context.push('/history');
              }, textColor, hintColor),
              _buildMenuItem(Icons.download_done, "Phim đã tải", () {
                context.push('/downloads');
              }, textColor, hintColor),
              _buildMenuItem(Icons.settings, "Cài đặt", () {
                context.push('/settings');
              }, textColor, hintColor),
              const SizedBox(height: 40),
              ListTile(
                leading: const Icon(Icons.logout, color: Colors.red),
                title: const Text("Đăng xuất", style: TextStyle(color: Colors.red, fontSize: 18)),
                onTap: () {
                  auth.logout();
                },
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildMenuItem(IconData icon, String title, VoidCallback onTap, Color textColor, Color? hintColor) {
    return ListTile(
      leading: Icon(icon, color: textColor),
      title: Text(title, style: TextStyle(color: textColor, fontSize: 16)),
      trailing: Icon(Icons.chevron_right, color: hintColor),
      onTap: onTap,
    );
  }

  void _showUpdateAvatarDialog(BuildContext context, AuthProvider auth) {
    final TextEditingController urlController = TextEditingController(text: auth.currentProfile?.avatarUrl ?? '');
    final textColor = Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black;
    final hintColor = Theme.of(context).brightness == Brightness.dark ? Colors.grey : Colors.grey[700];
    final dialogBg = Theme.of(context).brightness == Brightness.dark ? Colors.grey[900] : Colors.white;
    final borderColor = Theme.of(context).brightness == Brightness.dark ? Colors.white.withOpacity(0.05) : Colors.black.withOpacity(0.05);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: dialogBg,
        title: Text('Đổi ảnh đại diện', style: TextStyle(color: textColor)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Nhập URL ảnh mới (có thể dùng link Gravatar):', style: TextStyle(color: hintColor, fontSize: 14)),
            const SizedBox(height: 12),
            TextField(
              controller: urlController,
              style: TextStyle(color: textColor),
              decoration: InputDecoration(
                hintText: 'https://...',
                hintStyle: TextStyle(color: hintColor),
                filled: true,
                fillColor: borderColor,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide.none),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text('Hủy', style: TextStyle(color: hintColor)),
          ),
          TextButton(
            onPressed: () async {
              if (auth.currentProfile == null || auth.token == null) return;
              final newUrl = urlController.text.trim();
              if (newUrl.isEmpty) return;
              
              final profile = auth.currentProfile!;
              // Call API
              final success = await cmsApi.updateProfile(auth.token!, profile.id, profile.profileName, newUrl);
              if (success) {
                final updatedProfile = UserProfile.fromJson({
                  'id': profile.id,
                  'user_email': profile.userEmail,
                  'profile_name': profile.profileName,
                  'avatar_url': newUrl,
                  'is_kids_mode': profile.isKidsMode ? 1 : 0,
                  'has_pin': profile.hasPin ? 1 : 0,
                });
                await auth.setProfile(updatedProfile);
                if (ctx.mounted) Navigator.pop(ctx);
              } else {
                if (ctx.mounted) {
                  ScaffoldMessenger.of(ctx).showSnackBar(const SnackBar(content: Text('Lỗi khi cập nhật ảnh đại diện')));
                }
              }
            },
            child: Text('Lưu', style: TextStyle(color: Theme.of(context).primaryColor)),
          ),
        ],
      ),
    );
  }
}
