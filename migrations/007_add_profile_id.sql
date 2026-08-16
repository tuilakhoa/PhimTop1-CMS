ALTER TABLE watch_history ADD COLUMN profile_id INT DEFAULT 0;
ALTER TABLE user_follows ADD COLUMN profile_id INT DEFAULT 0;
