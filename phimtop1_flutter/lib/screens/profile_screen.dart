import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'dart:math';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';
import '../services/auth_service.dart';
import '../services/watch_party_service.dart';
import '../models/models.dart';
import '../widgets/menu_row_tile.dart';
import 'watch_movie_screen.dart';

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
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: _buildMenuGroup([
                      MenuRowTile(icon: Icons.download_done, title: "Phim đã tải (Ngoại tuyến)", onTap: () => context.push('/downloads'), iconColor: Colors.teal, textColor: textColor, hintColor: hintColor),
                      MenuRowTile(icon: Icons.people_alt, title: "Vào Phòng Xem Chung", onTap: () => _showGlobalJoinWatchPartyDialog(context), iconColor: Colors.indigoAccent, textColor: textColor, hintColor: hintColor),
                    ], dialogBg),
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
              const SizedBox(height: 32),
              _buildMenuGroup([
                MenuRowTile(icon: Icons.storefront, title: "Cửa hàng vật phẩm", onTap: () => context.push('/shop'), iconColor: Colors.amber, textColor: textColor, hintColor: hintColor),
                MenuRowTile(icon: Icons.link, title: "Liên kết Google", onTap: () async {
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
                }, iconColor: Colors.blueAccent, textColor: textColor, hintColor: hintColor),
              ], dialogBg),
              
              const SizedBox(height: 16),
              _buildMenuGroup([
                MenuRowTile(icon: Icons.favorite, title: "Phim đã thích", onTap: () => context.push('/follow'), iconColor: Colors.pinkAccent, textColor: textColor, hintColor: hintColor),
                MenuRowTile(icon: Icons.playlist_play, title: "Danh sách phát", onTap: () => context.push('/playlists'), iconColor: Colors.purpleAccent, textColor: textColor, hintColor: hintColor),
                MenuRowTile(icon: Icons.history, title: "Lịch sử xem", onTap: () => context.push('/history'), iconColor: Colors.green, textColor: textColor, hintColor: hintColor),
                MenuRowTile(icon: Icons.download_done, title: "Phim đã tải", onTap: () => context.push('/downloads'), iconColor: Colors.teal, textColor: textColor, hintColor: hintColor),
              ], dialogBg),
              
              const SizedBox(height: 16),
              _buildMenuGroup([
                MenuRowTile(icon: Icons.people_alt, title: "Vào Phòng Xem Chung", onTap: () => _showGlobalJoinWatchPartyDialog(context), iconColor: Colors.indigoAccent, textColor: textColor, hintColor: hintColor),
                MenuRowTile(icon: Icons.notifications, title: "Thông báo", onTap: () => context.push('/notifications'), iconColor: Colors.orange, textColor: textColor, hintColor: hintColor),
                MenuRowTile(icon: Icons.settings, title: "Cài đặt", onTap: () => context.push('/settings'), iconColor: Colors.grey, textColor: textColor, hintColor: hintColor),
              ], dialogBg),

              const SizedBox(height: 24),
              _buildMenuGroup([
                MenuRowTile(icon: Icons.logout, title: "Đăng xuất", onTap: () => auth.logout(), iconColor: Colors.red, textColor: Colors.red, hintColor: hintColor, showTrailing: false),
              ], dialogBg),
            ],
          );
        },
      ),
    );
  }

  void _showGlobalJoinWatchPartyDialog(BuildContext context) {
    final TextEditingController codeCtrl = TextEditingController();
    bool isJoining = false;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white12))),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text("Vào Phòng Xem Chung", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                      IconButton(icon: const Icon(Icons.close, color: Colors.grey), onPressed: () => Navigator.pop(ctx)),
                    ],
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    children: [
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.3),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: Colors.white.withOpacity(0.08)),
                        ),
                        child: TextField(
                          controller: codeCtrl,
                          style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold, letterSpacing: 4),
                          textAlign: TextAlign.center,
                          textCapitalization: TextCapitalization.characters,
                          decoration: InputDecoration(
                            hintText: 'NHẬP MÃ',
                            hintStyle: TextStyle(color: Colors.white.withOpacity(0.2), letterSpacing: 4, fontWeight: FontWeight.bold, fontSize: 18),
                            border: InputBorder.none,
                            contentPadding: const EdgeInsets.symmetric(vertical: 24),
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        height: 56,
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Theme.of(context).primaryColor,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          ),
                          onPressed: isJoining ? null : () async {
                            final code = codeCtrl.text.trim().toUpperCase();
                            if (code.isEmpty) return;
                            
                            setModalState(() => isJoining = true);
                            
                            try {
                              final res = await WatchPartyService.joinParty(code);
                              if (!mounted) return;
                              
                              if (res['status'] != 'success') {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Lỗi: ${res['message']}')));
                                setModalState(() => isJoining = false);
                                return;
                              }
                              
                              final movieSlug = res['data']['movie_slug'];
                              final episodeName = res['data']['episode_name'];
                              
                              final detailRes = await cmsApi.getMovieDetail(movieSlug);
                              if (!mounted) return;
                              
                              if (detailRes.status != 'success' || detailRes.data == null) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không tìm thấy phim của phòng này')));
                                setModalState(() => isJoining = false);
                                return;
                              }
                              
                              final movieData = detailRes.data!.movie;
                              final episodesData = detailRes.data!.episodes ?? [];
                              
                              String? m3u8;
                              String? epSlug;
                              for (var epGroup in episodesData) {
                                for (var ep in epGroup.serverData) {
                                  if (ep.name == episodeName) {
                                    m3u8 = ep.linkM3u8;
                                    epSlug = ep.slug;
                                    break;
                                  }
                                }
                                if (m3u8 != null) break;
                              }
                              
                              if (m3u8 == null || m3u8.isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không tìm thấy dữ liệu phát của tập này.')));
                                setModalState(() => isJoining = false);
                                return;
                              }
                              
                              Navigator.pop(ctx);
                              Navigator.push(context, MaterialPageRoute(
                                builder: (context) => WatchMovieScreen(
                                  m3u8Link: m3u8!,
                                  title: movieData?.name ?? '',
                                  movieSlug: movieSlug,
                                  episodeName: episodeName,
                                  episodeSlug: epSlug ?? '',
                                  thumbUrl: movieData?.thumbUrl ?? movieData?.posterUrl ?? '',
                                  autoJoinRoomCode: code,
                                ),
                              ));
                              
                            } catch (e) {
                              setModalState(() => isJoining = false);
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Lỗi: $e')));
                              }
                            }
                          },
                          child: isJoining
                              ? const CircularProgressIndicator(color: Colors.white)
                              : const Text('VÀO PHÒNG', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 1)),
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildMenuGroup(List<Widget> children, Color? bgColor) {
    return Container(
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: children,
      ),
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
