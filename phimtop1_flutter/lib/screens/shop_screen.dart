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
  bool canCheckIn = false;
  int checkinStreak = 0;
  bool isCheckingIn = false;

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
    
    // Check check-in status
    final status = await cmsApi.checkCheckinStatus(auth.token!);
    bool canCheckinToday = false;
    int currentStreak = 0;
    if (status != null) {
      canCheckinToday = !(status['is_checked_in_today'] as bool? ?? true);
      currentStreak = (status['checkin_streak'] as int?) ?? 0;
    }
    
    // Refresh user info as well to update coins/active frame
    await auth.fetchCoins();
    setState(() {
      frames = list;
      canCheckIn = canCheckinToday;
      checkinStreak = currentStreak;
      isLoading = false;
    });
  }

  Future<void> _doCheckIn() async {
    final auth = context.read<AuthProvider>();
    if (auth.token == null) return;
    
    setState(() => isCheckingIn = true);
    final response = await cmsApi.doCheckin(auth.token!);
    setState(() => isCheckingIn = false);
    
    if (response != null && response['status'] == 'success') {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(response['message'] ?? 'Điểm danh thành công!'),
          backgroundColor: Colors.green,
        ));
      }
      _loadFrames(); // Reload frames and coins
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(response?['message'] ?? 'Điểm danh thất bại, vui lòng thử lại.'),
          backgroundColor: Colors.red,
        ));
      }
    }
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
    final primaryColor = Theme.of(context).primaryColor;

    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor, // Modern dark blue/black background
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 220.0,
            pinned: true,
            backgroundColor: Theme.of(context).scaffoldBackgroundColor,
            elevation: 0,
            flexibleSpace: FlexibleSpaceBar(
              title: const Text('Cửa hàng Vật phẩm', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white, textBaseline: TextBaseline.alphabetic, fontSize: 18)),
              titlePadding: const EdgeInsets.only(left: 48, bottom: 16),
              background: Stack(
                fit: StackFit.expand,
                children: [
                  Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                  ),
                  Positioned(
                    right: -50,
                    top: -50,
                    child: Container(
                      width: 200,
                      height: 200,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: primaryColor.withOpacity(0.2),
                        boxShadow: [BoxShadow(color: primaryColor.withOpacity(0.2), blurRadius: 40)],
                      ),
                    ),
                  ),
                  Positioned(
                    left: -30,
                    bottom: 0,
                    child: Container(
                      width: 150,
                      height: 150,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.purpleAccent.withOpacity(0.1),
                        boxShadow: [BoxShadow(color: Colors.purpleAccent.withOpacity(0.1), blurRadius: 40)],
                      ),
                    ),
                  ),
                  Positioned(
                    left: 20,
                    bottom: 70,
                    right: 20,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Sưu tầm Khung Avatar', style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800)),
                              const SizedBox(height: 6),
                              Text('Thể hiện đẳng cấp VIP của bạn', style: TextStyle(color: Colors.white70, fontSize: 14)),
                            ],
                          ),
                        ),
                        if (user != null)
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              if (canCheckIn)
                                GestureDetector(
                                  onTap: isCheckingIn ? null : _doCheckIn,
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(colors: [Colors.red, Colors.orange]),
                                      borderRadius: BorderRadius.circular(24),
                                      boxShadow: [BoxShadow(color: Colors.red.withOpacity(0.3), blurRadius: 8)],
                                    ),
                                    child: Row(
                                      children: [
                                        isCheckingIn
                                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                                          : const Icon(Icons.calendar_month, color: Colors.white, size: 18),
                                        const SizedBox(width: 6),
                                        const Text('Điểm danh', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                                      ],
                                    ),
                                  ),
                                ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                decoration: BoxDecoration(
                                  color: Colors.amber.withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(24),
                                  border: Border.all(color: Colors.amber.withOpacity(0.5), width: 1.5),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.stars, color: Colors.amber, size: 22),
                                    const SizedBox(width: 8),
                                    Text('${user.coins} Xu', style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold, fontSize: 16)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          
          if (isLoading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator()),
            )
          else if (frames.isEmpty)
            SliverFillRemaining(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.storefront_outlined, size: 80, color: Colors.white.withOpacity(0.2)),
                    const SizedBox(height: 16),
                    Text('Cửa hàng đang cập nhật', style: TextStyle(color: Colors.white54, fontSize: 16)),
                  ],
                ),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.all(16),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  childAspectRatio: 0.72,
                  crossAxisSpacing: 16,
                  mainAxisSpacing: 16,
                ),
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final frame = frames[index];
                    final isEquipped = frame.isActive;
                    
                    return Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E293B),
                        borderRadius: BorderRadius.circular(24),
                        border: Border.all(
                          color: isEquipped ? primaryColor : Colors.white.withOpacity(0.05),
                          width: isEquipped ? 2 : 1,
                        ),
                        boxShadow: isEquipped ? [
                          BoxShadow(color: primaryColor.withOpacity(0.3), blurRadius: 16, spreadRadius: -2)
                        ] : [],
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const SizedBox(height: 20),
                          Stack(
                            alignment: Alignment.center,
                            children: [
                              Container(
                                width: 84,
                                height: 84,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(color: Colors.black.withOpacity(0.5), blurRadius: 10, offset: const Offset(0, 5)),
                                  ],
                                ),
                                child: CircleAvatar(
                                  radius: 42,
                                  backgroundColor: const Color(0xFF334155),
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
                              ),
                              Positioned.fill(
                                child: Transform.scale(
                                  scale: 1.18,
                                  child: Image.network(frame.imageUrl, fit: BoxFit.cover),
                                ),
                              ),
                            ],
                          ),
                          const Spacer(),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            child: Text(
                              frame.name,
                              style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 15),
                              textAlign: TextAlign.center,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 16).copyWith(bottom: 16),
                            child: SizedBox(
                              width: double.infinity,
                              child: isEquipped
                                  ? ElevatedButton(
                                      onPressed: () => _equipFrame(0),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.white.withOpacity(0.1),
                                        foregroundColor: Colors.white,
                                        elevation: 0,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                      ),
                                      child: const Text('Tháo ra', style: TextStyle(fontWeight: FontWeight.bold)),
                                    )
                                  : frame.isOwned
                                      ? ElevatedButton(
                                          onPressed: () => _equipFrame(frame.id),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: primaryColor,
                                            foregroundColor: Colors.white,
                                            elevation: 4,
                                            shadowColor: primaryColor.withOpacity(0.5),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                            padding: const EdgeInsets.symmetric(vertical: 12),
                                          ),
                                          child: const Text('Trang bị', style: TextStyle(fontWeight: FontWeight.bold)),
                                        )
                                      : ElevatedButton(
                                          onPressed: () => _buyFrame(frame),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: Colors.amber.withOpacity(0.15),
                                            foregroundColor: Colors.amber,
                                            elevation: 0,
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                            padding: const EdgeInsets.symmetric(vertical: 12),
                                          ),
                                          child: Row(
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            children: [
                                              const Icon(Icons.stars, size: 18),
                                              const SizedBox(width: 6),
                                              Text('${frame.price}', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
                                            ],
                                          ),
                                        ),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                  childCount: frames.length,
                ),
              ),
            ),
          
          // Extra informative banner at bottom
          if (!isLoading && frames.isNotEmpty)
            SliverToBoxAdapter(
              child: Container(
                margin: const EdgeInsets.all(16).copyWith(top: 8, bottom: 32),
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [primaryColor.withOpacity(0.2), Colors.transparent],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: primaryColor.withOpacity(0.3)),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: primaryColor.withOpacity(0.2),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(Icons.info_outline_rounded, color: primaryColor, size: 28),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Làm sao để có thêm Xu?', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                          const SizedBox(height: 6),
                          Text(
                            'Bạn có thể nhận thêm Xu thông qua việc xem phim, điểm danh hàng ngày hoặc làm nhiệm vụ.',
                            style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
