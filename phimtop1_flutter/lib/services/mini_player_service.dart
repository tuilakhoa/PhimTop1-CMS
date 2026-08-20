import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';

class MiniPlayerService {
  static final MiniPlayerService _instance = MiniPlayerService._internal();
  factory MiniPlayerService() => _instance;
  MiniPlayerService._internal();

  OverlayEntry? _overlayEntry;
  VideoPlayerController? activeController;
  String? currentMovieSlug;
  String? currentEpisodeSlug;
  String? currentThumbUrl;

  void showMiniPlayer({
    required BuildContext context,
    required VideoPlayerController controller,
    required String movieSlug,
    required String episodeSlug,
    String? thumbUrl,
    required VoidCallback onExpand,
    required VoidCallback onClose,
  }) {
    if (_overlayEntry != null) {
      hideMiniPlayer();
    }

    activeController = controller;
    currentMovieSlug = movieSlug;
    currentEpisodeSlug = episodeSlug;
    currentThumbUrl = thumbUrl;

    _overlayEntry = OverlayEntry(
      builder: (context) => _MiniPlayerWidget(
        controller: controller,
        onExpand: () {
          hideMiniPlayer();
          onExpand();
        },
        onClose: () {
          hideMiniPlayer();
          onClose();
        },
      ),
    );

    Overlay.of(context).insert(_overlayEntry!);
  }

  void hideMiniPlayer() {
    _overlayEntry?.remove();
    _overlayEntry = null;
    activeController = null;
    currentMovieSlug = null;
    currentEpisodeSlug = null;
  }
}

class _MiniPlayerWidget extends StatefulWidget {
  final VideoPlayerController controller;
  final VoidCallback onExpand;
  final VoidCallback onClose;

  const _MiniPlayerWidget({
    Key? key,
    required this.controller,
    required this.onExpand,
    required this.onClose,
  }) : super(key: key);

  @override
  State<_MiniPlayerWidget> createState() => _MiniPlayerWidgetState();
}

class _MiniPlayerWidgetState extends State<_MiniPlayerWidget> {
  double _x = 20;
  double _y = 100; // Will be set to bottom right in init

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final size = MediaQuery.of(context).size;
    _x = size.width - 180;
    _y = size.height - 150;
  }

  @override
  Widget build(BuildContext context) {
    return Positioned(
      left: _x,
      top: _y,
      child: GestureDetector(
        onPanUpdate: (details) {
          setState(() {
            _x += details.delta.dx;
            _y += details.delta.dy;
          });
        },
        onDoubleTap: widget.onExpand,
        onTap: () {
          setState(() {
            if (widget.controller.value.isPlaying) {
              widget.controller.pause();
            } else {
              widget.controller.play();
            }
          });
        },
        child: Material(
          color: Colors.transparent,
          child: Container(
            width: 160,
            height: 90,
            decoration: BoxDecoration(
              color: Colors.black,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.5),
                  blurRadius: 10,
                  offset: const Offset(0, 5),
                ),
              ],
            ),
            child: Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: VideoPlayer(widget.controller),
                ),
                Positioned(
                  top: 0,
                  right: 0,
                  child: IconButton(
                    icon: const Icon(Icons.close, color: Colors.white, size: 20),
                    onPressed: widget.onClose,
                  ),
                ),
                Positioned(
                  top: 0,
                  left: 0,
                  child: IconButton(
                    icon: const Icon(Icons.fullscreen, color: Colors.white, size: 20),
                    onPressed: widget.onExpand,
                  ),
                ),
                Positioned(
                  bottom: 5,
                  left: 5,
                  child: Icon(
                    widget.controller.value.isPlaying ? Icons.play_arrow : Icons.pause,
                    color: Colors.white70,
                    size: 20,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
