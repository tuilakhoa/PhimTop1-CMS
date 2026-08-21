import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'dart:math';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';
import '../services/auth_service.dart';
import '../models/models.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().fetchCoins();
    });
  }

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
                    child: Stack(
                      alignment: Alignment.center,
                      children: [
                        CircleAvatar(
                          radius: 40,
                          backgroundColor: Colors.grey[800],
                          backgroundImage: auth.currentProfile != null && auth.currentProfile!.avatarUrl.isNotEmpty
                              ? NetworkImage(auth.currentProfile!.avatarUrl)
                              : (user.avatar != null && user.avatar!.isNotEmpty ? NetworkImage(user.avatar!) : null),
                          child: (auth.currentProfile == null || auth.currentProfile!.avatarUrl.isEmpty) && (user.avatar == null || user.avatar!.isEmpty)
                              ? Text(
                                  auth.currentProfile?.profileName.isNotEmpty == true 
                                      ? auth.currentProfile!.profileName[0].toUpperCase() 
                                      : (user.name.isNotEmpty ? user.name[0].toUpperCase() : "?"),
                                  style: const TextStyle(fontSize: 32, color: Colors.white, fontWeight: FontWeight.bold),
                                )
                              : null,
                        ),
                        if (user.activeFrame != null && user.activeFrame!.isNotEmpty)
                          Positioned.fill(
                            child: Image.network(
                              user.activeFrame!,
                              fit: BoxFit.cover,
                            ),
                          ),
                      ],
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
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.stars, color: Colors.amber, size: 16),
                            const SizedBox(width: 4),
                            Text("Số dư: ${user.coins} Xu", style: const TextStyle(fontSize: 14, color: Colors.amber, fontWeight: FontWeight.bold)),
                          ],
                        ),
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
              _buildMenuItem(Icons.storefront, "Cửa hàng vật phẩm", () {
                context.push('/shop');
              }, Colors.amber, hintColor),
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
              _buildMenuItem(Icons.link, "Liên kết Google", () async {
                final authService = AuthService();
                final credential = await authService.signInWithGoogle();
                if (credential != null && credential.user != null) {
                  final uid = credential.user!.uid;
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Đang liên kết...")));
                    final success = await context.read<AuthProvider>().linkGoogle(uid);
                    if (context.mounted) {
                      if (success) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Liên kết tài khoản Google thành công!")));
                      } else {
                        final error = context.read<AuthProvider>().error ?? "Lỗi không xác định";
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error)));
                      }
                    }
                  }
                }
              }, Colors.blueAccent, hintColor),
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
    String initialUrl = '';
    if (auth.currentProfile != null) {
      initialUrl = auth.currentProfile!.avatarUrl;
    } else if (auth.user != null) {
      initialUrl = auth.user!.avatar ?? '';
    }
    
    final TextEditingController urlController = TextEditingController(text: initialUrl);
    final textColor = Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black;
    final hintColor = Theme.of(context).brightness == Brightness.dark ? Colors.grey : Colors.grey[700];
    final dialogBg = Theme.of(context).brightness == Brightness.dark ? Colors.grey[900] : Colors.white;
    final borderColor = Theme.of(context).brightness == Brightness.dark ? Colors.white.withOpacity(0.05) : Colors.black.withOpacity(0.05);

    void rollAvatar() {
      final styles = ['identicon', 'monsterid', 'wavatar', 'retro', 'robohash'];
      final randomStyle = styles[Random().nextInt(styles.length)];
      final randomHash = List.generate(32, (index) => Random().nextInt(16).toRadixString(16)).join('');
      urlController.text = 'https://www.gravatar.com/avatar/$randomHash?d=$randomStyle&s=200';
    }

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: dialogBg,
        title: Text('Đổi ảnh đại diện', style: TextStyle(color: textColor)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Nhập URL ảnh mới hoặc tạo tự động (Gravatar):', style: TextStyle(color: hintColor, fontSize: 14)),
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
                suffixIcon: IconButton(
                  icon: const Icon(Icons.casino, color: Colors.amber),
                  tooltip: 'Tạo ngẫu nhiên',
                  onPressed: rollAvatar,
                ),
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
              if (auth.token == null) return;
              final newUrl = urlController.text.trim();
              if (newUrl.isEmpty) return;
              
              if (auth.currentProfile != null) {
                final profile = auth.currentProfile!;
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
              } else if (auth.user != null) {
                final success = await cmsApi.updateUserAvatar(auth.token!, newUrl);
                if (success) {
                  final updatedUser = auth.user!.copyWith(avatar: newUrl);
                  await auth.updateUser(updatedUser);
                  if (ctx.mounted) Navigator.pop(ctx);
                } else {
                  if (ctx.mounted) {
                    ScaffoldMessenger.of(ctx).showSnackBar(const SnackBar(content: Text('Lỗi khi cập nhật ảnh đại diện')));
                  }
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
