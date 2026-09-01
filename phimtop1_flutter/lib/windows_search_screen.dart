import 'package:flutter/material.dart' show ThemeMode, Material, Brightness, CrossAxisAlignment, MainAxisAlignment, BorderRadius;
import 'package:fluent_ui/fluent_ui.dart';
import 'package:provider/provider.dart';
import '../providers/explore_provider.dart';
import '../models/models.dart';
import 'windows_detail_screen.dart';

class WindowsSearchScreen extends StatefulWidget {
  const WindowsSearchScreen({Key? key}) : super(key: key);

  @override
  State<WindowsSearchScreen> createState() => _WindowsSearchScreenState();
}

class _WindowsSearchScreenState extends State<WindowsSearchScreen> {
  final Map<String, String> typeMap = {
    'Tất cả': 'phim-moi-cap-nhat',
    'Phim Lẻ': 'phim-le',
    'Phim Bộ': 'phim-bo',
    'TV Show': 'tv-shows',
    'Hoạt Hình': 'hoat-hinh'
  };

  final Map<String, String> genreMap = {
    'Hành Động': 'hanh-dong',
    'Phiêu Lưu': 'phieu-luu',
    'Hài Hước': 'hai-huoc',
    'Viễn Tưởng': 'vien-tuong',
    'Tâm Lý': 'tam-ly',
    'Kinh Dị': 'kinh-di',
  };

