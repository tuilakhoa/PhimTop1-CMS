import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/home_provider.dart';
import '../widgets/movie_card.dart';
import '../widgets/focusable_wrapper.dart';
import '../widgets/tv_cast_button.dart';
import '../widgets/featured_slider.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<HomeProvider>().fetchHomeData();
    });
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
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

                  _buildHorizontalList("Phim Mới Cập Nhật", provider.normalMovies, provider.domain),
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
