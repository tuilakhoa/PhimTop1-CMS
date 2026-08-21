ALTER TABLE movies ADD COLUMN episode_total INT DEFAULT 0;
ALTER TABLE movies ADD COLUMN time VARCHAR(100);
ALTER TABLE movies ADD COLUMN trailer_url TEXT;
ALTER TABLE movies ADD COLUMN actor TEXT;
ALTER TABLE movies ADD COLUMN director TEXT;
ALTER TABLE movies ADD COLUMN is_copyright TINYINT(1) DEFAULT 0;
ALTER TABLE movies ADD COLUMN sub_docquyen TINYINT(1) DEFAULT 0;

CREATE TABLE IF NOT EXISTS episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_slug VARCHAR(255) NOT NULL,
    server_name VARCHAR(100) NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    filename VARCHAR(255),
    embed_url TEXT,
    m3u8_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_movie_slug (movie_slug)
);
