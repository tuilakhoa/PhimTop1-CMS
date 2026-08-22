import os

filepath = "phimtop1_flutter/lib/screens/movie_detail_screen.dart"
with open(filepath, "r") as f:
    lines = f.readlines()

new_lines = []
skip_playlist = False
skip_remote = False

for i, line in enumerate(lines):
    if line.startswith("  void _showPlaylistModal(BuildContext context, String movieSlug, String movieName, String thumbUrl) {"):
        skip_playlist = True
    
    if skip_playlist and line.startswith("  bool _isTvMode(BuildContext context) {"):
        skip_playlist = False
        
    if line.startswith("  void _showRemoteControl(BuildContext context, String title) {"):
        skip_remote = True
        
    if not skip_playlist and not skip_remote:
        # Check for imports block
        if "import '../widgets/error_view.dart';" in line:
            new_lines.append(line)
            new_lines.append("import '../widgets/movie_detail_modals.dart';\n")
        else:
            # We want to replace calls but not function declarations. Since we are skipping the declarations, replacing here is safe.
            line = line.replace("_showPlaylistModal(", "showPlaylistModal(")
            line = line.replace("_showDownloadModal(", "showDownloadModal(")
            line = line.replace("_showRemoteControl(", "showRemoteControlModal(")
            line = line.replace("_showReviewModal(", "showReviewModal(")
            new_lines.append(line)

# Add closing brace since we stripped remote control to the end of file
new_lines.append("}\n")

with open(filepath, "w") as f:
    f.writelines(new_lines)
