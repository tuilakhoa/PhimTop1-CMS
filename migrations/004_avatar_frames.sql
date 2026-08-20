CREATE TABLE IF NOT EXISTS avatar_frames (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image_url TEXT NOT NULL,
    price INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_frames (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    frame_id INT NOT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_email, frame_id)
);

ALTER TABLE members ADD COLUMN IF NOT EXISTS active_frame_id INT DEFAULT NULL;

-- Insert some default frames
INSERT INTO avatar_frames (name, image_url, price) VALUES 
('Khung Tân Thủ (Đồng)', 'https://i.imgur.com/k6lP14p.png', 50),
('Khung Thợ Săn (Bạc)', 'https://i.imgur.com/8Qj87yW.png', 200),
('Khung Chiến Binh (Vàng)', 'https://i.imgur.com/tH3Z08X.png', 500),
('Khung Hỏa Long (VIP)', 'https://i.imgur.com/V8k0V2T.png', 1000),
('Khung Neon Cyberpunk', 'https://i.imgur.com/T0b0R4V.png', 1500)
ON DUPLICATE KEY UPDATE name=name;