  final Map<String, String> countryMap = {
    'Tất cả': '',
    'Âu Mỹ': 'au-my',
    'Hàn Quốc': 'han-quoc',
    'Trung Quốc': 'trung-quoc',
    'Nhật Bản': 'nhat-ban',
    'Việt Nam': 'viet-nam'
  };

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ExploreProvider>().fetchMovies();
    });
  }

  String _getThumb(MovieItem m, String domain) {
    if (m.thumbUrl == null || m.thumbUrl!.isEmpty) return '';
    if (m.thumbUrl!.startsWith('http')) return m.thumbUrl!;
    final baseDomain = domain.isNotEmpty ? domain : 'https://phimimg.com';
    return '$baseDomain/${m.thumbUrl}';
  }

  @override
  Widget build(BuildContext context) {
    final exploreProvider = context.watch<ExploreProvider>();

    // Helper to find key by value
    String currentTypeLabel = typeMap.keys.firstWhere(
        (k) => typeMap[k] == exploreProvider.activeType, orElse: () => 'Tất cả');
    String currentCountryLabel = countryMap.keys.firstWhere(
        (k) => countryMap[k] == exploreProvider.activeCountry, orElse: () => 'Tất cả');

    return ScaffoldPage(
      padding: EdgeInsets.zero,
      content: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // A. Khu Vực Tiêu Đề & Nút Danh Mục (Top)
          Padding(
            padding: const EdgeInsets.all(24.0),
            child: Row(
              children: [
                ...typeMap.keys.map((c) => _buildCategoryPill(c, currentTypeLabel, exploreProvider)),
                const Spacer(),
                Text(
                  'Kết quả cho: "${exploreProvider.keyword.isEmpty ? 'Tất cả' : exploreProvider.keyword}"',
                  style: const TextStyle(color: Colors.white, fontStyle: FontStyle.italic),
                ),
              ],
            ),
          ),
          
          Expanded(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // B. Bảng Bộ Lọc (Left Sidebar)
                Container(
                  width: 250,
                  padding: const EdgeInsets.symmetric(horizontal: 24.0),
                  child: SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('BỘ LỌC TÌM KIẾM', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.white)),
                            Button(
                              onPressed: () {
                                exploreProvider.setFilters(
                                  type: 'phim-moi-cap-nhat',
                                  genre: '',
                                  country: '',
                                  year: '',
                                  searchKeyword: ''
                                );
                              },
                              style: ButtonStyle(backgroundColor: WidgetStateProperty.all(Colors.transparent)),
                              child: const Text('Xóa tất cả', style: TextStyle(color: Color(0xFF6B48FF), fontSize: 12)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        
                        // LOẠI
                        const Text('LOẠI', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8, runSpacing: 8,
                          children: typeMap.keys.map((c) => _buildFilterPill(c, c == currentTypeLabel, exploreProvider)).toList(),
                        ),
                        const SizedBox(height: 24),
                        
                        // THỂ LOẠI
                        const Text('THỂ LOẠI', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                        const SizedBox(height: 8),
                        Checkbox(
                          checked: exploreProvider.activeGenre.isEmpty,
                          onChanged: (v) {
                            if (v == true) exploreProvider.setFilters(genre: '');
                          },
                          content: const Text('Tất cả', style: TextStyle(color: Colors.white)),
                        ),
                        ...genreMap.entries.map((entry) => Padding(
                          padding: const EdgeInsets.only(top: 8.0),
                          child: Checkbox(
                            checked: exploreProvider.activeGenre == entry.value,
                            onChanged: (v) {
                              if (v == true) exploreProvider.setFilters(genre: entry.value);
                            },
                            content: Text(entry.key, style: const TextStyle(color: Colors.white)),
                          ),
                        )),
                        const Padding(
                          padding: EdgeInsets.only(top: 8.0),
                          child: Text('Xem thêm v', style: TextStyle(color: Color(0xFF6B48FF), fontSize: 12)),
                        ),
                        const SizedBox(height: 24),
                        
                        // NĂM PHÁT HÀNH
                        const Text('NĂM PHÁT HÀNH', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                        const SizedBox(height: 8),
                        Slider(
                          value: exploreProvider.activeYear.isNotEmpty ? double.parse(exploreProvider.activeYear) : 2025,
                          min: 1999,
                          max: 2025,
                          onChanged: (v) {
                            exploreProvider.setFilters(year: v.toInt().toString());
                          },
                        ),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('1999', style: TextStyle(color: Colors.grey, fontSize: 12)),
                            Text('${exploreProvider.activeYear.isNotEmpty ? exploreProvider.activeYear : "2025"}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                          ],
                        ),
                        const SizedBox(height: 24),
                        
                        // QUỐC GIA
                        const Text('QUỐC GIA', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                        const SizedBox(height: 8),
                        ComboBox<String>(
                          value: currentCountryLabel,
                          items: countryMap.keys.map((e) => ComboBoxItem(value: e, child: Text(e))).toList(),
                          onChanged: (v) {
                            exploreProvider.setFilters(country: countryMap[v!]);
                          },
                          isExpanded: true,
                        ),
                        const SizedBox(height: 24),
                        
                        // SẮP XẾP
                        const Text('SẮP XẾP', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                        const SizedBox(height: 8),
                        ComboBox<String>(
                          value: 'Liên quan nhất',
                          items: ['Liên quan nhất', 'Mới nhất', 'Xem nhiều nhất'].map((e) => ComboBoxItem(value: e, child: Text(e))).toList(),
                          onChanged: (v) {},
                          isExpanded: true,
                        ),
                        const SizedBox(height: 40),
                      ],
                    ),
                  ),
                ),
                
                // C. Lưới Kết Quả (Right Content)
                Expanded(
                  child: Column(
                    children: [
                      // Thanh công cụ kết quả
                      Padding(
                        padding: const EdgeInsets.only(right: 24.0, bottom: 16.0),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Kết quả tìm kiếm (${exploreProvider.movies.length} kết quả)',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                            ),
                            Row(
                              children: [
                                const Icon(FluentIcons.view_all, color: Colors.white, size: 16),
                                const SizedBox(width: 8),
                                const Icon(FluentIcons.list, color: Colors.grey, size: 16),
                                const SizedBox(width: 16),
                                ComboBox<String>(
                                  value: 'Liên quan nhất',
                                  items: ['Liên quan nhất', 'Mới nhất'].map((e) => ComboBoxItem(value: e, child: Text(e))).toList(),
                                  onChanged: (v) {},
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      
                      // Lưới phim
                      Expanded(
                        child: exploreProvider.isLoading
                          ? const Center(child: ProgressRing())
                          : exploreProvider.error != null
                              ? Center(child: Text(exploreProvider.error!))
                              : exploreProvider.movies.isEmpty
                                  ? const Center(child: Text('Không tìm thấy kết quả nào.'))
                                  : GridView.builder(
                                      padding: const EdgeInsets.only(right: 24.0, bottom: 24.0),
                                      gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                                        maxCrossAxisExtent: 220,
                                        childAspectRatio: 0.55,
                                        crossAxisSpacing: 16,
                                        mainAxisSpacing: 16,
                                      ),
                                      itemCount: exploreProvider.movies.length,
                                      itemBuilder: (context, index) {
                                        final movie = exploreProvider.movies[index];
                                        return _buildSearchMovieCard(context, movie, exploreProvider.domain);
                                      },
                                    ),
                      ),
                      
                      // Pagination
                      if (!exploreProvider.isLoading && exploreProvider.movies.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.symmetric(vertical: 24.0),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              _buildPageButton('1', true),
                              _buildPageButton('2', false),
                              _buildPageButton('3', false),
                              _buildPageButton('>', false),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryPill(String title, String currentTypeLabel, ExploreProvider provider) {
    final isSelected = title == currentTypeLabel;
    return Padding(
      padding: const EdgeInsets.only(right: 8.0),
      child: FilledButton(
        style: ButtonStyle(
          backgroundColor: WidgetStateProperty.all(isSelected ? const Color(0xFF6B48FF) : const Color(0xFF161623)),
          padding: WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 16, vertical: 8)),
        ),
        onPressed: () {
          provider.setFilters(type: typeMap[title]);
        },
        child: Text(title, style: const TextStyle(color: Colors.white)),
      ),
    );
  }

  Widget _buildFilterPill(String title, bool isSelected, ExploreProvider provider) {
    return GestureDetector(
      onTap: () {
        provider.setFilters(type: typeMap[title]);
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF6B48FF) : const Color(0xFF161623),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: isSelected ? const Color(0xFF6B48FF) : const Color(0xFF2A2A3A)),
        ),
        child: Text(title, style: const TextStyle(color: Colors.white, fontSize: 12)),
      ),
    );
  }

  Widget _buildPageButton(String label, bool isSelected) {
    return Container(
      width: 36, height: 36,
      margin: const EdgeInsets.symmetric(horizontal: 4),
      decoration: BoxDecoration(
        color: isSelected ? const Color(0xFF6B48FF) : const Color(0xFF161623),
        borderRadius: BorderRadius.circular(8),
      ),
      alignment: Alignment.center,
      child: Text(label, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
    );
  }

  Widget _buildSearchMovieCard(BuildContext context, MovieItem movie, String domain) {
    return HoverButton(
      onPressed: () {
        Navigator.push(context, FluentPageRoute(builder: (_) => WindowsDetailScreen(movieSlug: movie.slug)));
      },
      builder: (context, states) {
        return AnimatedScale(
          scale: states.isHovered ? 1.03 : 1.0,
          duration: const Duration(milliseconds: 200),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Image Container (Bo góc, có Badge và Rating)
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    color: const Color(0xFF161623),
                    boxShadow: states.isHovered
                        ? [BoxShadow(color: const Color(0xFF6B48FF).withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 4))]
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
                      // Badge (Góc trái trên)
                      Positioned(
                        top: 8, left: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF6B48FF),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Text('NEW', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                        ),
                      ),
                      // Rating (Góc phải dưới)
                      Positioned(
                        bottom: 8, right: 8,
                        child: Row(
                          children: [
                            const Icon(FluentIcons.favorite_star_fill, color: Color(0xFFFFD700), size: 12),
                            const SizedBox(width: 4),
                            Text('9.4', style: const TextStyle(color: Color(0xFFFFD700), fontSize: 12, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),
              // Text phía dưới ảnh
              Text(
                movie.name,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 4),
              Text(
                '${movie.year ?? 2024} - Phim',
                style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 12),
              ),
            ],
          ),
        );
      },
    );
  }
}
