import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter/services.dart';
import '../providers/explore_provider.dart';
import '../widgets/movie_card.dart';
import '../widgets/focusable_wrapper.dart';
import '../widgets/youtube_tv_movie_card.dart';
import '../widgets/tv_virtual_keyboard.dart';
import '../widgets/error_view.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final TextEditingController _controller = TextEditingController();

  @override
  void initState() {
    super.initState();
    _controller.text = context.read<ExploreProvider>().keyword;
  }

  static const platform = MethodChannel('com.phimtop1.app/speech');

  void _openVoiceSearch() async {
    try {
      final String result = await platform.invokeMethod('startVoiceSearch');
      if (result.isNotEmpty && mounted) {
        _controller.text = result;
        context.read<ExploreProvider>().setFilters(searchKeyword: result);
        setState(() {});
      }
    } on PlatformException catch (e) {
      debugPrint("Voice search error: '${e.message}'.");
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Không thể mở tìm kiếm giọng nói: ${e.message}')),
        );
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
  
  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800 && size.shortestSide >= 500;
  }

  Widget _buildTvHeader() {
    return Padding(
      padding: const EdgeInsets.only(top: 32, right: 32, left: 16, bottom: 24),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              // Mic Button
              InkWell(
                onTap: _openVoiceSearch,
                borderRadius: BorderRadius.circular(24),
                child: Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.mic,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
              ),
              const SizedBox(width: 16),
              // Search Input
              Container(
                width: 300,
                height: 48,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(24),
                ),
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: TextField(
                  controller: _controller,
                  autofocus: true,
                  readOnly: true, // Prevent system keyboard on TV
                  showCursor: true,
                  style: const TextStyle(color: Colors.white, fontSize: 16),
                  decoration: InputDecoration(
                    hintText: "Tìm kiếm",
                    hintStyle: const TextStyle(color: Colors.white54),
                    border: InputBorder.none,
                    suffixIcon: _controller.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, color: Colors.white54, size: 20),
                            onPressed: () {
                              _controller.clear();
                              context.read<ExploreProvider>().setFilters(searchKeyword: "");
                              setState(() {});
                            },
                          )
                        : null,
                  ),
                  onChanged: (val) {
                    context.read<ExploreProvider>().setFilters(searchKeyword: val);
                    setState(() {});
                  },
                ),
              ),
            ],
          ),
          // App Logo
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
    );
  }

  Widget _buildTvMiddleRow() {
    final provider = context.watch<ExploreProvider>();
    final trending = provider.trendingMovies;
    // Extract some unique names for suggestions
    final suggestions = trending.take(5).map((e) => e.name).toList();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Left side: Suggestions
          Expanded(
            flex: 1,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Padding(
                  padding: EdgeInsets.only(bottom: 16),
                  child: Text("Từ khóa gợi ý", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                ),
                ...suggestions.map((s) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Focus(
                    child: Builder(
                      builder: (context) {
                        final hasFocus = Focus.of(context).hasFocus;
                        return InkWell(
                          onTap: () {
                            _controller.text = s;
                            context.read<ExploreProvider>().setFilters(searchKeyword: s);
                            setState(() {});
                          },
                          borderRadius: BorderRadius.circular(20),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            decoration: BoxDecoration(
                              color: hasFocus ? Colors.white : Colors.white.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.history, size: 16, color: hasFocus ? Colors.black : Colors.white70),
                                const SizedBox(width: 8),
                                Flexible(
                                  child: Text(
                                    s,
                                    style: TextStyle(color: hasFocus ? Colors.black : Colors.white),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }
                    ),
                  ),
                )).toList(),
              ],
            ),
          ),
          
          // Right side: Virtual Keyboard
          Expanded(
            flex: 2,
            child: TvVirtualKeyboard(
              text: _controller.text,
              onTextChanged: (newText) {
                _controller.text = newText;
                context.read<ExploreProvider>().setFilters(searchKeyword: newText);
                setState(() {});
              },
              onSearch: () {},
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBody(bool isTv) {
    return Consumer<ExploreProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(child: CircularProgressIndicator());
        }
        if (provider.error != null) {
          return ErrorView(error: provider.error!, onRetry: () => provider.fetchMovies(reset: true));
        }

        final isSearching = provider.keyword.isNotEmpty;
        final displayList = isSearching ? provider.movies : provider.trendingMovies;

        if (isSearching && provider.movies.isEmpty) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          return Center(child: Text("Không tìm thấy kết quả nào", style: TextStyle(color: isDark ? Colors.white70 : Colors.black54)));
        }

        final isDark = Theme.of(context).brightness == Brightness.dark;
        final suggestions = isSearching 
            ? displayList.take(6).map((e) => e.name).toList()
            : provider.trendingMovies.take(8).map((e) => e.name).toList();

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (!isTv && suggestions.isNotEmpty)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isSearching ? "Có phải bạn muốn tìm" : "Gợi ý tìm kiếm",
                      style: TextStyle(
                        color: isDark ? Colors.white : Colors.black87,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: suggestions.map((s) => InkWell(
                        onTap: () {
                          _controller.text = s;
                          context.read<ExploreProvider>().setFilters(searchKeyword: s);
                          setState(() {});
                        },
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width - 32),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          decoration: BoxDecoration(
                            color: isDark ? Colors.white.withOpacity(0.1) : Colors.black.withOpacity(0.05),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: isDark ? Colors.white24 : Colors.black12),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(isSearching ? Icons.search : Icons.trending_up, size: 16, color: Theme.of(context).primaryColor),
                              const SizedBox(width: 6),
                              Flexible(
                                child: Text(
                                  s,
                                  style: TextStyle(
                                    color: isDark ? Colors.white : Colors.black87,
                                    fontSize: 14,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ),
                      )).toList(),
                    ),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            if (isTv)
              Padding(
                padding: const EdgeInsets.only(left: 16, bottom: 8, top: 16),
                child: Text(
                  isSearching ? "Kết quả tìm kiếm" : "Phim Đề Cử",
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            Expanded(
              child: GridView.builder(
                padding: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: isTv ? 4 : 3,
                  childAspectRatio: isTv ? 1.3 : 0.6,
                  crossAxisSpacing: 16,
                  mainAxisSpacing: 16,
                ),
                itemCount: displayList.length,
                itemBuilder: (context, index) {
                  final movie = displayList[index];
                  return FocusableWrapper(
                    onTap: () => context.push('/movie/${movie.slug}'),
                    child: isTv
                        ? YoutubeTvMovieCard(movie: movie, domain: provider.domain)
                        : YoukuMovieCard(movie: movie, domain: provider.domain),
                  );
                },
              ),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isTv = _isTvMode(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isTv ? const Color(0xFF0F0F0F) : Theme.of(context).scaffoldBackgroundColor,
      appBar: isTv
          ? null
          : AppBar(
              title: TextField(
                controller: _controller,
                autofocus: false,
                style: TextStyle(color: isDark ? Colors.white : Colors.black),
                decoration: InputDecoration(
                  hintText: "Nhập tên phim...",
                  hintStyle: TextStyle(color: isDark ? Colors.white54 : Colors.black54),
                  border: InputBorder.none,
                  suffixIcon: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: Icon(
                          Icons.mic,
                          color: Theme.of(context).primaryColor,
                        ),
                        onPressed: _openVoiceSearch,
                      ),
                      IconButton(
                        icon: Icon(Icons.clear, color: isDark ? Colors.white54 : Colors.black54),
                        onPressed: () {
                          _controller.clear();
                          context.read<ExploreProvider>().setFilters(searchKeyword: "");
                          setState(() {});
                        },
                      ),
                    ],
                  ),
                ),
                onChanged: (val) {
                  context.read<ExploreProvider>().setFilters(searchKeyword: val);
                  setState(() {});
                },
              ),
            ),
      body: isTv
          ? Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildTvHeader(),
                _buildTvMiddleRow(),
                Expanded(child: _buildBody(isTv)),
              ],
            )
          : _buildBody(isTv),
    );
  }
}

