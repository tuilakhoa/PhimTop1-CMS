import 'package:flutter/material.dart';
import 'dart:ui';
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
import '../widgets/tv_virtual_keyboard.dart';
import '../services/watching_session_service.dart';
import '../services/watch_party_service.dart';
import '../services/mini_player_service.dart';
import '../providers/auth_provider.dart';
import '../api/cms_api.dart';
import '../services/widget_service.dart';

class WatchMovieScreen extends StatefulWidget {
  final String m3u8Link;
  final String title;
  final String movieSlug;
  final String episodeName;
  final String episodeSlug;
  final String thumbUrl;
  final String? autoJoinRoomCode;

  const WatchMovieScreen({
    super.key,
    required this.m3u8Link,
    required this.title,
    this.movieSlug = '',
    this.episodeName = '',
    this.episodeSlug = '',
    this.thumbUrl = '',
    this.autoJoinRoomCode,
  });

  @override
  State<WatchMovieScreen> createState() => _WatchMovieScreenState();
}

class _WatchMovieScreenState extends State<WatchMovieScreen> {
  VideoPlayerController? _videoController;
  ChewieController? _chewieController;
  bool _showSuggestions = false;
  bool _isMinimizing = false;
  final FocusNode _suggestionsFocusNode = FocusNode();

  StreamSubscription? _remoteSubscription;
  StreamSubscription? _watchingSessionSubscription;

  String? _wpRoomCode;
  bool _wpIsHost = false;
  Timer? _wpSyncTimer;
  Timer? _historySyncTimer;

  String? _token;

