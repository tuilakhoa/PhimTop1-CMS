import re

file_path = 'watch.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_logic = """$themeFile = __DIR__ . "/themes/{$theme}/movie.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    require __DIR__ . "/themes/dark/movie.php";
}"""

new_logic = """$themeFile = __DIR__ . "/themes/{$theme}/watch.php";
if (file_exists($themeFile)) {
    require $themeFile;
} else {
    $themeMovieFile = __DIR__ . "/themes/{$theme}/movie.php";
    if (file_exists($themeMovieFile)) {
        require $themeMovieFile;
    } else {
        require __DIR__ . "/themes/dark/movie.php";
    }
}"""

content = content.replace(old_logic, new_logic)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Root watch.php updated")
