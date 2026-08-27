import re
with open('watch.php', 'r') as f:
    text = f.read()

# I already added incrementView inside watch.php, let me replace that.
pattern = r"\$apiResult = fetchApiMovieDetail\(\$originalSlug\);\s*\$repo->incrementView\(\$originalSlug\);"
replacement = """$apiResult = fetchApiMovieDetail($originalSlug);
    if ($apiResult && $apiResult['movie']) {
        $repo->saveMovie($apiResult['movie']);
        $repo->incrementView($originalSlug);
    }"""
text = re.sub(pattern, replacement, text)

with open('watch.php', 'w') as f:
    f.write(text)
