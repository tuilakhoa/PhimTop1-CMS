import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:video_player/video_player.dart';
import 'package:chewie/chewie.dart';
import 'package:simple_pip_mode/simple_pip.dart';
import 'package:simple_pip_mode/pip_widget.dart';
import 'package:provider/provider.dart';
import '../providers/explore_provider.dart';
import '../models/models.dart';
import 'dart:async';
import 'package:cached_network_image/cached_network_image.dart';
import '../services/tv_remote_service.dart';
import '../services/watching_session_service.dart';
import '../providers/auth_provider.dart';

class WatchMovieScreen extends StatefulWidget {
  final String m3u8Link;
  final String title;
  final String movieSlug;
  final String episodeName;

  const WatchMovieScreen({
    super.key,
    required this.m3u8Link,
    required this.title,
    this.movieSlug = '',
    this.episodeName = '',
  });

  @override
  State<WatchMovieScreen> createState() => _WatchMovieScreenState();
}

class _WatchMovieScreenState extends State<WatchMovieScreen> {
  VideoPlayerController? _videoController;
  ChewieController? _chewieController;
  bool _showSuggestions = false;
  final FocusNode _suggestionsFocusNode = FocusNode();

  StreamSubscription? _remoteSubscription;
  StreamSubscription? _watchingSessionSubscription;

