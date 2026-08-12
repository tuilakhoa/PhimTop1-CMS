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
import '../services/watch_party_service.dart';
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

  String? _wpRoomCode;
  bool _wpIsHost = false;
  Timer? _wpSyncTimer;

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
    _wpSyncTimer?.cancel();
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

  void _startWatchPartySync() {
    _wpSyncTimer?.cancel();
    if (_wpRoomCode == null || _videoController == null) return;
    
    _wpSyncTimer = Timer.periodic(const Duration(seconds: 2), (timer) async {
      if (_videoController == null) return;
      
      if (_wpIsHost) {
        await WatchPartyService.syncState(
          _wpRoomCode!, 
          _videoController!.value.isPlaying, 
          _videoController!.value.position.inSeconds
        );
      } else {
        final res = await WatchPartyService.getState(_wpRoomCode!);
        if (res['status'] == 'success') {
          final data = res['data'];
          if (data['status'] != 'active') {
            timer.cancel();
            setState(() { _wpRoomCode = null; _wpIsHost = false; });
            if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Phòng xem chung đã kết thúc.')));
            return;
          }
          
          final targetTime = data['current_time'] as int;
          final isPlaying = data['is_playing'] == 1;
          
          final currentSec = _videoController!.value.position.inSeconds;
          if ((currentSec - targetTime).abs() > 2) {
            _videoController!.seekTo(Duration(seconds: targetTime));
          }
          
          if (isPlaying && !_videoController!.value.isPlaying) {
            _videoController!.play();
          } else if (!isPlaying && _videoController!.value.isPlaying) {
            _videoController!.pause();
          }
        }
      }
    });
  }

  void _showWatchPartyDialog() {
    if (_wpRoomCode != null) {
      // Already in a room, show active state
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          backgroundColor: Colors.grey[900],
          title: const Text('Phòng Xem Chung', style: TextStyle(color: Colors.white)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Mã phòng: $_wpRoomCode', style: const TextStyle(color: Colors.indigoAccent, fontSize: 24, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Text('Vai trò: ${_wpIsHost ? "Chủ phòng" : "Người xem"}', style: const TextStyle(color: Colors.white70)),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                _wpSyncTimer?.cancel();
                setState(() { _wpRoomCode = null; _wpIsHost = false; });
                Navigator.pop(context);
              },
              child: const Text('Rời Phòng', style: TextStyle(color: Colors.red)),
            ),
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Đóng', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      );
      return;
    }

    final codeCtrl = TextEditingController();
    bool isPublic = false;
    List<dynamic> publicRooms = [];
    bool isLoadingRooms = true;

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) {
          if (isLoadingRooms) {
            isLoadingRooms = false;
            WatchPartyService.getPublicParties(widget.movieSlug).then((res) {
              if (res['status'] == 'success') {
                if (mounted) {
                  setDialogState(() {
                    publicRooms = res['data'] ?? [];
                  });
                }
              }
            });
          }

          return AlertDialog(
            backgroundColor: Colors.grey[900],
            title: const Text('Phòng Xem Chung', style: TextStyle(color: Colors.white)),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.indigo, minimumSize: const Size(double.infinity, 45)),
                    onPressed: () async {
                      final auth = context.read<AuthProvider>();
                      final userName = auth.user?.name ?? 'Guest';
                      final res = await WatchPartyService.createParty(widget.movieSlug, widget.episodeName, userName, isPublic: isPublic);
                      if (!mounted) return;
                      Navigator.pop(context);
                      if (res['status'] == 'success') {
                        setState(() {
                          _wpRoomCode = res['room_code'];
                          _wpIsHost = true;
                        });
                        _startWatchPartySync();
                        _showWatchPartyDialog();
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Lỗi: ${res['message']}')));
                      }
                    },
                    child: const Text('Tạo Phòng Mới', style: TextStyle(color: Colors.white)),
                  ),
                  CheckboxListTile(
                    title: const Text('Công khai phòng này', style: TextStyle(color: Colors.white70, fontSize: 13)),
                    value: isPublic,
                    onChanged: (val) {
                      setDialogState(() {
                        isPublic = val ?? false;
                      });
                    },
                    controlAffinity: ListTileControlAffinity.leading,
                    contentPadding: EdgeInsets.zero,
                    activeColor: Colors.indigo,
                  ),
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 8),
                    child: Text('HOẶC NHẬP MÃ', style: TextStyle(color: Colors.grey, fontSize: 12)),
                  ),
                  TextField(
                    controller: codeCtrl,
                    style: const TextStyle(color: Colors.white),
                    decoration: const InputDecoration(
                      hintText: 'Nhập mã phòng',
                      hintStyle: TextStyle(color: Colors.grey),
                      enabledBorder: OutlineInputBorder(borderSide: BorderSide(color: Colors.grey)),
                      focusedBorder: OutlineInputBorder(borderSide: BorderSide(color: Colors.indigo)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.grey[700], minimumSize: const Size(double.infinity, 45)),
                    onPressed: () async {
                      final code = codeCtrl.text.trim().toUpperCase();
                      if (code.isEmpty) return;
                      await _joinWatchParty(code, context);
                    },
                    child: const Text('Vào Phòng', style: TextStyle(color: Colors.white)),
                  ),
                  if (publicRooms.isNotEmpty) ...[
                    const Padding(
                      padding: EdgeInsets.only(top: 16, bottom: 8),
                      child: Text('PHÒNG CÔNG KHAI', style: TextStyle(color: Colors.grey, fontSize: 12)),
                    ),
                    Container(
                      constraints: const BoxConstraints(maxHeight: 150),
                      child: ListView.builder(
                        shrinkWrap: true,
                        itemCount: publicRooms.length,
                        itemBuilder: (ctx, i) {
                          final room = publicRooms[i];
                          return Card(
                            color: Colors.grey[800],
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              title: Text('Mã: ${room['room_code']}', style: const TextStyle(color: Colors.indigoAccent, fontWeight: FontWeight.bold, fontSize: 14)),
                              subtitle: Text('Host: ${room['creator_name']} - Tập ${room['episode_name']}', style: const TextStyle(color: Colors.white70, fontSize: 12)),
                              trailing: ElevatedButton(
                                style: ElevatedButton.styleFrom(backgroundColor: Colors.grey[700]),
                                onPressed: () => _joinWatchParty(room['room_code'], context),
                                child: const Text('Vào', style: TextStyle(color: Colors.white)),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _joinWatchParty(String code, BuildContext dialogContext) async {
    final res = await WatchPartyService.joinParty(code);
    if (!mounted) return;
    
    if (res['status'] == 'success') {
      if (res['data']['movie_slug'] != widget.movieSlug) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Phòng này đang xem phim khác!')));
        return;
      }
      Navigator.pop(dialogContext);
      setState(() {
        _wpRoomCode = code;
        _wpIsHost = false;
      });
      _startWatchPartySync();
      _showWatchPartyDialog();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Lỗi: ${res['message']}')));
    }
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
            if (_wpRoomCode != null)
              Container(
                margin: const EdgeInsets.only(right: 8),
                alignment: Alignment.center,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.green.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.green),
                  ),
                  child: Text(_wpRoomCode!, style: const TextStyle(color: Colors.green, fontSize: 12, fontWeight: FontWeight.bold)),
                ),
              ),
            IconButton(
              icon: Icon(Icons.group, color: _wpRoomCode != null ? Colors.indigoAccent : Colors.white),
              onPressed: _showWatchPartyDialog,
            ),
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
