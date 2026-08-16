import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';
import 'package:provider/provider.dart';
import '../core/config.dart';
import '../providers/auth_provider.dart';
import 'dart:convert';

class ShortItem {
  final int id;
  final String movieSlug;
  final String shortVideoUrl;
  final String title;
  final String movieName;
  final String movieThumb;

  ShortItem({
    required this.id,
    required this.movieSlug,
    required this.shortVideoUrl,
    required this.title,
    required this.movieName,
    required this.movieThumb,
  });

  factory ShortItem.fromJson(Map<String, dynamic> json) {
    return ShortItem(
      id: json['id'] ?? 0,
      movieSlug: json['movie_slug'] ?? '',
      shortVideoUrl: json['short_video_url'] ?? '',
      title: json['title'] ?? '',
      movieName: json['movie_name'] ?? '',
      movieThumb: json['movie_thumb'] ?? '',
    );
  }
}

class ShortsScreen extends StatefulWidget {
  const ShortsScreen({super.key});

  @override
  State<ShortsScreen> createState() => _ShortsScreenState();
}

class _ShortsScreenState extends State<ShortsScreen> {
  final PageController _pageController = PageController();
  List<ShortItem> _shorts = [];
  bool _isLoading = true;
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _fetchShorts();
  }

  Future<void> _fetchShorts() async {
    try {
      final dio = Dio();
      final response = await dio.get(
        '${AppConfig.baseUrl}api/v1/shorts.php?action=list',
        options: Options(headers: {
          'X-App-API-Key': AppConfig.apiKey,
        }),
      );
      
      if (response.data != null && response.data['status'] == 'success') {
        final List<dynamic> data = response.data['data'];
        setState(() {
          _shorts = data.map((e) => ShortItem.fromJson(e)).toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint("Error fetching shorts: $e");
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: Colors.black,
        body: Center(child: CircularProgressIndicator(color: Colors.redAccent)),
      );
    }

    if (_shorts.isEmpty) {
      return const Scaffold(
        backgroundColor: Colors.black,
        body: Center(
          child: Text("Không có video ngắn nào", style: TextStyle(color: Colors.white70, fontSize: 16)),
        ),
      );
    }

    return Scaffold(
      backgroundColor: Colors.black,
      body: PageView.builder(
        controller: _pageController,
        scrollDirection: Axis.vertical,
        itemCount: _shorts.length,
        onPageChanged: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        itemBuilder: (context, index) {
          return ShortVideoPlayer(
            short: _shorts[index],
            isActive: _currentIndex == index,
          );
        },
      ),
    );
  }
}

class ShortVideoPlayer extends StatefulWidget {
  final ShortItem short;
  final bool isActive;

  const ShortVideoPlayer({super.key, required this.short, required this.isActive});

  @override
  State<ShortVideoPlayer> createState() => _ShortVideoPlayerState();
}

class _ShortVideoPlayerState extends State<ShortVideoPlayer> {
  VideoPlayerController? _controller;
  bool _initialized = false;
  bool _isPlaying = true;

  @override
  void initState() {
    super.initState();
    _initVideo();
  }

  Future<void> _initVideo() async {
    _controller = VideoPlayerController.networkUrl(Uri.parse(widget.short.shortVideoUrl));
    try {
      await _controller!.initialize();
      _controller!.setLooping(true);
      if (widget.isActive) {
        _controller!.play();
      }
      if (mounted) {
        setState(() {
          _initialized = true;
        });
      }
    } catch (e) {
      debugPrint("Error initializing short video: $e");
    }
  }

  @override
  void didUpdateWidget(covariant ShortVideoPlayer oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.isActive && !oldWidget.isActive) {
      _controller?.play();
      _isPlaying = true;
    } else if (!widget.isActive && oldWidget.isActive) {
      _controller?.pause();
      _isPlaying = false;
    }
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  void _togglePlay() {
    if (_controller == null || !_initialized) return;
    setState(() {
      if (_controller!.value.isPlaying) {
        _controller!.pause();
        _isPlaying = false;
      } else {
        _controller!.play();
        _isPlaying = true;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: _togglePlay,
      child: Stack(
        fit: StackFit.expand,
        children: [
          // Video Player
          if (_initialized && _controller != null)
            FittedBox(
              fit: BoxFit.cover,
              child: SizedBox(
                width: _controller!.value.size.width,
                height: _controller!.value.size.height,
                child: VideoPlayer(_controller!),
              ),
            )
          else
            const Center(child: CircularProgressIndicator(color: Colors.white54)),

          // Play Icon overlay when paused
          if (!_isPlaying)
            const Center(
              child: Icon(Icons.play_arrow_rounded, color: Colors.white70, size: 80),
            ),

          // Gradient overlay for text visibility
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              height: 250,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.bottomCenter,
                  end: Alignment.topCenter,
                  colors: [Colors.black.withOpacity(0.8), Colors.transparent],
                ),
              ),
            ),
          ),

          // UI Overlay (Right side buttons)
          Positioned(
            right: 16,
            bottom: 80,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                _buildSidebarButton(Icons.favorite_border, "Thích"),
                const SizedBox(height: 20),
                _buildSidebarButton(Icons.comment_rounded, "Bình luận"),
                const SizedBox(height: 20),
                _buildSidebarButton(Icons.share_rounded, "Chia sẻ"),
                const SizedBox(height: 20),
                GestureDetector(
                  onTap: () {
                     Navigator.pushNamed(context, '/detail', arguments: widget.short.movieSlug);
                  },
                  child: Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                      image: DecorationImage(
                        image: CachedNetworkImageProvider(
                          widget.short.movieThumb.startsWith('http') 
                              ? widget.short.movieThumb 
                              : 'https://phimimg.com/${widget.short.movieThumb}'
                        ),
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Details (Bottom Left)
          Positioned(
            left: 16,
            bottom: 20,
            right: 80,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.short.movieName,
                  style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Text(
                  widget.short.title,
                  style: const TextStyle(color: Colors.white70, fontSize: 14),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.redAccent,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  ),
                  onPressed: () {
                     Navigator.pushNamed(context, '/detail', arguments: widget.short.movieSlug);
                  },
                  icon: const Icon(Icons.play_circle_fill_rounded, size: 20),
                  label: const Text("Xem Phim"),
                )
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSidebarButton(IconData icon, String label) {
    return Column(
      children: [
        Icon(icon, color: Colors.white, size: 32),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(color: Colors.white, fontSize: 12)),
      ],
    );
  }
}
