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

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<HistoryItem> _history = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<HomeProvider>().fetchHomeData();
      _fetchHistory();
    });
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
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white),
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

  Widget _buildHistoryList() {
    if (_history.isEmpty) return const SizedBox();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.only(left: 16.0, top: 24.0, bottom: 16.0),
          child: Row(
            children: [
              Icon(Icons.history, color: Colors.red, size: 24),
              SizedBox(width: 8),
              Text(
                "Tiếp tục xem",
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white),
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
                                errorBuilder: (_, __, ___) => Container(color: Colors.grey[900], child: const Icon(Icons.movie)),
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
                                    color: Colors.red,
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
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        item.episodeName,
                        style: const TextStyle(color: Colors.grey, fontSize: 12),
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
        const Text(
          "PHIM",
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
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
            return Center(child: Text(provider.error!, style: const TextStyle(color: Colors.red)));
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
                  _buildHorizontalList("Bảng Xếp Hạng", provider.trendingMovies, provider.domain),
                  _buildHorizontalList("Phim Bộ Mới Nhất", provider.phimBo, provider.domain),
                  _buildHorizontalList("Phim Lẻ Mới Nhất", provider.phimLe, provider.domain),
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
