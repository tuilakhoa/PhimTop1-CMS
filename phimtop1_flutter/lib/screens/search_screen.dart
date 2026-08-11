import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:speech_to_text/speech_to_text.dart' as stt;
import '../providers/explore_provider.dart';
import '../widgets/movie_card.dart';
import '../widgets/focusable_wrapper.dart';
import '../widgets/youtube_tv_movie_card.dart';
import '../widgets/tv_virtual_keyboard.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final TextEditingController _controller = TextEditingController();
  final stt.SpeechToText _speech = stt.SpeechToText();
  bool _isListening = false;
  String _lastWords = '';

  @override
  void initState() {
    super.initState();
    _controller.text = context.read<ExploreProvider>().keyword;
    _initSpeech();
  }

  void _initSpeech() async {
    try {
      await _speech.initialize();
    } catch (e) {
      debugPrint("Speech init error: $e");
    }
  }

  void _startListening() async {
    if (!_speech.isAvailable) {
      bool available = await _speech.initialize();
      if (!available) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Không thể khởi tạo micrô')),
          );
        }
        return;
      }
    }
    
    setState(() => _isListening = true);
    await _speech.listen(
      onResult: (result) {
        setState(() {
          _lastWords = result.recognizedWords;
          if (result.finalResult) {
            _controller.text = _lastWords;
            context.read<ExploreProvider>().setFilters(searchKeyword: _lastWords);
            _isListening = false;
          }
        });
      },
      localeId: 'vi_VN',
    );
  }

  void _stopListening() async {
    await _speech.stop();
    setState(() => _isListening = false);
  }

  @override
  void dispose() {
    _controller.dispose();
    _speech.cancel();
    super.dispose();
  }
  
  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800;
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
                onTap: _isListening ? _stopListening : _startListening,
                borderRadius: BorderRadius.circular(24),
                child: Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    _isListening ? Icons.mic : Icons.mic_none,
                    color: _isListening ? Colors.red : Colors.white,
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
                    hintText: _isListening ? "Đang nghe..." : "Tìm kiếm",
                    hintStyle: TextStyle(color: _isListening ? Colors.red : Colors.white54),
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
          return Center(child: Text(provider.error!, style: const TextStyle(color: Colors.red)));
        }

        final isSearching = provider.keyword.isNotEmpty;
        final displayList = isSearching ? provider.movies : provider.trendingMovies;

        if (!isTv && !isSearching) {
          return const Center(child: Text("Nhập từ khóa để tìm kiếm", style: TextStyle(color: Colors.white70)));
        }
        if (isSearching && provider.movies.isEmpty) {
          return const Center(child: Text("Không tìm thấy kết quả nào", style: TextStyle(color: Colors.white70)));
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
    return Scaffold(
      backgroundColor: isTv ? const Color(0xFF0F0F0F) : Theme.of(context).scaffoldBackgroundColor,
      appBar: isTv
          ? null
          : AppBar(
              title: TextField(
                controller: _controller,
                autofocus: false,
                style: const TextStyle(color: Colors.white),
                decoration: InputDecoration(
                  hintText: _isListening ? "Đang nghe..." : "Nhập tên phim...",
                  hintStyle: TextStyle(color: _isListening ? Colors.red : Colors.white54),
                  border: InputBorder.none,
                  suffixIcon: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: Icon(
                          _isListening ? Icons.mic : Icons.mic_none,
                          color: _isListening ? Colors.red : Colors.white,
                        ),
                        onPressed: _isListening ? _stopListening : _startListening,
                      ),
                      IconButton(
                        icon: const Icon(Icons.clear, color: Colors.white54),
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
