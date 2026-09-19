const fs = require('fs');
let content = fs.readFileSync('server.js', 'utf8');

content = content.replace(/SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at FROM movies/g, 
    'SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at, tmdb_vote, imdb_vote, countries_json FROM movies');

fs.writeFileSync('server.js', content, 'utf8');
