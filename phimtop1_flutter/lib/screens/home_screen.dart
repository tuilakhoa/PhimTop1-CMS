import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/home_provider.dart';
import '../widgets/movie_card.dart';
import '../widgets/focusable_wrapper.dart';
import '../widgets/tv_cast_button.dart';
import '../widgets/featured_slider.dart';
import '../core/config.dart';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';
import '../models/models.dart';
import 'dart:io';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import '../widgets/update_dialog.dart';
import '../services/widget_service.dart';
import '../widgets/error_view.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<HistoryItem> _history = [];
  bool _hasCheckedUpdate = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await context.read<HomeProvider>().fetchHomeData();
      if (mounted) {
        _checkUpdate();
      }
      _fetchHistory();
    });
  }

  Future<void> _checkUpdate() async {
    if (_hasCheckedUpdate) return;
    _hasCheckedUpdate = true;
    
    final provider = context.read<HomeProvider>();
    
    final bool isIOS = Platform.isIOS;
    final int targetBuild = isIOS ? provider.appBuildNumberIos : provider.appBuildNumber;
    final String targetVersion = isIOS ? provider.appLatestVersionIos : provider.appLatestVersion;
    final bool forceUpdate = isIOS ? provider.appForceUpdateIos : provider.appForceUpdate;
    final String downloadUrl = isIOS ? provider.appDownloadUrlIos : provider.appDownloadUrl;

    if (downloadUrl.isEmpty) return;

    try {
      final packageInfo = await PackageInfo.fromPlatform();
      final currentBuildNumber = int.tryParse(packageInfo.buildNumber) ?? 0;
      
      if (targetBuild > currentBuildNumber) {
        if (!mounted) return;
        
        showDialog(
          context: context,
          barrierDismissible: !forceUpdate,
          builder: (context) {
            return UpdateDialog(
              version: targetVersion,
              message: provider.appUpdateMessage,
              downloadUrl: downloadUrl,
              forceUpdate: forceUpdate,
            );
          },
        );
      }
    } catch (_) {}
  }

  Future<void> _fetchHistory() async {
    final token = context.read<AuthProvider>().token;
    if (token != null) {
      try {
        final res = await cmsApi.getHistory(token);
        if (mounted && res.data != null) {
          setState(() {
            _history = res.data!.take(10).toList();
          });
          WidgetService.updateContinueWatchingWidget(res.data!);
        }
      } catch (_) {}
    }
  }

  @override
  void dispose() {
    super.dispose();
  }

  Widget _buildHorizontalList(String title, List<dynamic> movies, String domain) {
    if (movies.isEmpty) return const SizedBox();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 16.0, top: 24.0, bottom: 16.0),
          child: Text(
            title,
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black),
          ),
        ),
        SizedBox(
          height: 280,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            itemCount: movies.length,
            itemBuilder: (context, index) {
              final movie = movies[index];
              return FocusableWrapper(
                onTap: () => context.push('/movie/${movie.slug}'),
                child: YoukuMovieCard(
                  movie: movie,
                  domain: domain,
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildLargeHorizontalList(String title, List<dynamic> movies, String domain) {
    if (movies.isEmpty) return const SizedBox();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 16.0, top: 24.0, bottom: 16.0),
          child: Row(
            children: [
              const Icon(Icons.auto_awesome, color: Colors.amber, size: 24),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(
                  fontSize: 20, 
                  fontWeight: FontWeight.bold, 
                  color: Theme.of(context).brightness == Brightness.dark ? Colors.amberAccent : Colors.orange[800]
                ),
              ),
            ],
          ),
        ),
        SizedBox(
          height: 250,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            itemCount: movies.length,
            itemBuilder: (context, index) {
              final movie = movies[index];
              return FocusableWrapper(
                onTap: () => context.push('/movie/${movie.slug}'),
                child: YoukuMovieCard(
                  movie: movie,
                  domain: domain,
                  isFeatured: true,
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildGridList(String title, List<dynamic> movies, String domain) {
    if (movies.isEmpty) return const SizedBox();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 16.0, top: 24.0, bottom: 16.0),
          child: Text(
            title,
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black),
          ),
        ),
        SizedBox(
          height: 560,
          child: GridView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 280 / 160,
              mainAxisSpacing: 16,
              crossAxisSpacing: 16,
            ),
            itemCount: movies.length,
            itemBuilder: (context, index) {
              final movie = movies[index];
              return FocusableWrapper(
                onTap: () => context.push('/movie/${movie.slug}'),
                child: YoukuMovieCard(
                  movie: movie,
                  domain: domain,
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildHistoryList() {
    if (_history.isEmpty) return const SizedBox();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 16.0, top: 24.0, bottom: 16.0),
          child: Row(
            children: [
              Icon(Icons.history, color: Theme.of(context).primaryColor, size: 24),
              const SizedBox(width: 8),
              Text(
                "Tiếp tục xem",
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black),
              ),
            ],
          ),
        ),
        SizedBox(
          height: 180,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            itemCount: _history.length,
            itemBuilder: (context, index) {
              final item = _history[index];
              final progress = item.duration > 0 ? (item.currentTime / item.duration).clamp(0.0, 1.0) : 0.0;
              final thumbUrl = item.thumbUrl.startsWith('http') ? item.thumbUrl : 'https://phimimg.com/${item.thumbUrl}';
              
              return FocusableWrapper(
                onTap: () => context.push('/movie/${item.movieSlug}'),
                child: Container(
                  width: 200,
                  margin: const EdgeInsets.only(right: 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Stack(
                            fit: StackFit.expand,
                            children: [
                              Image.network(
                                thumbUrl,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => Container(color: Theme.of(context).brightness == Brightness.dark ? Colors.grey[900] : Colors.grey[200], child: const Icon(Icons.movie, color: Colors.grey)),
                              ),
                              Container(
                                color: Colors.black38,
                                child: const Center(
                                  child: Icon(Icons.play_circle_outline, size: 48, color: Colors.white70),
                                ),
                              ),
                              if (item.duration > 0)
                                Positioned(
                                  bottom: 0, left: 0, right: 0,
                                  child: LinearProgressIndicator(
                                    value: progress,
                                    backgroundColor: Colors.black54,
                                    color: Theme.of(context).primaryColor,
                                    minHeight: 4,
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        item.movieName,
                        style: TextStyle(color: Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black, fontWeight: FontWeight.bold, fontSize: 14),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        item.episodeName,
                        style: TextStyle(color: Theme.of(context).brightness == Brightness.dark ? Colors.grey : Colors.grey[700], fontSize: 12),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildTextLogo(BuildContext context) {
    return Row(
      children: [
        Text(
          "PHIM",
          style: TextStyle(color: Theme.of(context).brightness == Brightness.dark ? Colors.white : Colors.black, fontWeight: FontWeight.bold),
        ),
        Text(
          "TOP1",
          style: TextStyle(color: Theme.of(context).primaryColor, fontWeight: FontWeight.bold),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Consumer<HomeProvider>(
          builder: (context, provider, child) {
            if (provider.logoUrl.isNotEmpty) {
              final logo = provider.logoUrl;
              final fullUrl = logo.startsWith('http') ? logo : '${AppConfig.baseUrl}${logo.startsWith('/') ? '' : '/'}$logo';
              return Image.network(
                fullUrl,
                height: 32,
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) => _buildTextLogo(context),
              );
            }
            return _buildTextLogo(context);
          },
        ),
        actions: [
          const TvCastButton(),
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () {
              context.push('/search');
            },
          )
        ],
      ),
      body: Consumer<HomeProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }
          if (provider.error != null) {
            return ErrorView(
              error: provider.error!,
              onRetry: provider.fetchHomeData,
            );
          }

          return RefreshIndicator(
            onRefresh: provider.fetchHomeData,
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Featured Slider
                  if (provider.featuredMovies.isNotEmpty) 
                    FeaturedSlider(
                      movies: provider.featuredMovies,
                      domain: provider.domain,
                    ),

                  if (_history.isNotEmpty) _buildHistoryList(),

                  _buildHorizontalList("Phim Mới Cập Nhật", provider.normalMovies, provider.domain),
                  _buildGridList("Bảng Xếp Hạng", provider.trendingMovies, provider.domain),
                  if (provider.recommendedMovies.isNotEmpty) _buildLargeHorizontalList("Dành Riêng Cho Bạn", provider.recommendedMovies, provider.domain),
                  _buildHorizontalList("Phim Bộ Mới Nhất", provider.phimBo, provider.domain),
                  _buildGridList("Phim Lẻ Mới Nhất", provider.phimLe, provider.domain),
                  _buildHorizontalList("TV Shows", provider.tvShows, provider.domain),
                  _buildHorizontalList("Phim Hoạt Hình", provider.hoatHinh, provider.domain),
                  const SizedBox(height: 32),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
