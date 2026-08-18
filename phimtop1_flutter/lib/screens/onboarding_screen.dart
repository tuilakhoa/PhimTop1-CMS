import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api/cms_api.dart';
import 'package:cached_network_image/cached_network_image.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  List<String> _posterUrls = [];
  String _domain = "";

  @override
  void initState() {
    super.initState();
    _fetchBackgroundPosters();
  }

  Future<void> _fetchBackgroundPosters() async {
    try {
      final res = await cmsApi.getHome();
      if (res.data != null && res.data!.items.isNotEmpty) {
        setState(() {
          _domain = res.data!.domain;
          _posterUrls = res.data!.items
              .where((m) => m.thumbUrl != null || m.posterUrl != null)
              .map((m) => m.posterUrl ?? m.thumbUrl!)
              .take(12)
              .toList();
        });
      }
    } catch (e) {
      // Ignore if fails
    }
  }

  Future<void> _completeOnboarding(BuildContext context, {bool toLogin = false}) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('has_seen_onboarding', true);
    if (context.mounted) {
      if (toLogin) {
        context.go('/login');
      } else {
        context.go('/');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // Background PageView
          PageView(
            controller: _pageController,
            onPageChanged: (index) {
              setState(() {
                _currentPage = index;
              });
            },
            children: [
              _buildPage1(),
              _buildPage2(),
            ],
          ),
          
          // Skip Button
          Positioned(
            top: MediaQuery.of(context).padding.top + 16,
            right: 16,
            child: GestureDetector(
              onTap: () => _completeOnboarding(context, toLogin: false),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text(
                  "ĐỂ SAU",
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                ),
              ),
            ),
          ),
          
          // Bottom Navigation Dots & Buttons
          Positioned(
            bottom: 40,
            left: 24,
            right: 24,
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(
                    2,
                    (index) => AnimatedContainer(
                      duration: const Duration(milliseconds: 300),
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      width: _currentPage == index ? 24 : 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: _currentPage == index ? Theme.of(context).primaryColor : Colors.white38,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).primaryColor,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    onPressed: () => _completeOnboarding(context, toLogin: true),
                    child: const Text(
                      "ĐĂNG NHẬP NGAY",
                      style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ],
            ),
          )
        ],
      ),
    );
  }

  Widget _buildPage1() {
    return Stack(
      fit: StackFit.expand,
      children: [
        // Grid of posters
        if (_posterUrls.isNotEmpty)
          Opacity(
            opacity: 0.5,
            child: GridView.builder(
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 3,
                childAspectRatio: 0.7,
                crossAxisSpacing: 4,
                mainAxisSpacing: 4,
              ),
              itemCount: _posterUrls.length,
              itemBuilder: (context, index) {
                final url = _posterUrls[index];
                final imageUrl = url.startsWith('http') ? url : '$_domain$url';
                return CachedNetworkImage(
                  imageUrl: imageUrl,
                  fit: BoxFit.cover,
                );
              },
            ),
          )
        else
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF1A1A24), Colors.black],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
          ),
          
        // Gradient overlay
        Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [Colors.black.withOpacity(0.1), Colors.black, Colors.black],
              stops: const [0.0, 0.6, 1.0],
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
            ),
          ),
        ),
        
        // Text Content
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.end,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                "Chào Mừng Bạn",
                style: TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              const Text(
                "Bạn muốn khám phá các nội dung thịnh hành nhất? Tất cả đều có trên PhimTop1, rất nhiều nội dung tuyệt vời đang chờ bạn: các bộ phim truyền hình châu Á, phim điện ảnh và hơn thế nữa. Đăng ký ngay để tham gia cùng với chúng tôi!",
                style: TextStyle(color: Colors.white70, fontSize: 14, height: 1.5),
              ),
              const SizedBox(height: 180), // Space for bottom buttons
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildPage2() {
    return Container(
      color: const Color(0xFF0F1218),
      padding: const EdgeInsets.symmetric(horizontal: 24.0),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text(
            "Tuyệt Vời Trên Mọi Thiết Bị\nThêm Mãn Nhãn Trên TV!",
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.greenAccent, fontSize: 28, fontWeight: FontWeight.bold, height: 1.3),
          ),
          const SizedBox(height: 60),
          
          // Glowing Devices Icon
          Container(
            width: 250,
            height: 250,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: Colors.greenAccent.withOpacity(0.2),
                  blurRadius: 60,
                  spreadRadius: 20,
                ),
                BoxShadow(
                  color: Theme.of(context).primaryColor.withOpacity(0.1),
                  blurRadius: 80,
                  spreadRadius: 30,
                ),
              ],
            ),
            child: Stack(
              alignment: Alignment.center,
              children: [
                Icon(Icons.tv, size: 160, color: Colors.greenAccent.withOpacity(0.8)),
                Positioned(
                  bottom: 40,
                  left: 40,
                  child: Icon(Icons.laptop_mac, size: 80, color: Theme.of(context).primaryColor.withOpacity(0.9)),
                ),
                Positioned(
                  bottom: 30,
                  right: 50,
                  child: Icon(Icons.smartphone, size: 60, color: Colors.tealAccent),
                ),
              ],
            ),
          ),
          const SizedBox(height: 80),
          const Text(
            "PHIMTOP1",
            style: TextStyle(
              color: Colors.greenAccent, 
              fontSize: 32, 
              fontWeight: FontWeight.w900,
              letterSpacing: 2.0
            ),
          ),
          const SizedBox(height: 160), // Space for bottom buttons
        ],
      ),
    );
  }
}
