-- Phase 2: Multi-profiles & Push Notifications
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    profile_name VARCHAR(255) NOT NULL,
    avatar_url TEXT,
    is_kids_mode TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_fcm_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY email_device (user_email, device_id)
);

-- Phase 3: Reviews and Shorts
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    movie_slug VARCHAR(255) NOT NULL,
    rating_score INT NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS movie_shorts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_slug VARCHAR(255) NOT NULL,
    short_video_url TEXT NOT NULL,
    title VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Phase 1/4: Timestamps for Intro/Outro Skip
CREATE TABLE IF NOT EXISTS movie_timestamps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_slug VARCHAR(255) NOT NULL,
    episode_slug VARCHAR(255) NOT NULL,
    intro_start INT DEFAULT 0,
    intro_end INT DEFAULT 0,
    outro_start INT DEFAULT 0,
    outro_end INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY movie_ep (movie_slug, episode_slug)
);
