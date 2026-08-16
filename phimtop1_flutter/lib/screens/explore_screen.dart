import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../providers/explore_provider.dart';
import '../widgets/movie_card.dart';
import '../widgets/focusable_wrapper.dart';
import '../widgets/tv_cast_button.dart';
import '../widgets/youtube_tv_movie_card.dart';
import '../widgets/error_view.dart';

class ExploreScreen extends StatelessWidget {
  const ExploreScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _isTvMode(context) ? const Color(0xFF0F0F0F) : Theme.of(context).scaffoldBackgroundColor,
      appBar: AppBar(
        backgroundColor: _isTvMode(context) ? const Color(0xFF0F0F0F) : null,
        title: const Text("Khám Phá", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
      body: Consumer<ExploreProvider>(
        builder: (context, provider, child) {
          return Column(
            children: [
              // Filters Section
              _buildFilters(context, provider),
              
              // Content Section
              Expanded(
                child: provider.isLoading
                    ? const Center(child: CircularProgressIndicator())
                    : provider.error != null
                        ? ErrorView(error: provider.error!, onRetry: () => provider.fetchMovies(reset: true))
                        : provider.movies.isEmpty && !provider.isTrendingLoading && provider.trendingMovies.isNotEmpty && _isFiltersEmpty(provider)
                            ? _buildTrending(provider, context)
                            : provider.movies.isEmpty
                                ? const Center(child: Text("Không có dữ liệu", style: TextStyle(color: Colors.white70)))
                                : _buildMovieGrid(provider, context),
              ),
            ],
          );
        },
      ),
    );
  }

  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800 && size.shortestSide >= 500;
  }

  bool _isFiltersEmpty(ExploreProvider provider) {
    return provider.keyword.isEmpty && 
           provider.activeType == "phim-moi-cap-nhat" && 
           provider.activeGenre.isEmpty && 
           provider.activeCountry.isEmpty && 
           provider.activeYear.isEmpty;
  }

  Widget _buildFilters(BuildContext context, ExploreProvider provider) {
    final types = [
      {"slug": "phim-moi-cap-nhat", "name": "Mới Cập Nhật"},
      {"slug": "phim-bo", "name": "Phim Bộ"},
      {"slug": "phim-le", "name": "Phim Lẻ"},
      {"slug": "hoat-hinh", "name": "Hoạt Hình"},
      {"slug": "tv-shows", "name": "TV Shows"},
    ];

    final genres = provider.allCategories.where((c) => c.type == "genre").toList();
    final countries = provider.allCategories.where((c) => c.type == "country").toList();
    
    // Generate years from current year down to 2010
    final currentYear = DateTime.now().year;
    final years = List.generate(currentYear - 2010 + 1, (index) => (currentYear - index).toString());

    return Container(
      color: const Color(0xFF151515),
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Column(
        children: [
          _buildFilterRow(
            items: types,
            selectedValue: provider.activeType,
            onSelected: (val) => provider.setFilters(type: val),
            valueKey: "slug",
            labelKey: "name",
          ),
          if (genres.isNotEmpty)
            _buildFilterRow(
              items: [{"slug": "", "name": "Tất cả thể loại"}, ...genres.map((e) => {"slug": e.slug, "name": e.name})],
              selectedValue: provider.activeGenre,
              onSelected: (val) => provider.setFilters(genre: val),
              valueKey: "slug",
              labelKey: "name",
            ),
          if (countries.isNotEmpty)
            _buildFilterRow(
              items: [{"slug": "", "name": "Tất cả quốc gia"}, ...countries.map((e) => {"slug": e.slug, "name": e.name})],
              selectedValue: provider.activeCountry,
              onSelected: (val) => provider.setFilters(country: val),
              valueKey: "slug",
              labelKey: "name",
            ),
          _buildFilterRow(
            items: [{"slug": "", "name": "Tất cả năm"}, ...years.map((e) => {"slug": e, "name": e})],
            selectedValue: provider.activeYear,
            onSelected: (val) => provider.setFilters(year: val),
            valueKey: "slug",
            labelKey: "name",
          ),
        ],
      ),
    );
  }

  Widget _buildFilterRow({
    required List<Map<String, dynamic>> items,
    required String selectedValue,
    required Function(String) onSelected,
    required String valueKey,
    required String labelKey,
  }) {
    return SizedBox(
      height: 40,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        itemCount: items.length,
        itemBuilder: (context, index) {
          final item = items[index];
          final value = item[valueKey] as String;
          final label = item[labelKey] as String;
          final isSelected = selectedValue == value;

          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              label: Text(label),
              selected: isSelected,
              selectedColor: Theme.of(context).primaryColor,
              backgroundColor: Colors.grey[900],
              labelStyle: TextStyle(
                color: isSelected ? Colors.white : Colors.white70,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
              ),
              onSelected: (_) => onSelected(value),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              showCheckmark: false,
            ),
          );
        },
      ),
    );
  }

  Widget _buildTrending(ExploreProvider provider, BuildContext context) {
    final isTv = _isTvMode(context);
    return GridView.builder(
      padding: const EdgeInsets.all(16),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: isTv ? 4 : 3,
        childAspectRatio: isTv ? 1.3 : 0.6,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
      ),
      itemCount: provider.trendingMovies.length,
      itemBuilder: (context, index) {
        final movie = provider.trendingMovies[index];
        return FocusableWrapper(
          onTap: () => context.push('/movie/${movie.slug}'),
          child: isTv
              ? YoutubeTvMovieCard(movie: movie, domain: provider.domain)
              : YoukuMovieCard(movie: movie, domain: provider.domain),
        );
      },
    );
  }

  Widget _buildMovieGrid(ExploreProvider provider, BuildContext context) {
    final isTv = _isTvMode(context);
    return GridView.builder(
      padding: const EdgeInsets.all(16),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: isTv ? 4 : 3,
        childAspectRatio: isTv ? 1.3 : 0.6,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
      ),
      itemCount: provider.movies.length,
      itemBuilder: (context, index) {
        final movie = provider.movies[index];
        return FocusableWrapper(
          onTap: () => context.push('/movie/${movie.slug}'),
          child: isTv
              ? YoutubeTvMovieCard(movie: movie, domain: provider.domain)
              : YoukuMovieCard(movie: movie, domain: provider.domain),
        );
      },
    );
  }
}
