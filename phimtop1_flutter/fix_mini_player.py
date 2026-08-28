import re

def fix_mini_player(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    content = content.replace("import 'package:video_player/video_player.dart';", "import 'package:media_kit/media_kit.dart';\nimport 'package:media_kit_video/media_kit_video.dart';")
    content = content.replace("VideoPlayerController", "VideoController")
    content = content.replace("VideoPlayer(widget.controller)", "Video(controller: widget.controller, controls: NoVideoControls)")
    content = content.replace("widget.controller.value.isPlaying", "widget.controller.player.state.playing")
    content = content.replace("widget.controller.pause()", "widget.controller.player.pause()")
    content = content.replace("widget.controller.play()", "widget.controller.player.play()")

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

fix_mini_player('lib/services/mini_player_service.dart')
