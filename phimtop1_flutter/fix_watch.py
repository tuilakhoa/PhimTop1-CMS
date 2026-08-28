import re

file_path = 'lib/screens/watch_movie_screen.dart'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Remove the whole _chewieController block
# It starts from `_chewieController = ChewieController(`
# and ends with `);` after `additionalOptions`
content = re.sub(r'_chewieController = ChewieController\([\s\S]*?\);\n', '', content)
content = re.sub(r'setState\(\(\) \{\s*\}\);', '', content)

# Fix line 1147:
# old:
# child: _chewieController != null && _chewieController!.videoPlayerController.value.isInitialized
#   ? Chewie(controller: _chewieController!)
#   : _videoController.player.state.error != null
# new:
# child: _videoController.player.state.error != null
content = re.sub(r'child: _chewieController != null[\s\S]*?\? Chewie\(controller: _chewieController!\)\s*: _videoController\.player\.state\.error != null', r'child: _videoController.player.state.error != null', content)

# Wait, the PIP widget replacement in my previous script was:
#                 AspectRatio(
#                   aspectRatio: 16 / 9,
#                   child: PipWidget(
#                     builder: (context) => Video(
# Wait! Did I even replace the widget? Let's check if there is `Chewie(controller: _chewieController!)` left in the file!

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