  @override
  void initState() {
    super.initState();
    _token = context.read<AuthProvider>().token;
    
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
      if (widget.autoJoinRoomCode != null) {
        _autoJoinWatchParty(widget.autoJoinRoomCode!);
      }
      final auth = context.read<AuthProvider>();
      WatchingSessionService().startSession(
        movieSlug: widget.movieSlug,
        movieName: widget.title,
        episodeName: widget.episodeName,
        userName: auth.user?.name ?? 'Guest',
        isLoggedIn: auth.user != null,
        getProgress: () => _videoController?.value.position.inSeconds ?? 0,
      );

      // Setup History Sync
      if (auth.user != null && auth.token != null && widget.movieSlug.isNotEmpty) {
        // initial log
        cmsApi.addHistory(
          auth.token!,
          widget.movieSlug,
          widget.title,
          widget.episodeName,
          episodeSlug: widget.episodeSlug,
          thumbUrl: widget.thumbUrl,
        );

        _historySyncTimer = Timer.periodic(const Duration(seconds: 15), (timer) {
          if (!mounted || _videoController == null || !_videoController!.value.isPlaying) return;
          cmsApi.addHistory(
            auth.token!,
            widget.movieSlug,
            widget.title,
            widget.episodeName,
            episodeSlug: widget.episodeSlug,
            thumbUrl: widget.thumbUrl,
            currentTime: _videoController!.value.position.inSeconds,
            duration: _videoController!.value.duration.inSeconds,
          );
        });
      }
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

  void _initPlayer() async {
    _videoController = VideoPlayerController.networkUrl(Uri.parse(widget.m3u8Link));
    
    try {
      await _videoController!.initialize();
      
      bool autoPlayFired = false;
      _videoController!.addListener(() {
        if (!mounted) return;
        if (_videoController!.value.isInitialized) {
          final pos = _videoController!.value.position.inSeconds;
          final dur = _videoController!.value.duration.inSeconds;
          if (dur > 0 && pos > 0 && (pos / dur >= 0.95)) {
            if (!autoPlayFired) {
               autoPlayFired = true;
               ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Tự động chuyển tập...')));
               Future.delayed(const Duration(seconds: 3), () {
                 if (mounted) Navigator.pop(context, 'next_episode');
               });
            }
          }
        }
      });
      
      // Check history and seek if needed
      Duration? startAt;
      if (_token != null && widget.movieSlug.isNotEmpty) {
        try {
          final res = await cmsApi.getHistory(_token!);
          if (res.data != null) {
            final match = res.data!.firstWhere(
              (item) => item.movieSlug == widget.movieSlug && item.episodeSlug == widget.episodeSlug,
              orElse: () => HistoryItem.fromJson({}), // Dummy empty item
            );
            if (match.id != 0 && match.currentTime > 0) {
              startAt = Duration(seconds: match.currentTime);
              await _videoController!.seekTo(startAt); // fallback explicitly just in case
            }
          }
        } catch (_) {}
      }

      if (mounted) {
        setState(() {
          _chewieController = ChewieController(
            videoPlayerController: _videoController!,
            autoPlay: true,
            looping: false,
            startAt: startAt,
            aspectRatio: _videoController!.value.aspectRatio,
            allowPlaybackSpeedChanging: true,
            playbackSpeeds: const [0.5, 0.75, 1, 1.25, 1.5, 2.0],
            materialProgressColors: ChewieProgressColors(
              playedColor: Theme.of(context).primaryColor,
              handleColor: Colors.white,
              backgroundColor: Colors.white24,
              bufferedColor: Colors.white60,
            ),
            additionalOptions: (optionContext) {
              return [
                OptionItem(
                  onTap: (menuContext) {
                    Navigator.pop(optionContext);
                    SimplePip().enterPipMode();
                  },
                  iconData: Icons.picture_in_picture_alt,
                  title: 'Hình trong hình (PiP)',
                ),
                OptionItem(
                  onTap: (menuContext) {
                    Navigator.pop(optionContext);
                    if (_videoController != null) {
                      setState(() {
                        _isMinimizing = true;
                      });
                      MiniPlayerService().showMiniPlayer(
                        context: context,
                        controller: _videoController!,
                        movieSlug: widget.movieSlug,
                        episodeSlug: widget.episodeSlug,
                        onExpand: () {
                          _videoController?.dispose();
                          Navigator.pushNamed(context, '/detail', arguments: widget.movieSlug);
                        },
                        onClose: () {
                          _videoController?.dispose();
                        },
                      );
                      Navigator.pop(context);
                    }
                  },
                  iconData: Icons.fit_screen_rounded,
                  title: 'Trình phát Mini',
                ),
                OptionItem(
                  onTap: (menuContext) {
                    Navigator.pop(optionContext);
                    _showWatchPartyDialog();
                  },
                  iconData: _wpRoomCode != null ? Icons.group : Icons.group_add,
                  title: _wpRoomCode != null ? 'Quản lý Xem Chung' : 'Phòng Xem Chung',
                ),
              ];
            },
            errorBuilder: (context, errorMessage) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.error_outline_rounded, color: Colors.white54, size: 48),
                    const SizedBox(height: 16),
                    Text(
                      errorMessage,
                      style: const TextStyle(color: Colors.white),
                    ),
                  ],
                ),
              );
            },
          );
        });
      }
    } catch (e) {
      debugPrint("WatchMovieScreen Init Error: $e");
      if (mounted) setState(() {});
    }
  }

  @override
  void dispose() {
    _remoteSubscription?.cancel();
    _watchingSessionSubscription?.cancel();
    _wpSyncTimer?.cancel();
    _historySyncTimer?.cancel();
    WatchingSessionService().stopSession();
    
    // Final save of the exact time before disposing
    if (_token != null && _videoController != null && widget.movieSlug.isNotEmpty) {
      cmsApi.addHistory(
        _token!,
        widget.movieSlug,
        widget.title,
        widget.episodeName,
        episodeSlug: widget.episodeSlug,
        thumbUrl: widget.thumbUrl,
        currentTime: _videoController!.value.position.inSeconds,
        duration: _videoController!.value.duration.inSeconds,
      ).then((_) {
        cmsApi.getHistory(_token!).then((res) {
          if (res.data != null) {
            WidgetService.updateContinueWatchingWidget(res.data!);
          }
        });
      });
    }

    // Revert to portrait only when leaving screen
    SystemChrome.setPreferredOrientations([
      DeviceOrientation.portraitUp,
    ]);
    _wpSyncTimer?.cancel();
    if (!_isMinimizing && _videoController != null) {
      _videoController?.dispose();
      _chewieController?.dispose();
    }
    _suggestionsFocusNode.dispose();
    super.dispose();
  }

  bool _isTvMode(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return MediaQuery.of(context).orientation == Orientation.landscape && size.width > 800 && size.shortestSide >= 500;
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
      _showMobileActiveWatchPartyView();
    } else {
      _showMobileSetupWatchPartyView();
    }
  }

  Future<void> _autoJoinWatchParty(String code) async {
    final res = await WatchPartyService.joinParty(code);
    if (!mounted) return;
    
    if (res['status'] == 'success') {
      if (res['data']['movie_slug'] != widget.movieSlug) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mã phòng này dành cho bộ phim khác.')));
        return;
      }
      setState(() {
        _wpRoomCode = code;
        _wpIsHost = false;
      });
      _startWatchPartySync();
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Đã tham gia phòng: $code')));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Lỗi vào phòng: ${res['message']}')));
    }
  }

  void _showMobileActiveWatchPartyView() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white12))),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text("Đang trong Phòng Xem", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                    IconButton(icon: const Icon(Icons.close, color: Colors.grey), onPressed: () => Navigator.pop(context)),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  children: [
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.05),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        children: [
                          const Text('MÃ PHÒNG', style: TextStyle(color: Colors.grey, fontSize: 12, fontWeight: FontWeight.bold, letterSpacing: 2)),
                          const SizedBox(height: 8),
                          Text('$_wpRoomCode', style: TextStyle(color: Theme.of(context).primaryColor, fontSize: 32, fontWeight: FontWeight.w900, letterSpacing: 6)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.05),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(_wpIsHost ? Icons.workspace_premium : Icons.person, color: _wpIsHost ? Colors.amber : Colors.white70, size: 20),
                          const SizedBox(width: 8),
                          Text('Vai trò: ${_wpIsHost ? "Chủ phòng" : "Người xem"}', style: TextStyle(color: _wpIsHost ? Colors.amber : Colors.white70, fontSize: 15, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 32),
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.redAccent.withOpacity(0.2),
                          foregroundColor: Colors.redAccent,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () {
                          _wpSyncTimer?.cancel();
                          setState(() { _wpRoomCode = null; _wpIsHost = false; });
                          Navigator.pop(context);
                        },
                        icon: const Icon(Icons.exit_to_app),
                        label: const Text('Rời Phòng', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showTvActiveWatchPartyView() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.grey[900],
        title: const Row(
          children: [
            Icon(Icons.tv, color: Colors.indigoAccent, size: 32),
            SizedBox(width: 12),
            Text('Phòng Xem Chung (TV)', style: TextStyle(color: Colors.white, fontSize: 24)),
          ],
        ),
        content: Padding(
          padding: const EdgeInsets.symmetric(vertical: 24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Mã phòng: $_wpRoomCode', style: const TextStyle(color: Colors.indigoAccent, fontSize: 48, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              Text('Vai trò: ${_wpIsHost ? "Chủ phòng" : "Người xem"}', style: const TextStyle(color: Colors.white70, fontSize: 20)),
            ],
          ),
        ),
        actions: [
          Focus(
            autofocus: true,
            child: Builder(builder: (context) {
              return ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Focus.of(context).hasFocus ? Theme.of(context).primaryColor : Theme.of(context).primaryColor.withOpacity(0.8),
                  padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
                ),
                onPressed: () {
                  _wpSyncTimer?.cancel();
                  setState(() { _wpRoomCode = null; _wpIsHost = false; });
                  Navigator.pop(context);
                },
                child: const Text('Rời Phòng', style: TextStyle(color: Colors.white, fontSize: 18)),
              );
            }),
          ),
          const SizedBox(width: 16),
          Focus(
            child: Builder(builder: (context) {
              return ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Focus.of(context).hasFocus ? Colors.grey[600] : Colors.grey[800],
                  padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
                ),
                onPressed: () => Navigator.pop(context),
                child: const Text('Đóng', style: TextStyle(color: Colors.white, fontSize: 18)),
              );
            }),
          ),
        ],
      ),
    );
  }

  void _showMobileSetupWatchPartyView() {
    final codeCtrl = TextEditingController();
    bool isPublic = false;
    List<dynamic> publicRooms = [];
    bool isLoadingRooms = true;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
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

          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
            child: SizedBox(
              height: MediaQuery.of(context).size.height * 0.7,
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white12))),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text("Phòng Xem Chung", style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                        IconButton(icon: const Icon(Icons.close, color: Colors.grey), onPressed: () => Navigator.pop(context)),
                      ],
                    ),
                  ),
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Create Room Section
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.05),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Column(
                              children: [
                                SizedBox(
                                  width: double.infinity,
                                  height: 48,
                                  child: ElevatedButton.icon(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Theme.of(context).primaryColor,
                                      foregroundColor: Colors.white,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                    ),
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
                                    icon: const Icon(Icons.add_rounded),
                                    label: const Text('Tạo Phòng Mới', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                                  ),
                                ),
                                const SizedBox(height: 12),
                                Theme(
                                  data: ThemeData(unselectedWidgetColor: Colors.grey),
                                  child: CheckboxListTile(
                                    title: const Text('Công khai phòng này', style: TextStyle(color: Colors.white70, fontSize: 14)),
                                    value: isPublic,
                                    onChanged: (val) {
                                      setDialogState(() {
                                        isPublic = val ?? false;
                                      });
                                    },
                                    controlAffinity: ListTileControlAffinity.leading,
                                    contentPadding: EdgeInsets.zero,
                                    activeColor: Theme.of(context).primaryColor,
                                    checkColor: Colors.white,
                                    dense: true,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: 24),
                            child: Row(
                              children: [
                                Expanded(child: Divider(color: Colors.white.withOpacity(0.1))),
                                Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16),
                                  child: Text('HOẶC', style: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12, fontWeight: FontWeight.w600)),
                                ),
                                Expanded(child: Divider(color: Colors.white.withOpacity(0.1))),
                              ],
                            ),
                          ),
                          
                          // Join Room Section
                          Container(
                            decoration: BoxDecoration(
                              color: Colors.black.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: Colors.white.withOpacity(0.1)),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  child: TextField(
                                    controller: codeCtrl,
                                    style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 2),
                                    textAlign: TextAlign.center,
                                    textCapitalization: TextCapitalization.characters,
                                    decoration: InputDecoration(
                                      hintText: 'NHẬP MÃ PHÒNG',
                                      hintStyle: TextStyle(color: Colors.white.withOpacity(0.2), letterSpacing: 2, fontWeight: FontWeight.bold, fontSize: 14),
                                      border: InputBorder.none,
                                      contentPadding: const EdgeInsets.symmetric(vertical: 16),
                                    ),
                                  ),
                                ),
                                Container(
                                  margin: const EdgeInsets.only(right: 8),
                                  decoration: BoxDecoration(
                                    color: Theme.of(context).primaryColor.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: IconButton(
                                    icon: Icon(Icons.arrow_forward_rounded, color: Theme.of(context).primaryColor),
                                    onPressed: () async {
                                      final code = codeCtrl.text.trim().toUpperCase();
                                      if (code.isEmpty) return;
                                      await _joinWatchParty(code, context);
                                    },
                                  ),
                                ),
                              ],
                            ),
                          ),
                          
                          // Public Rooms
                          if (publicRooms.isNotEmpty) ...[
                            const SizedBox(height: 32),
                            const Text('PHÒNG ĐANG MỞ', style: TextStyle(color: Colors.grey, fontSize: 12, fontWeight: FontWeight.bold, letterSpacing: 1)),
                            const SizedBox(height: 12),
                            ListView.builder(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              itemCount: publicRooms.length,
                              itemBuilder: (ctx, i) {
                                final room = publicRooms[i];
                                return Container(
                                  margin: const EdgeInsets.only(bottom: 12),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withOpacity(0.02),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: Colors.white.withOpacity(0.05)),
                                  ),
                                  child: ListTile(
                                    title: Text('${room['room_code']}', style: TextStyle(color: Theme.of(context).primaryColor, fontWeight: FontWeight.bold, fontSize: 16, letterSpacing: 1)),
                                    subtitle: Padding(
                                      padding: const EdgeInsets.only(top: 4),
                                      child: Text('${room['creator_name']} • Tập ${room['episode_name']}', style: const TextStyle(color: Colors.grey, fontSize: 13)),
                                    ),
                                    trailing: ElevatedButton(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Theme.of(context).primaryColor,
                                        foregroundColor: Colors.white,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                      ),
                                      onPressed: () => _joinWatchParty(room['room_code'], context),
                                      child: const Text('Tham gia'),
                                    ),
                                  ),
                                );
                              },
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _createPartyTv(bool isPublic, BuildContext dialogContext) async {
    final auth = context.read<AuthProvider>();
    final userName = auth.user?.name ?? 'Guest';
    final res = await WatchPartyService.createParty(widget.movieSlug, widget.episodeName, userName, isPublic: isPublic);
    if (!mounted) return;
    Navigator.pop(dialogContext);
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
  }

  void _showTvSetupWatchPartyView() {
    String enteredCode = "";
    List<dynamic> publicRooms = [];
    bool isLoadingRooms = true;
    bool isTypingCode = false;

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

          Widget leftColumn;
          if (isTypingCode) {
            leftColumn = Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Mã: $enteredCode', style: const TextStyle(color: Colors.indigoAccent, fontSize: 28, fontWeight: FontWeight.bold, letterSpacing: 4)),
                const SizedBox(height: 16),
                Expanded(
                  child: TvVirtualKeyboard(
                    text: enteredCode,
                    onTextChanged: (v) {
                      setDialogState(() { enteredCode = v.toUpperCase(); });
                    },
                    onSearch: () {},
                  ),
                ),
                Row(
                  children: [
                    _tvButton(
                      title: 'Vào Phòng',
                      icon: Icons.login,
                      color: Colors.green[700]!,
                      onPressed: () => _joinWatchParty(enteredCode, context),
                    ),
                    const SizedBox(width: 16),
                    _tvButton(
                      title: 'Quay Lại',
                      icon: Icons.arrow_back,
                      color: Colors.grey[700]!,
                      onPressed: () { setDialogState(() { isTypingCode = false; enteredCode = ""; }); },
                    ),
                  ],
                )
              ],
            );
          } else {
            leftColumn = Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _tvButton(
                  title: 'Tạo Phòng CÔNG KHAI',
                  icon: Icons.public,
                  color: Colors.indigo,
                  onPressed: () => _createPartyTv(true, context),
                  autofocus: true,
                ),
                const SizedBox(height: 24),
                _tvButton(
                  title: 'Tạo Phòng RIÊNG TƯ',
                  icon: Icons.lock,
                  color: Colors.grey[800]!,
                  onPressed: () => _createPartyTv(false, context),
                ),
                const SizedBox(height: 32),
                const Text('HOẶC', style: TextStyle(color: Colors.grey, fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(height: 32),
                _tvButton(
                  title: 'Nhập Mã Phòng Để Vào',
                  icon: Icons.keyboard,
                  color: Colors.orange[800]!,
                  onPressed: () {
                    setDialogState(() {
                      isTypingCode = true;
                    });
                  },
                ),
              ],
            );
          }

          return Dialog(
            backgroundColor: Colors.grey[900],
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: Container(
              width: MediaQuery.of(context).size.width * 0.9,
              height: MediaQuery.of(context).size.height * 0.9,
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.tv, color: Colors.indigoAccent, size: 36),
                          SizedBox(width: 16),
                          Text('Phòng Xem Chung', style: TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold)),
                        ],
                      ),
                      Focus(
                        child: Builder(builder: (context) {
                          return IconButton(
                            icon: const Icon(Icons.close, color: Colors.white, size: 32),
                            onPressed: () => Navigator.pop(context),
                            color: Focus.of(context).hasFocus ? Theme.of(context).primaryColor : Colors.white,
                          );
                        }),
                      ),
                    ],
                  ),
                  const Divider(color: Colors.white24, height: 32),
                  Expanded(
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Left Column: Actions / Keyboard
                        Expanded(
                          flex: 1,
                          child: leftColumn,
                        ),
                        const VerticalDivider(color: Colors.white24, width: 32),
                        // Right Column: Public Rooms
                        Expanded(
                          flex: 1,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('CÁC PHÒNG CÔNG KHAI', style: TextStyle(color: Colors.grey, fontSize: 16, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 16),
                              if (publicRooms.isEmpty)
                                const Text('Không có phòng công khai nào.', style: TextStyle(color: Colors.white70, fontSize: 16))
                              else
                                Expanded(
                                  child: ListView.builder(
                                    itemCount: publicRooms.length,
                                    itemBuilder: (ctx, i) {
                                      final room = publicRooms[i];
                                      return Focus(
                                        child: Builder(builder: (context) {
                                          final hasFocus = Focus.of(context).hasFocus;
                                          return Card(
                                            color: hasFocus ? Colors.indigoAccent : Colors.grey[800],
                                            elevation: hasFocus ? 8 : 2,
                                            margin: const EdgeInsets.only(bottom: 12),
                                            child: InkWell(
                                              onTap: () => _joinWatchParty(room['room_code'], context),
                                              child: Padding(
                                                padding: const EdgeInsets.all(16.0),
                                                child: Row(
                                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                                  children: [
                                                    Column(
                                                      crossAxisAlignment: CrossAxisAlignment.start,
                                                      children: [
                                                        Text('Mã: ${room['room_code']}', style: TextStyle(color: hasFocus ? Colors.white : Colors.indigoAccent, fontWeight: FontWeight.bold, fontSize: 20)),
                                                        const SizedBox(height: 4),
                                                        Text('Host: ${room['creator_name']} - Tập ${room['episode_name']}', style: TextStyle(color: hasFocus ? Colors.white : Colors.white70, fontSize: 14)),
                                                      ],
                                                    ),
                                                    Icon(Icons.login, color: hasFocus ? Colors.white : Colors.grey),
                                                  ],
                                                ),
                                              ),
                                            ),
                                          );
                                        }),
                                      );
                                    },
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
            ),
          );
        },
      ),
    );
  }

  Widget _tvButton({required String title, required IconData icon, required Color color, required VoidCallback onPressed, bool autofocus = false}) {
    return Focus(
      autofocus: autofocus,
      child: Builder(builder: (context) {
        final hasFocus = Focus.of(context).hasFocus;
        return InkWell(
          onTap: onPressed,
          borderRadius: BorderRadius.circular(12),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            decoration: BoxDecoration(
              color: hasFocus ? Colors.white : color,
              borderRadius: BorderRadius.circular(12),
              boxShadow: hasFocus ? [BoxShadow(color: color.withOpacity(0.6), blurRadius: 12, spreadRadius: 2)] : [],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(icon, color: hasFocus ? Colors.black : Colors.white, size: 28),
                const SizedBox(width: 16),
                Text(title, style: TextStyle(color: hasFocus ? Colors.black : Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
              ],
            ),
          ),
        );
      }),
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

  // Menu items moved to AppBar actions directly to avoid duplicate 3-dot menus

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
              : CircularProgressIndicator(color: Theme.of(context).primaryColor),
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
                margin: const EdgeInsets.only(right: 16),
                alignment: Alignment.center,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.green.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.green),
                  ),
                  child: Text(_wpRoomCode!, style: const TextStyle(color: Colors.green, fontSize: 13, fontWeight: FontWeight.bold)),
                ),
              ),
          ],
        ),
        body: isTv ? tvLayout : playerWidget,
      ),
    );
  }
}
