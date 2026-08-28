import re

def refactor_media_kit(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Imports
    content = content.replace("import 'package:video_player/video_player.dart';", "import 'package:media_kit/media_kit.dart';\nimport 'package:media_kit_video/media_kit_video.dart';")
    content = content.replace("import 'package:chewie/chewie.dart';", "")

    # 2. State vars
    content = content.replace("VideoPlayerController? _videoController;", "final Player _player = Player();\n  late final VideoController _videoController = VideoController(_player);")
    content = content.replace("ChewieController? _chewieController;", "")

    # 3. Replace _videoController methods
    # Wait, it's safer to use regex or string replace.
    # _videoController!.play() -> _player.play()
    content = content.replace("_videoController!.play()", "_player.play()")
    content = content.replace("_videoController!.pause()", "_player.pause()")
    content = content.replace("_videoController!.seekTo(", "_player.seek(")
    content = content.replace("_videoController!.value.position", "_player.state.position")
    content = content.replace("_videoController!.value.duration", "_player.state.duration")
    content = content.replace("_videoController!.value.isPlaying", "_player.state.playing")
    content = content.replace("_videoController?.value.position", "_player.state.position")
    content = content.replace("_videoController?.value.duration", "_player.state.duration")
    
    # Check nulls for _videoController (since _player is not null now)
    content = content.replace("if (!mounted || _videoController == null) return;", "if (!mounted) return;")
    content = content.replace("if (!mounted || _videoController == null || !_videoController!.value.isPlaying) return;", "if (!mounted || !_player.state.playing) return;")
    content = content.replace("if (_videoController == null) return;", "")
    content = content.replace("(_videoController != null && _videoController!.value.hasError)", "_videoController.player.state.error != null")
    
    # 4. _initPlayer()
    old_init = """  void _initPlayer() async {
    _videoController = VideoPlayerController.networkUrl(Uri.parse(widget.m3u8Link));
    
    try {
      await _videoController!.initialize();
      
      // Lắng nghe tiến độ để tự động next/chuyển tập hoặc lưu lịch sử
      _videoController!.addListener(() {
        if (!mounted) return;
        if (_videoController!.value.isInitialized) {
          final pos = _videoController!.value.position.inSeconds;
          final dur = _videoController!.value.duration.inSeconds;
          
          if (dur > 0 && pos > 0) {
            _handleSyncAndNext(pos, dur);
          }
        }
      });"""
    
    new_init = """  void _initPlayer() async {
    try {
      await _player.open(Media(widget.m3u8Link));
      
      // Lắng nghe tiến độ để tự động next/chuyển tập hoặc lưu lịch sử
      _player.stream.position.listen((position) {
        if (!mounted) return;
        final pos = position.inSeconds;
        final dur = _player.state.duration.inSeconds;
        
        if (dur > 0 && pos > 0) {
          _handleSyncAndNext(pos, dur);
        }
      });"""
    content = content.replace(old_init, new_init)

    # _chewieController initialization
    old_chewie = """      // Cấu hình Chewie
      _chewieController = ChewieController(
        videoPlayerController: _videoController!,
        autoPlay: true,
        looping: false,
        allowFullScreen: true,
        aspectRatio: _videoController!.value.aspectRatio,
        errorBuilder: (context, errorMessage) {
          return Center(
            child: Text(
              errorMessage,
              style: const TextStyle(color: Colors.white),
            ),
          );
        },
      );"""
    
    # Just remove it, but keep _player.play() if we need autoPlay
    new_chewie = """      _player.play();"""
    content = content.replace(old_chewie, new_chewie)

    # Remove dispose checks
    content = content.replace("_videoController?.dispose();", "")
    content = content.replace("_chewieController?.dispose();", "")
    
    # Replace dispose
    old_dispose = """    if (!_isMinimizing && _videoController != null) {
      _videoController?.dispose();
      _chewieController?.dispose();
    }"""
    new_dispose = """    if (!_isMinimizing) {
      _player.dispose();
    }"""
    content = content.replace(old_dispose, new_dispose)
    
    # Replace the UI Widget
    old_widget = """                if (_chewieController != null &&
                    _chewieController!.videoPlayerController.value.isInitialized)
                  AspectRatio(
                    aspectRatio: _chewieController!.aspectRatio ?? 16 / 9,
                    child: PipWidget(
                      builder: (context) => Chewie(
                        controller: _chewieController!,
                      ),
                      pipBuilder: (context) {
                        return Chewie(
                          controller: _chewieController!,
                        );
                      },
                      onPipEntered: () {
                        setState(() => _isPip = true);
                      },
                      onPipExited: () {
                        setState(() => _isPip = false);
                        // Cleanup on exit PIP if needed, handled by activity lifecycle though.
                      },
                    ),
                  )
                else
                  const AspectRatio(
                    aspectRatio: 16 / 9,
                    child: Center(
                      child: CircularProgressIndicator(color: Colors.yellow),
                    ),
                  ),"""

    new_widget = """                AspectRatio(
                  aspectRatio: 16 / 9,
                  child: PipWidget(
                    builder: (context) => Video(
                      controller: _videoController,
                      controls: MaterialVideoControls,
                    ),
                    pipBuilder: (context) {
                      return Video(
                        controller: _videoController,
                        controls: NoVideoControls,
                      );
                    },
                    onPipEntered: () {
                      setState(() => _isPip = true);
                    },
                    onPipExited: () {
                      setState(() => _isPip = false);
                    },
                  ),
                ),"""
    content = content.replace(old_widget, new_widget)
    
    # Some extra cleanup
    content = content.replace("if (_token != null && _videoController != null && widget.movieSlug.isNotEmpty)", "if (_token != null && widget.movieSlug.isNotEmpty)")
    content = content.replace("if (_wpRoomCode == null || _videoController == null) return;", "if (_wpRoomCode == null) return;")

    # Resume from specific time
    content = content.replace("await _videoController!.seekTo(startAt);", "await _player.seek(startAt);")

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

refactor_media_kit('lib/screens/watch_movie_screen.dart')
