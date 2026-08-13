CREATE TABLE IF NOT EXISTS movies (
    id VARCHAR(100) PRIMARY KEY, name VARCHAR(255) NOT NULL, origin_name VARCHAR(255),
    slug VARCHAR(255) UNIQUE NOT NULL, thumb_url TEXT, poster_url TEXT, year INT, type VARCHAR(100),
    status VARCHAR(100), episode_current VARCHAR(100), quality VARCHAR(100), lang VARCHAR(100),
    chieu_rap TINYINT(1) DEFAULT 0, view INT DEFAULT 0, content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    slug VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_slug VARCHAR(255) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NULL,
    avatar TEXT,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    movie_slug VARCHAR(255) NOT NULL,
    movie_name VARCHAR(255) NOT NULL,
    episode_name VARCHAR(100) NOT NULL,
    thumb_url TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_movie (user_email, movie_slug)
);

CREATE TABLE IF NOT EXISTS user_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    item_slug VARCHAR(255) NOT NULL,
    item_type VARCHAR(50) DEFAULT 'movie',
    item_name VARCHAR(255) NOT NULL,
    thumb_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_item (user_email, item_slug)
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    url VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS seo_metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    item_id VARCHAR(255) NOT NULL,
    custom_slug VARCHAR(255) NULL,
    seo_title VARCHAR(255) NULL,
    seo_desc TEXT NULL,
    seo_keywords TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY type_item (type, item_id),
    UNIQUE KEY type_custom_slug (type, custom_slug)
);

CREATE TABLE IF NOT EXISTS active_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id VARCHAR(100) UNIQUE NOT NULL,
    device_name VARCHAR(255),
    platform VARCHAR(50) DEFAULT 'web',
    movie_slug VARCHAR(255),
    movie_name VARCHAR(255),
    episode_name VARCHAR(100),
    user_name VARCHAR(255),
    is_logged_in TINYINT(1) DEFAULT 0,
    pending_command VARCHAR(50),
    progress INT DEFAULT 0,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS watch_parties (
    room_code VARCHAR(50) PRIMARY KEY,
    movie_slug VARCHAR(255) NOT NULL,
    episode_name VARCHAR(100) NOT NULL,
    creator_name VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    is_public TINYINT(1) DEFAULT 0,
    is_playing TINYINT(1) DEFAULT 0,
    `current_time` INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
