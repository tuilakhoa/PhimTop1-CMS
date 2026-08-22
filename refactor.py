import os

filepath = "phimtop1_flutter/lib/screens/movie_detail_screen.dart"
with open(filepath, "r") as f:
    content = f.read()

# Add import
content = content.replace("import '../widgets/error_view.dart';", "import '../widgets/error_view.dart';\nimport '../widgets/movie_detail_modals.dart';")

# Rename calls
content = content.replace("_showPlaylistModal(", "showPlaylistModal(")
content = content.replace("_showDownloadModal(", "showDownloadModal(")
content = content.replace("_showRemoteControl(", "showRemoteControlModal(")
content = content.replace("_showReviewModal(", "showReviewModal(")

# Remove function bodies
# _showPlaylistModal to _isTvMode
import re

content = re.sub(r'  void _showPlaylistModal\(.*?\)\s*{.*?  bool _isTvMode\(BuildContext context\) {', 
                 '  bool _isTvMode(BuildContext context) {', 
                 content, flags=re.DOTALL)

# _showRemoteControl to the end of class
content = re.sub(r'  void _showRemoteControl\(.*?\)\s*{.*}\n}\n$', 
                 '}\n', 
                 content, flags=re.DOTALL)

with open(filepath, "w") as f:
    f.write(content)

