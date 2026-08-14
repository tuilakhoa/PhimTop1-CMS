ALTER TABLE watch_history ADD COLUMN episode_slug VARCHAR(255) DEFAULT '';
ALTER TABLE watch_history ADD COLUMN current_time INT DEFAULT 0;
ALTER TABLE watch_history ADD COLUMN duration INT DEFAULT 0;
