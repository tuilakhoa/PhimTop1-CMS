import re
with open('movie.php', 'r') as f:
    text = f.read()

pattern = r"\$apiResult = fetchApiMovieDetail\(\$originalSlug\);"
replacement = """$apiResult = fetchApiMovieDetail($originalSlug);
    if ($apiResult && $apiResult['movie']) {
        $repo->saveMovie($apiResult['movie']);
        // Optional: $repo->incrementView($originalSlug); // We will increment view only on watching
    }"""
text = re.sub(pattern, replacement, text)

with open('movie.php', 'w') as f:
    f.write(text)
