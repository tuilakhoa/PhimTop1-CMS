ALTER TABLE members ADD COLUMN google_id VARCHAR(255) DEFAULT NULL;
ALTER TABLE members ADD UNIQUE KEY unique_google_id (google_id);
