import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/home_provider.dart';
import '../services/tv_remote_service.dart';
import '../widgets/youtube_tv_movie_card.dart';
import '../models/models.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../widgets/error_view.dart';

class TvDashboardScreen extends StatefulWidget {
  final Widget child;
  const TvDashboardScreen({super.key, required this.child});

  @override
  State<TvDashboardScreen> createState() => _TvDashboardScreenState();
}

class _TvDashboardScreenState extends State<TvDashboardScreen> {
  final TvRemoteService _tvService = TvRemoteService();
  int _focusedSidebarIndex = -1;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _tvService.startServer();
      context.read<HomeProvider>().fetchHomeData();
    });
  }

  int _calculateSelectedIndex() {
    final String location = GoRouterState.of(context).uri.path;
    if (location.startsWith('/explore')) return 1;
    if (location.startsWith('/cartoon')) return 2;
    if (location.startsWith('/profile')) return 3;
    if (location.startsWith('/search')) return 4;
    return 0; // home
  }

  void _onItemTapped(int index) {
    switch (index) {
      case 0:
        context.go('/');
        break;
      case 1:
        context.go('/explore');
        break;
      case 2:
        context.go('/cartoon');
        break;
      case 3:
        context.go('/profile');
        break;
      case 4: // search
        context.push('/search');
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F0F), // YouTube TV Dark Background
      body: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildSidebar(),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (GoRouterState.of(context).uri.path != '/search')
                  _buildTopHeader(),
                Expanded(
                  child: FocusScope(
                    child: _buildTVContentWrapper(),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTopHeader() {
    return Padding(
      padding: const EdgeInsets.only(top: 32, right: 32, left: 16, bottom: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Mock Search Bar (clickable)
          InkWell(
            onTap: () => context.push('/search'),
            borderRadius: BorderRadius.circular(24),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.1),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Row(
                children: [
                  const Icon(Icons.mic, color: Colors.white70, size: 20),
                  const SizedBox(width: 12),
                  Text(
                    "Tìm kiếm hoặc nói...",
                    style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 16),
                  ),
                ],
              ),
            ),
          ),
          
          Row(
            children: [
              // Cast Button for PIN
              IconButton(
                icon: const Icon(Icons.cast, color: Colors.white70),
                onPressed: () {
                  showDialog(
                    context: context,
                    builder: (context) => AlertDialog(
                      backgroundColor: const Color(0xFF151515),
                      title: const Text("Mã Ghép Nối TV", style: TextStyle(color: Colors.white)),
                      content: AnimatedBuilder(
                        animation: _tvService,
                        builder: (context, child) {
                          if (!_tvService.isServerRunning || _tvService.currentPin.isEmpty) {
                            return const Text("Đang chờ...", style: TextStyle(color: Colors.white70));
                          }
                          return Text(
                            _tvService.currentPin,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 32,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 4,
                            ),
                            textAlign: TextAlign.center,
                          );
                        },
                      ),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(context),
                          child: const Text("ĐÓNG", style: TextStyle(color: Colors.red)),
                        ),
                      ],
                    ),
                  );
                },
              ),
              const SizedBox(width: 16),
              // App Logo (PhimTop1 Style)
              const Text(
                "PhimTop1",
                style: TextStyle(
                  color: Colors.white, 
                  fontSize: 24, 
                  fontWeight: FontWeight.w900, 
                  letterSpacing: -0.5,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSidebar() {
    final selectedIndex = _calculateSelectedIndex();
    final isSidebarExpanded = _focusedSidebarIndex != -1;
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      width: isSidebarExpanded ? 240 : 72,
      color: isSidebarExpanded ? Colors.black87 : Colors.transparent,
      padding: const EdgeInsets.symmetric(vertical: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Profile at top
          _buildSidebarItem(3, selectedIndex, Icons.account_circle, "Cá nhân", isSidebarExpanded, isProfile: true),
          const SizedBox(height: 24),
          _buildSidebarItem(4, -1, Icons.search, "Tìm kiếm", isSidebarExpanded), // Search icon
          const SizedBox(height: 16),
          _buildSidebarItem(0, selectedIndex, Icons.home_filled, "Trang chủ", isSidebarExpanded),
          const SizedBox(height: 16),
          _buildSidebarItem(1, selectedIndex, Icons.explore, "Khám phá", isSidebarExpanded),
          const SizedBox(height: 16),
          _buildSidebarItem(2, selectedIndex, Icons.animation, "Hoạt hình", isSidebarExpanded),
        ],
      ),
    );
  }

  Widget _buildSidebarItem(int index, int selectedIndex, IconData icon, String label, bool isExpanded, {bool isProfile = false}) {
    final isSelected = index == selectedIndex && !isProfile;
    return Focus(
      onFocusChange: (hasFocus) {
        setState(() {
          if (hasFocus) {
            _focusedSidebarIndex = index;
          } else if (_focusedSidebarIndex == index) {
            _focusedSidebarIndex = -1;
          }
        });
      },
      child: Builder(
        builder: (context) {
          final hasFocus = Focus.of(context).hasFocus;
          return InkWell(
            onTap: () => _onItemTapped(index),
            borderRadius: BorderRadius.circular(24),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: isExpanded ? 220 : 48,
              height: 48,
              margin: EdgeInsets.symmetric(horizontal: isExpanded ? 10 : 12),
              decoration: BoxDecoration(
                color: hasFocus 
                    ? Colors.white 
                    : (isSelected ? Colors.white.withOpacity(0.1) : Colors.transparent),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Row(
                mainAxisAlignment: isExpanded ? MainAxisAlignment.start : MainAxisAlignment.center,
                children: [
                  SizedBox(width: isExpanded ? 16 : 0),
                  Icon(
                    icon,
                    color: hasFocus 
                        ? Colors.black 
                        : (isSelected ? Colors.white : Colors.white70),
                    size: isProfile ? 32 : 24,
                  ),
                  if (isExpanded) ...[
                    const SizedBox(width: 16),
                    Expanded(
                      child: Text(
                        label,
                        style: TextStyle(
                          color: hasFocus ? Colors.black : Colors.white,
                          fontSize: 16,
                          fontWeight: hasFocus ? FontWeight.bold : FontWeight.normal,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        }
      ),
    );
  }

  Widget _buildTVContentWrapper() {
    final location = GoRouterState.of(context).uri.path;
    if (location == '/' || location.isEmpty) {
      return _buildHomeTVContent();
    } else {
      return widget.child;
    }
  }

  Widget _buildHomeTVContent() {
    return Consumer<HomeProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) return const Center(child: CircularProgressIndicator());
        if (provider.error != null) return ErrorView(error: provider.error!, onRetry: provider.fetchHomeData);

        return ListView(
          padding: const EdgeInsets.only(bottom: 60, left: 16),
          children: [
            if (provider.featuredMovies.isNotEmpty)
              _buildHeroBanner(provider.featuredMovies.first, provider.domain),
            if (provider.featuredMovies.isNotEmpty)
              _buildTVRow("Phim Đề Cử", provider.featuredMovies, provider.domain),
            _buildTVRow("Phim Mới Cập Nhật", provider.normalMovies, provider.domain),
            _buildTVRow("Phim Bộ Mới Nhất", provider.phimBo, provider.domain),
            _buildTVRow("Phim Lẻ Mới Nhất", provider.phimLe, provider.domain),
            _buildTVRow("TV Shows", provider.tvShows, provider.domain),
            _buildTVRow("Phim Hoạt Hình", provider.hoatHinh, provider.domain),
          ],
        );
      },
    );
  }

  Widget _buildHeroBanner(MovieItem movie, String domain) {
    return Container(
      height: 400,
      margin: const EdgeInsets.only(bottom: 24, right: 16),
      child: Focus(
        child: Builder(
          builder: (context) {
            final hasFocus = Focus.of(context).hasFocus;
            return InkWell(
              onTap: () => context.push('/movie/${movie.slug}'),
              borderRadius: BorderRadius.circular(16),
              child: AnimatedScale(
                scale: hasFocus ? 1.02 : 1.0,
                duration: const Duration(milliseconds: 200),
                curve: Curves.easeOutCubic,
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: hasFocus ? Colors.white : Colors.transparent,
                      width: hasFocus ? 3 : 0,
                    ),
                    boxShadow: hasFocus
                        ? [
                            BoxShadow(
                              color: Colors.white.withOpacity(0.3),
                              blurRadius: 10,
                              spreadRadius: 1,
                              offset: const Offset(0, 4),
                            )
                          ]
                        : [],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(13),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        CachedNetworkImage(
                          imageUrl: (movie.thumbUrl ?? movie.posterUrl ?? '').startsWith('http')
                              ? (movie.thumbUrl ?? movie.posterUrl!)
                              : '$domain/${movie.thumbUrl ?? movie.posterUrl}',
                          fit: BoxFit.cover,
                          alignment: Alignment.topCenter,
                        ),
                        Container(
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                              colors: [
                                Colors.black.withOpacity(0.9),
                                Colors.transparent,
                              ],
                            ),
                          ),
                        ),
                        Positioned(
                          bottom: 32,
                          left: 32,
                          right: 32,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                movie.name,
                                style: const TextStyle(
                                  fontSize: 36,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              if (movie.originName != null) ...[
                                const SizedBox(height: 8),
                                Text(
                                  movie.originName!,
                                  style: TextStyle(
                                    fontSize: 18,
                                    color: Colors.white.withOpacity(0.8),
                                  ),
                                ),
                              ],
                              const SizedBox(height: 16),
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                                    decoration: BoxDecoration(
                                      color: hasFocus ? Colors.white : Colors.white.withOpacity(0.9),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Row(
                                      children: [
                                        Icon(Icons.play_arrow, color: hasFocus ? Colors.black : Colors.black87),
                                        const SizedBox(width: 8),
                                        Text("Xem Ngay", style: TextStyle(color: hasFocus ? Colors.black : Colors.black87, fontWeight: FontWeight.bold, fontSize: 16)),
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
              ),
            );
          }
        ),
      ),
    );
  }

  Widget _buildTVRow(String title, List<MovieItem> movies, String domain) {
    if (movies.isEmpty) return const SizedBox.shrink();
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 8.0, bottom: 16.0, top: 12.0),
          child: Text(
            title,
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white),
          ),
        ),
        SizedBox(
          height: 240, // Height for 16:9 + text
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: movies.length,
            clipBehavior: Clip.none,
            itemBuilder: (context, index) {
              final movie = movies[index];
              return Focus(
                child: Builder(
                  builder: (context) {
                    final hasFocus = Focus.of(context).hasFocus;
                    return InkWell(
                      onTap: () => context.push('/movie/${movie.slug}'),
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        width: 260,
                        margin: const EdgeInsets.only(right: 16),
                        child: YoutubeTvMovieCard(
                          movie: movie,
                          domain: domain,
                          isFocused: hasFocus,
                        ),
                      ),
                    );
                  }
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 16),
      ],
    );
  }
}
