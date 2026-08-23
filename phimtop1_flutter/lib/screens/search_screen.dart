import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:speech_to_text/speech_to_text.dart' as stt;
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

  void _openVoiceSearch() {
    if (_isTvMode(context)) {
      showDialog(
        context: context,
        builder: (context) => Dialog(
          backgroundColor: Colors.transparent,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(24),
            child: VoiceSearchBottomSheet(
              onResult: (text) {
                _controller.text = text;
                context.read<ExploreProvider>().setFilters(searchKeyword: text);
                setState(() {});
              },
            ),
          ),
        ),
      );
    } else {
      showModalBottomSheet(
        context: context,
        backgroundColor: Colors.transparent,
        isScrollControlled: true,
        builder: (context) => VoiceSearchBottomSheet(
          onResult: (text) {
            _controller.text = text;
            context.read<ExploreProvider>().setFilters(searchKeyword: text);
            setState(() {});
          },
        ),
      );
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

        if (!isTv && !isSearching) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final suggestions = provider.trendingMovies.take(8).map((e) => e.name).toList();
          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "Gợi ý tìm kiếm",
                  style: TextStyle(
                    color: isDark ? Colors.white : Colors.black87,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
                Wrap(
                  spacing: 10,
                  runSpacing: 10,
                  children: suggestions.map((s) => InkWell(
                    onTap: () {
                      _controller.text = s;
                      context.read<ExploreProvider>().setFilters(searchKeyword: s);
                      setState(() {});
                    },
                    borderRadius: BorderRadius.circular(20),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                        color: isDark ? Colors.white.withOpacity(0.1) : Colors.black.withOpacity(0.05),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: isDark ? Colors.white24 : Colors.black12),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.trending_up, size: 16, color: Theme.of(context).primaryColor),
                          const SizedBox(width: 6),
                          Text(
                            s,
                            style: TextStyle(
                              color: isDark ? Colors.white : Colors.black87,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                  )).toList(),
                ),
              ],
            ),
          );
        }
        if (isSearching && provider.movies.isEmpty) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          return Center(child: Text("Không tìm thấy kết quả nào", style: TextStyle(color: isDark ? Colors.white70 : Colors.black54)));
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
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

class VoiceSearchBottomSheet extends StatefulWidget {
  final Function(String) onResult;
  const VoiceSearchBottomSheet({super.key, required this.onResult});

  @override
  State<VoiceSearchBottomSheet> createState() => _VoiceSearchBottomSheetState();
}

class _VoiceSearchBottomSheetState extends State<VoiceSearchBottomSheet> with SingleTickerProviderStateMixin {
  final stt.SpeechToText _speech = stt.SpeechToText();
  bool _isListening = false;
  String _text = "Hãy nói tên phim...";
  late AnimationController _animationController;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    )..repeat(reverse: true);
    _startListening();
  }

  void _startListening() async {
    bool available = await _speech.initialize(
      onError: (e) => debugPrint("Lỗi STT: \${e.errorMsg}"),
      onStatus: (s) {
        if (s == "done" || s == "notListening") {
          setState(() => _isListening = false);
        }
      },
    );
    if (available) {
      setState(() {
        _isListening = true;
        _text = "Đang nghe...";
      });
      _speech.listen(
        onResult: (result) {
          setState(() {
            _text = result.recognizedWords.isNotEmpty ? result.recognizedWords : "Hãy nói tên phim...";
          });
          if (result.finalResult && result.recognizedWords.isNotEmpty) {
            widget.onResult(result.recognizedWords);
            Future.delayed(const Duration(milliseconds: 500), () {
              if (mounted) Navigator.pop(context);
            });
          }
        },
        localeId: 'vi_VN',
        listenFor: const Duration(seconds: 15),
        pauseFor: const Duration(seconds: 3),
      );
    } else {
      setState(() {
        _isListening = false;
        _text = "Micrô không khả dụng";
      });
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    _speech.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? Colors.white : Colors.black;

    return Container(
      padding: const EdgeInsets.all(24),
      height: 350,
      decoration: BoxDecoration(
        color: Theme.of(context).scaffoldBackgroundColor,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.g_translate, size: 24, color: isDark ? Colors.white70 : Colors.black54),
              const SizedBox(width: 8),
              Text("Google Speech Services", style: TextStyle(color: isDark ? Colors.white70 : Colors.black54, fontSize: 16)),
            ],
          ),
          const SizedBox(height: 32),
          Text(
            _text,
            style: TextStyle(color: textColor, fontSize: 28, fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const Spacer(),
          AnimatedBuilder(
            animation: _animationController,
            builder: (context, child) {
              final double scale = _isListening ? 1.0 + (_animationController.value * 0.4) : 1.0;
              return Container(
                width: 120,
                height: 120,
                alignment: Alignment.center,
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    if (_isListening)
                      Container(
                        width: 80 * scale,
                        height: 80 * scale,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Theme.of(context).primaryColor.withOpacity(1.0 - _animationController.value),
                        ),
                      ),
                    FloatingActionButton(
                      onPressed: _isListening ? () {
                        _speech.stop();
                        Navigator.pop(context);
                      } : _startListening,
                      backgroundColor: _isListening ? Colors.red : Theme.of(context).primaryColor,
                      elevation: _isListening ? 8 : 4,
                      child: Icon(
                        _isListening ? Icons.mic : Icons.mic_none,
                        size: 36,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

