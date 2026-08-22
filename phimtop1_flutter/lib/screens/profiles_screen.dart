import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import '../widgets/menu_row_tile.dart';

class ProfilesScreen extends StatefulWidget {
  const ProfilesScreen({super.key});

  @override
  State<ProfilesScreen> createState() => _ProfilesScreenState();
}

class _ProfilesScreenState extends State<ProfilesScreen> {
  List<UserProfile> _profiles = [];
  bool _isLoading = true;
  bool _isManageMode = false;

  @override
  void initState() {
    super.initState();
    _fetchProfiles();
  }

  Future<void> _fetchProfiles() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) {
      context.go('/login');
      return;
    }
    
    final profiles = await cmsApi.getProfiles(token);
    setState(() {
      _profiles = profiles;
      _isLoading = false;
    });
  }

  Future<void> _handleProfileClick(UserProfile profile) async {
    if (_isManageMode) {
      final action = await showDialog<String>(
        context: context,
        builder: (ctx) => AlertDialog(
          backgroundColor: Colors.grey[900],
          title: Text('Quản lý hồ sơ ${profile.profileName}', style: const TextStyle(color: Colors.white)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              MenuRowTile(
                icon: Icons.lock,
                iconColor: Colors.blueAccent,
                textColor: Colors.white,
                title: profile.hasPin ? 'Đổi/Xóa mã PIN' : 'Tạo mã PIN',
                onTap: () => Navigator.pop(ctx, 'pin'),
                showTrailing: false,
              ),
              MenuRowTile(
                icon: Icons.delete,
                iconColor: Colors.red,
                textColor: Colors.red,
                title: 'Xóa hồ sơ',
                onTap: () => Navigator.pop(ctx, 'delete'),
                showTrailing: false,
              ),
            ],
          ),
        ),
      );

      if (action == 'delete') {
        final confirm = await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            backgroundColor: Colors.grey[900],
            title: const Text('Xóa hồ sơ?', style: TextStyle(color: Colors.white)),
            content: Text('Bạn có chắc chắn muốn xóa hồ sơ "${profile.profileName}"?', style: const TextStyle(color: Colors.white70)),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx, false),
                child: const Text('Hủy', style: TextStyle(color: Colors.grey)),
              ),
              TextButton(
                onPressed: () => Navigator.pop(ctx, true),
                child: const Text('Xóa', style: TextStyle(color: Colors.red)),
              ),
            ],
          ),
        );

        if (confirm == true) {
          final token = context.read<AuthProvider>().token;
          final success = await cmsApi.deleteProfile(token!, profile.id);
          if (success) {
            _fetchProfiles();
          } else {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không thể xóa hồ sơ cuối cùng')));
            }
          }
        }
      } else if (action == 'pin') {
        final newPin = await _showPinInputDialog(context, 'Nhập mã PIN (4 số, để trống để xóa)');
        if (newPin != null) {
          final token = context.read<AuthProvider>().token!;
          // Call API to update profile with pinCode
          final success = await cmsApi.updateProfile(token, profile.id, profile.profileName, profile.avatarUrl, pinCode: newPin.isEmpty ? '' : newPin);
          if (success) {
            _fetchProfiles();
          } else {
            if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lỗi khi cập nhật mã PIN')));
          }
        }
      }
    } else {
      if (profile.hasPin) {
        final pin = await _showPinInputDialog(context, 'Nhập mã PIN');
        if (pin == null || pin.isEmpty) return;
        
        final token = context.read<AuthProvider>().token!;
        final success = await cmsApi.verifyPin(token, profile.id, pin);
        if (!success) {
          if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mã PIN không đúng')));
          return;
        }
      }
      
      await context.read<AuthProvider>().setProfile(profile);
      if (mounted) {
        context.go('/');
      }
    }
  }

  Future<String?> _showPinInputDialog(BuildContext context, String title) {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.grey[900],
        title: Text(title, style: const TextStyle(color: Colors.white, fontSize: 18)),
        content: TextField(
          controller: controller,
          obscureText: true,
          keyboardType: TextInputType.number,
          maxLength: 4,
          autofocus: true,
          style: const TextStyle(color: Colors.white, fontSize: 24, letterSpacing: 16),
          textAlign: TextAlign.center,
          decoration: InputDecoration(
            counterText: "",
            enabledBorder: const UnderlineInputBorder(borderSide: BorderSide(color: Colors.white54)),
            focusedBorder: UnderlineInputBorder(borderSide: BorderSide(color: Theme.of(context).primaryColor)),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy', style: TextStyle(color: Colors.grey))),
          TextButton(onPressed: () => Navigator.pop(ctx, controller.text), child: Text('Xác nhận', style: TextStyle(color: Theme.of(context).primaryColor))),
        ],
      ),
    );
  }

  void _showAddProfileModal() {
    final nameController = TextEditingController();
    bool isKidsMode = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.grey[900],
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setStateModal) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
                left: 16, right: 16, top: 24,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text("Thêm Hồ Sơ", style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  const Text("Thêm một hồ sơ cho người xem khác trên tài khoản của bạn.", style: TextStyle(color: Colors.white54, fontSize: 14)),
                  const SizedBox(height: 24),
                  Row(
                    children: [
                      Container(
                        width: 80, height: 80,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(12),
                          color: Colors.grey[800],
                        ),
                        child: const Icon(Icons.person, color: Colors.white54, size: 40),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: TextField(
                          controller: nameController,
                          style: const TextStyle(color: Colors.white),
                          decoration: InputDecoration(
                            hintText: "Tên",
                            hintStyle: const TextStyle(color: Colors.white38),
                            filled: true,
                            fillColor: Colors.white.withOpacity(0.05),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide.none),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  MenuRowTile(
                    icon: Icons.child_care,
                    iconColor: Colors.pinkAccent,
                    textColor: Colors.white,
                    title: "Chế độ Trẻ em (Kids Mode)",
                    subtitle: "Chỉ hiển thị nội dung phù hợp cho trẻ em",
                    trailing: Switch(
                      value: isKidsMode,
                      activeColor: Theme.of(context).primaryColor,
                      onChanged: (val) {
                        setStateModal(() { isKidsMode = val; });
                      },
                    ),
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: Colors.black,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      onPressed: () async {
                        final token = context.read<AuthProvider>().token;
                        if (nameController.text.trim().isEmpty) return;
                        
                        final success = await cmsApi.createProfile(token!, nameController.text.trim(), isKidsMode);
                        if (success) {
                          if (mounted) Navigator.pop(context);
                          _fetchProfiles();
                        } else {
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lỗi khi tạo hồ sơ hoặc đạt giới hạn')));
                          }
                        }
                      },
                      child: const Text("LƯU", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                    ),
                  ),
                  const SizedBox(height: 32),
                ],
              ),
            );
          }
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        body: Center(child: CircularProgressIndicator(color: Theme.of(context).primaryColor)),
      );
    }

    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        title: Image.network(
          'https://phimimg.com/upload/vod/20230219-1/afdc78a16db8a20d2d317ee0c36df12d.jpg', // Using generic for now
          height: 30,
          errorBuilder: (_,__,___) => Text('PhimTop1', style: TextStyle(color: Theme.of(context).primaryColor, fontWeight: FontWeight.bold)),
        ),
      ),
      body: Center(
        child: SingleChildScrollView(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                "Ai đang xem?",
                style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w500),
              ),
              const SizedBox(height: 32),
              Wrap(
                spacing: 24,
                runSpacing: 24,
                alignment: WrapAlignment.center,
                children: [
                  ..._profiles.map((p) => _buildProfileCard(p)),
                  if (_profiles.length < 5 && !_isManageMode)
                    _buildAddProfileCard(),
                ],
              ),
              const SizedBox(height: 64),
              OutlinedButton(
                style: OutlinedButton.styleFrom(
                  foregroundColor: _isManageMode ? Colors.black : Colors.grey,
                  backgroundColor: _isManageMode ? Colors.white : Colors.transparent,
                  side: BorderSide(color: _isManageMode ? Colors.white : Colors.grey),
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                ),
                onPressed: () {
                  setState(() {
                    _isManageMode = !_isManageMode;
                  });
                },
                child: Text(
                  _isManageMode ? "HOÀN TẤT" : "QUẢN LÝ HỒ SƠ",
                  style: const TextStyle(letterSpacing: 2, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProfileCard(UserProfile profile) {
    return GestureDetector(
      onTap: () => _handleProfileClick(profile),
      child: Column(
        children: [
          Stack(
            children: [
              Container(
                width: 100, height: 100,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.transparent, width: 2),
                  image: DecorationImage(
                    image: CachedNetworkImageProvider(profile.avatarUrl),
                    fit: BoxFit.cover,
                  ),
                ),
              ),
              if (profile.isKidsMode)
                Positioned(
                  top: 4, left: 4,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                    decoration: BoxDecoration(color: Theme.of(context).primaryColor, borderRadius: BorderRadius.circular(4)),
                    child: const Text('KIDS', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                  ),
                ),
              if (_isManageMode)
                Positioned.fill(
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.black54,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.white, width: 2),
                    ),
                    child: const Center(child: Icon(Icons.edit, color: Colors.white, size: 32)),
                  ),
                )
              else if (profile.hasPin)
                Positioned.fill(
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.black54,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Center(child: Icon(Icons.lock, color: Colors.white, size: 24)),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: 100,
            child: Text(
              profile.profileName,
              style: const TextStyle(color: Colors.white70, fontSize: 14),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAddProfileCard() {
    return GestureDetector(
      onTap: _showAddProfileModal,
      child: Column(
        children: [
          Container(
            width: 100, height: 100,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.transparent, width: 2),
              color: Colors.grey[900],
            ),
            child: const Center(child: Icon(Icons.add_circle_outline, color: Colors.white54, size: 48)),
          ),
          const SizedBox(height: 12),
          const SizedBox(
            width: 100,
            child: Text(
              "Thêm hồ sơ",
              style: TextStyle(color: Colors.white70, fontSize: 14),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }
}
