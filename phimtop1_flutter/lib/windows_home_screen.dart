import 'package:flutter/material.dart' show ThemeMode, Material, Brightness, CrossAxisAlignment, MainAxisAlignment, BorderRadius;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../providers/home_provider.dart';
import '../models/models.dart';
import 'windows_detail_screen.dart';

class WindowsHomeScreen extends StatefulWidget {
  const WindowsHomeScreen({Key? key}) : super(key: key);

  @override
  State<WindowsHomeScreen> createState() => _WindowsHomeScreenState();
}

class _WindowsHomeScreenState extends State<WindowsHomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<HomeProvider>().fetchHomeData();
    });
  }

  @override
  Widget build(BuildContext context) {
    final homeProvider = context.watch<HomeProvider>();

    if (homeProvider.isLoading) {
      return const Center(child: ProgressRing());
    }

    if (homeProvider.error != null) {
      return Center(child: Text('Lỗi: ${homeProvider.error}'));
    }

    final featuredMovies = homeProvider.featuredMovies;
    final topMovies = homeProvider.trendingMovies.take(5).toList(); // Lấy 5 phim cho Top hôm nay
    
    // Nếu phim Featured trống, lấy tạm phim đầu tiên của Phim Mới Cập Nhật
    final heroMovie = featuredMovies.isNotEmpty ? featuredMovies[0] : 
                      (homeProvider.normalMovies.isNotEmpty ? homeProvider.normalMovies[0] : null);

    return ScaffoldPage.scrollable(
      padding: EdgeInsets.zero,
      children: [
        // Khối Trên Cùng: Hero Banner (Trái) + Top Hôm Nay (Phải)
        Padding(
          padding: const EdgeInsets.all(24.0),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Hero Banner (Flex 7)
              Expanded(
                flex: 7,
                child: _buildHeroBanner(context, heroMovie, homeProvider.domain),
              ),
              const SizedBox(width: 24),
              // Top Hôm Nay (Flex 3)
              Expanded(
                flex: 3,
                child: _buildTopToday(context, topMovies, homeProvider.domain),
              ),
            ],
          ),
        ),

        // Các dải phim
        _buildMovieSection(context, "Phim Mới Cập Nhật", homeProvider.normalMovies, homeProvider.domain),
        _buildMovieSection(context, "Phim Bộ Đề Xuất", homeProvider.phimBo, homeProvider.domain),
        _buildMovieSection(context, "Phim Lẻ Đề Xuất", homeProvider.phimLe, homeProvider.domain),
        _buildMovieSection(context, "Phim Hoạt Hình", homeProvider.hoatHinh, homeProvider.domain),
        const SizedBox(height: 40),
      ],
    );
  }

  Widget _buildHeroBanner(BuildContext context, MovieItem? movie, String domain) {
    if (movie == null) return const SizedBox(height: 350);

    return Container(
      height: 350,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        color: const Color(0xFF1E1E2C),
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        fit: StackFit.expand,
        children: [
          // Hình nền (Thumbnail)
          Image.network(
            _getThumb(movie, domain),
            fit: BoxFit.cover,
            alignment: Alignment.topCenter,
            errorBuilder: (c, e, s) => Container(color: Colors.grey),
          ),
          // Gradient phủ đè
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  const Color(0xFF0A0A12).withOpacity(1.0),
                  const Color(0xFF0A0A12).withOpacity(0.6),
                  Colors.transparent,
                ],
                begin: Alignment.centerLeft,
                end: Alignment.centerRight,
              ),
            ),
          ),
          // Nội dung Banner
          Padding(
            padding: const EdgeInsets.all(32.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Nhãn
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    border: Border.all(color: const Color(0xFFFFA500)), // Cam
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text('ĐỀ XUẤT CHO BẠN', style: TextStyle(color: Color(0xFFFFA500), fontSize: 12, fontWeight: FontWeight.bold)),
                ),
                const SizedBox(height: 16),
                // Tên Phim
                Text(
                  movie.name,
                  style: const TextStyle(fontSize: 40, fontWeight: FontWeight.bold, color: Colors.white),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                if (movie.originName != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 4.0),
                    child: Text(
                      movie.originName!,
                      style: TextStyle(fontSize: 18, color: Colors.white.withOpacity(0.7)),
                    ),
                  ),
                const SizedBox(height: 16),
                // Tags (2026, HD, Thuyết Minh, Tập...)
                Row(
                  children: [
                    _buildBannerTag(movie.year?.toString() ?? "2024"),
                    const SizedBox(width: 8),
                    _buildBannerTag("HD"),
                    const SizedBox(width: 8),
                    _buildBannerTag("Vietsub"),
                    const SizedBox(width: 8),
                    _buildBannerTag("Full"),
                  ],
                ),
                const SizedBox(height: 24),
                // Nút bấm
                Row(
                  children: [
                    FilledButton(
                      style: ButtonStyle(
                        backgroundColor: WidgetStateProperty.all(const Color(0xFF6B48FF)), // Màu tím đặc trưng
                        padding: WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 24, vertical: 12)),
                      ),
                      onPressed: () {
                        Navigator.push(context, FluentPageRoute(builder: (_) => WindowsDetailScreen(movieSlug: movie.slug)));
                      },
                      child: const Row(
                        children: [
                          Icon(FluentIcons.play, size: 14, color: Colors.white),
                          SizedBox(width: 8),
                          Text('Xem ngay', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
                        ],
                      ),
                    ),
                    const SizedBox(width: 16),
                    Button(
                      style: ButtonStyle(
                        backgroundColor: WidgetStateProperty.all(Colors.white.withOpacity(0.1)),
                        padding: WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 20, vertical: 12)),
                      ),
                      onPressed: () {},
                      child: const Row(
                        children: [
                          Icon(FluentIcons.add, size: 14, color: Colors.white),
                          SizedBox(width: 8),
                          Text('Yêu thích', style: TextStyle(color: Colors.white)),
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
    );
  }

  Widget _buildBannerTag(String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(text, style: const TextStyle(fontSize: 12, color: Colors.white)),
    );
  }

  Widget _buildTopToday(BuildContext context, List<MovieItem> movies, String domain) {
    return Container(
      height: 350,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFF161623),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Top hôm nay', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
              Button(
                onPressed: () {},
                style: ButtonStyle(backgroundColor: WidgetStateProperty.all(Colors.transparent)),
                child: const Text('Xem tất cả', style: TextStyle(color: Color(0xFF6B48FF), fontSize: 12)),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Expanded(
            child: ListView.separated(
              itemCount: movies.length,
              separatorBuilder: (c, i) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final movie = movies[index];
                final isTop3 = index < 3;
                return HoverButton(
                  onPressed: () {
                    Navigator.push(context, FluentPageRoute(builder: (_) => WindowsDetailScreen(movieSlug: movie.slug)));
                  },
                  builder: (context, states) {
                    return Container(
                      decoration: BoxDecoration(
                        color: states.isHovered ? Colors.white.withOpacity(0.05) : Colors.transparent,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      padding: const EdgeInsets.all(4),
                      child: Row(
                        children: [
                          // Số thứ tự
                          Container(
                            width: 24, height: 24,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: isTop3 ? const Color(0xFFFFA500) : Colors.white.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text('${index + 1}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                          ),
                          const SizedBox(width: 12),
                          // Thumbnail
                          ClipRRect(
                            borderRadius: BorderRadius.circular(6),
                            child: Image.network(
                              _getThumb(movie, domain),
                              width: 50, height: 50, fit: BoxFit.cover,
                              errorBuilder: (c, e, s) => Container(width: 50, height: 50, color: Colors.grey),
                            ),
                          ),
                          const SizedBox(width: 12),
                          // Info
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  movie.name,
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                                  maxLines: 1, overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Full',
                                  style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                          // Rating
                          Row(
                            children: [
                              const Icon(FluentIcons.favorite_star_fill, color: Color(0xFFFFD700), size: 12),
                              const SizedBox(width: 4),
                              Text('9.${8-index}', style: const TextStyle(color: Color(0xFFFFD700), fontSize: 12, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ],
                      ),
                    );
                  }
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMovieSection(BuildContext context, String title, List<MovieItem> movies, String domain) {
    if (movies.isEmpty) return const SizedBox();

    return Padding(
      padding: const EdgeInsets.only(left: 24.0, right: 24.0, top: 16.0, bottom: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(FluentIcons.play, color: Color(0xFF6B48FF)),
                  const SizedBox(width: 10),
                  Text(title, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)),
                ],
              ),
              Button(
                onPressed: () {},
                style: ButtonStyle(backgroundColor: WidgetStateProperty.all(Colors.transparent)),
                child: const Text('Xem tất cả', style: TextStyle(color: Color(0xFF6B48FF))),
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 250,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: movies.length,
              separatorBuilder: (context, index) => const SizedBox(width: 16),
              itemBuilder: (context, index) {
                return _buildMovieCard(context, movies[index], domain);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMovieCard(BuildContext context, MovieItem movie, String domain) {
    return HoverButton(
      onPressed: () {
        Navigator.push(context, FluentPageRoute(builder: (_) => WindowsDetailScreen(movieSlug: movie.slug)));
      },
      builder: (context, states) {
        return AnimatedScale(
          scale: states.isHovered ? 1.05 : 1.0,
          duration: const Duration(milliseconds: 200),
          child: Container(
            width: 160,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              color: const Color(0xFF161623),
              boxShadow: states.isHovered
                  ? [BoxShadow(color: const Color(0xFF6B48FF).withOpacity(0.3), blurRadius: 12, offset: const Offset(0, 4))]
                  : [],
            ),
            clipBehavior: Clip.antiAlias,
            child: Stack(
              fit: StackFit.expand,
              children: [
                // Image
                Image.network(
                  _getThumb(movie, domain),
                  fit: BoxFit.cover,
                  errorBuilder: (c, e, s) => Container(color: Colors.grey),
                ),
                // Gradient Bottom
                Positioned(
                  bottom: 0, left: 0, right: 0,
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Colors.transparent, Colors.black.withOpacity(0.9)],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          movie.name,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 4),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              movie.year?.toString() ?? "2024",
                              style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 12),
                            ),
                            const Row(
                              children: [
                                Icon(FluentIcons.favorite_star_fill, color: Color(0xFFFFD700), size: 10),
                                SizedBox(width: 2),
                                Text('9.4', style: TextStyle(color: Color(0xFFFFD700), fontSize: 12, fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                // Badge (Tập / NEW)
                Positioned(
                  top: 8, left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFF6B48FF),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Text(
                      'NEW',
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  String _getThumb(MovieItem m, String domain) {
    if (m.thumbUrl == null || m.thumbUrl!.isEmpty) return '';
    if (m.thumbUrl!.startsWith('http')) return m.thumbUrl!;
    final baseDomain = domain.isNotEmpty ? domain : 'https://phimimg.com';
    return '$baseDomain/${m.thumbUrl}';
  }
}
