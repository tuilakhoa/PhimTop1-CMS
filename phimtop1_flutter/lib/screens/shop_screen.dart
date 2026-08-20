import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import 'package:go_router/go_router.dart';

class ShopScreen extends StatefulWidget {
  const ShopScreen({super.key});

  @override
  State<ShopScreen> createState() => _ShopScreenState();
}

class _ShopScreenState extends State<ShopScreen> {
  List<AvatarFrame> frames = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadFrames();
  }

  Future<void> _loadFrames() async {
    final auth = context.read<AuthProvider>();
    if (auth.token == null) {
      if (mounted) context.pop();
      return;
    }
    setState(() => isLoading = true);
    final list = await cmsApi.getFrames(auth.token!);
    // Refresh user info as well to update coins/active frame
    await auth.fetchCoins();
    setState(() {
      frames = list;
      isLoading = false;
    });
  }

  Future<void> _buyFrame(AvatarFrame frame) async {
    final auth = context.read<AuthProvider>();
    if (auth.token == null) return;
    
    // Check balance first
    if (auth.user != null && auth.user!.coins < frame.price) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Không đủ xu! Vui lòng xem thêm phim để nhận xu.')));
      return;
    }

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Xác nhận mua'),
        content: Text('Bạn có muốn dùng ${frame.price} Xu để mua ${frame.name} không?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
          TextButton(
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => isLoading = true);
              final success = await cmsApi.buyFrame(auth.token!, frame.id);
              if (success) {
                if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mua thành công!')));
                await _loadFrames();
              } else {
                if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mua thất bại, vui lòng thử lại.')));
                setState(() => isLoading = false);
              }
            },
            child: const Text('Mua ngay'),
          ),
        ],
      ),
    );
  }

  Future<void> _equipFrame(int? frameId) async {
    final auth = context.read<AuthProvider>();
    if (auth.token == null) return;
    setState(() => isLoading = true);
    // 0 means unequip
    final success = await cmsApi.equipFrame(auth.token!, frameId ?? 0);
    if (success) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cập nhật khung thành công! Vui lòng đăng nhập lại để làm mới hoàn toàn.')));
      await _loadFrames();
    } else {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Trang bị thất bại.')));
      setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Cửa hàng Vật phẩm', style: TextStyle(fontWeight: FontWeight.bold)),
        actions: [
          if (user != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  const Icon(Icons.stars, color: Colors.amber, size: 20),
                  const SizedBox(width: 4),
                  Text('${user.coins} Xu', style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold, fontSize: 16)),
                ],
              ),
            ),
        ],
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : GridView.builder(
              padding: const EdgeInsets.all(16),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                childAspectRatio: 0.8,
                crossAxisSpacing: 16,
                mainAxisSpacing: 16,
              ),
              itemCount: frames.length,
              itemBuilder: (context, index) {
                final frame = frames[index];
                return Card(
                  color: Theme.of(context).cardColor,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Stack(
                        alignment: Alignment.center,
                        children: [
                          CircleAvatar(
                            radius: 40,
                            backgroundColor: Colors.grey[800],
                            backgroundImage: auth.currentProfile != null && auth.currentProfile!.avatarUrl.isNotEmpty
                                ? NetworkImage(auth.currentProfile!.avatarUrl)
                                : (user?.avatar != null && user!.avatar!.isNotEmpty ? NetworkImage(user.avatar!) : null),
                            child: (auth.currentProfile == null || auth.currentProfile!.avatarUrl.isEmpty) && (user?.avatar == null || user!.avatar!.isEmpty)
                                ? Text(
                                    auth.currentProfile?.profileName.isNotEmpty == true 
                                        ? auth.currentProfile!.profileName[0].toUpperCase() 
                                        : (user?.name.isNotEmpty == true ? user!.name[0].toUpperCase() : "?"),
                                    style: const TextStyle(fontSize: 32, color: Colors.white, fontWeight: FontWeight.bold),
                                  )
                                : null,
                          ),
                          Positioned.fill(
                            child: Image.network(frame.imageUrl, fit: BoxFit.cover),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(frame.name, style: const TextStyle(fontWeight: FontWeight.bold), textAlign: TextAlign.center),
                      const SizedBox(height: 8),
                      if (frame.isActive)
                        ElevatedButton(
                          onPressed: () => _equipFrame(0), // unequip
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                          child: const Text('Đang dùng (Gỡ)', style: TextStyle(color: Colors.white, fontSize: 12)),
                        )
                      else if (frame.isOwned)
                        ElevatedButton(
                          onPressed: () => _equipFrame(frame.id),
                          style: ElevatedButton.styleFrom(backgroundColor: Theme.of(context).primaryColor),
                          child: const Text('Trang bị', style: TextStyle(color: Colors.white)),
                        )
                      else
                        ElevatedButton.icon(
                          onPressed: () => _buyFrame(frame),
                          icon: const Icon(Icons.stars, color: Colors.amber, size: 16),
                          label: Text('${frame.price}', style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.grey[900],
                          ),
                        ),
                    ],
                  ),
                );
              },
            ),
    );
  }
}
