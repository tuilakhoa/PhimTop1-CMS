import re
with open('api/v1/movie.php', 'r') as f:
    text = f.read()

pattern = r"\$data = fetchApiMovieDetail\(\$slug\);"
replacement = """$data = fetchApiMovieDetail($slug);
    if ($data && !empty($data['movie'])) {
        $repo->saveMovie($data['movie']);
        $repo->incrementView($slug);
    }"""
text = re.sub(pattern, replacement, text)

with open('api/v1/movie.php', 'w') as f:
    f.write(text)