  @override
  void initState() {
    super.initState();
    // Allow landscape orientation when playing movie
    SystemChrome.setPreferredOrientations([
      DeviceOrientation.portraitUp,
      DeviceOrientation.landscapeLeft,
      DeviceOrientation.landscapeRight,
    ]);

    _initPlayer();

    // Listen for remote control commands
    _remoteSubscription = TvRemoteService().onPlayerAction.listen((data) {
      if (!mounted || _videoController == null) return;
      final command = data['command'];
      if (command == 'play') {
        _videoController!.play();
      } else if (command == 'pause') {
        _videoController!.pause();
      } else if (command == 'seek') {
        final value = data['value'] as int;
        _videoController!.seekTo(Duration(seconds: value));
      } else if (command == 'rewind') {
        final current = _videoController!.value.position;
        _videoController!.seekTo(current - const Duration(seconds: 15));
      } else if (command == 'forward') {
        final current = _videoController!.value.position;
        _videoController!.seekTo(current + const Duration(seconds: 15));
      } else if (command == 'stop') {
        if (Navigator.canPop(context)) {
          Navigator.pop(context);
        }
      }
    });

    // Start watching session heartbeat
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final auth = context.read<AuthProvider>();
      WatchingSessionService().startSession(
        movieSlug: widget.movieSlug,
        movieName: widget.title,
        episodeName: widget.episodeName,
        userName: auth.user?.name ?? 'Guest',
        isLoggedIn: auth.user != null,
        getProgress: () => _videoController?.value.position.inSeconds ?? 0,
      );
    });

    // Listen to admin commands
    _watchingSessionSubscription = WatchingSessionService().onCommand.listen((command) {
      if (!mounted || _videoController == null) return;
      if (command == 'play') {
        _videoController!.play();
      } else if (command == 'pause') {
        _videoController!.pause();
      } else if (command == 'stop') {
        if (Navigator.canPop(context)) {
          Navigator.pop(context);
        }
      }
    });
  }

  void _initPlayer() {
    _videoController = VideoPlayerController.networkUrl(Uri.parse(widget.m3u8Link))
      ..initialize().then((_) {
        setState(() {
          _chewieController = ChewieController(
            videoPlayerController: _videoController!,
            autoPlay: true,
            looping: false,
            aspectRatio: _videoController!.value.aspectRatio,
            errorBuilder: (context, errorMessage) {
              return Center(
                child: Text(
                  errorMessage,
                  style: const TextStyle(color: Colors.white),
                ),
              );
            },
          );
        });
      }).catchError((e) {
        debugPrint("WatchMovieScreen Init Error: $e");
        if (mounted) setState(() {});
      });
  }

  @override
  void dispose() {
    _remoteSubscription?.cancel();
    _watchingSessionSubscription?.cancel();
    WatchingSessionService().stopSession();
    // Revert to portrait only when leaving screen
    SystemChrome.setPreferredOrientations([
      DeviceOrientation.portraitUp,
    ]);
    _videoController?.dispose();
    _chewieController?.dispose();
    _suggestionsFocusNode.dispose();
    super.dispose();
  }

  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800;
  }

  Widget _buildTvSuggestions(BuildContext context) {
    return Consumer<ExploreProvider>(
      builder: (context, provider, child) {
        if (provider.trendingMovies.isEmpty) {
          return const SizedBox.shrink();
        }
        return Container(
          height: 180, // Increased height to accommodate title
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.bottomCenter,
              end: Alignment.topCenter,
              colors: [
                Colors.black.withOpacity(0.9),
                Colors.black.withOpacity(0.6),
                Colors.transparent,
              ],
            ),
          ),
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                child: Text("Gợi ý cho bạn", style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
              ),
              Expanded(
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: provider.trendingMovies.length,
                  itemBuilder: (context, index) {
                    final movie = provider.trendingMovies[index];
                    return _buildSuggestionItem(movie, provider.domain);
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSuggestionItem(MovieItem movie, String domain) {
    return Container(
      width: 120, // Slightly wider for title
      margin: const EdgeInsets.only(left: 16),
      child: Focus(
        child: Builder(
          builder: (context) {
            final hasFocus = Focus.of(context).hasFocus;
            return InkWell(
              onTap: () {
                // Not ideal, but to go to another movie we pop and push replacement or let it go
                Navigator.pop(context);
                Navigator.pushReplacementNamed(context, '/detail', arguments: movie.slug);
              },
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                decoration: BoxDecoration(
                  border: Border.all(color: hasFocus ? Colors.white : Colors.transparent, width: 2),
                  borderRadius: BorderRadius.circular(8),
                ),
                padding: const EdgeInsets.all(2),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(6),
                        child: CachedNetworkImage(
                          imageUrl: (movie.thumbUrl ?? movie.posterUrl ?? '').startsWith('http')
                              ? (movie.thumbUrl ?? movie.posterUrl!)
                              : '$domain/${movie.thumbUrl ?? movie.posterUrl}',
                          fit: BoxFit.cover,
                          width: double.infinity,
                          placeholder: (context, url) => Container(color: Colors.grey[900]),
                          errorWidget: (context, url, error) => const Icon(Icons.error, color: Colors.white),
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      movie.name ?? '',
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            );
          }
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isTv = _isTvMode(context);
    final playerWidget = Center(
      child: _chewieController != null && _chewieController!.videoPlayerController.value.isInitialized
          ? Chewie(controller: _chewieController!)
          : (_videoController != null && _videoController!.value.hasError)
              ? const Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.error_outline, color: Colors.red, size: 64),
                    SizedBox(height: 16),
                    Text("Lỗi tải video. Vui lòng thử lại sau.", style: TextStyle(color: Colors.white)),
                  ],
                )
              : const CircularProgressIndicator(color: Colors.red),
    );

    final tvLayout = Focus(
      autofocus: true,
      onKeyEvent: (node, event) {
        if (event is KeyDownEvent) {
          if (event.logicalKey == LogicalKeyboardKey.arrowDown) {
            if (!_showSuggestions) {
              setState(() {
                _showSuggestions = true;
              });
              Future.delayed(const Duration(milliseconds: 100), () {
                if (mounted) {
                  _suggestionsFocusNode.requestFocus();
                }
              });
              return KeyEventResult.handled;
            }
          }
        }
        return KeyEventResult.ignored;
      },
      child: Stack(
        children: [
          Positioned.fill(
            child: playerWidget,
          ),
          if (_showSuggestions)
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: Focus(
                focusNode: _suggestionsFocusNode,
                onFocusChange: (hasFocus) {
                  if (!hasFocus) {
                    // When suggestions lose focus entirely (e.g. user pressed UP)
                    // We can hide them
                    setState(() {
                      _showSuggestions = false;
                    });
                  }
                },
                child: _buildTvSuggestions(context),
              ),
            ),
        ],
      ),
    );

    return PipWidget(
      pipChild: Scaffold(
        backgroundColor: Colors.black,
        body: playerWidget,
      ),
      child: Scaffold(
        backgroundColor: Colors.black,
        appBar: isTv ? null : AppBar(
          backgroundColor: Colors.transparent,
          elevation: 0,
          title: Text(widget.title, style: const TextStyle(color: Colors.white, fontSize: 16)),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.white),
            onPressed: () => Navigator.pop(context),
          ),
          actions: [
            IconButton(
              icon: const Icon(Icons.picture_in_picture_alt, color: Colors.white),
              onPressed: () {
                SimplePip().enterPipMode();
              },
            ),
          ],
        ),
        body: isTv ? tvLayout : playerWidget,
      ),
    );
  }
}
