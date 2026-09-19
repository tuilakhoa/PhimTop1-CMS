const mysql = require('mysql2/promise');
require('dotenv').config();

async function run() {
    const pool = mysql.createPool({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'phimtop1_cms',
        waitForConnections: true,
    });

    try {
        await pool.query(`
            CREATE TABLE IF NOT EXISTS themes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                image_url VARCHAR(255),
                download_url VARCHAR(255),
                type VARCHAR(50) DEFAULT 'free',
                rating DECIMAL(3,1) DEFAULT 5.0,
                downloads INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        console.log("Table 'themes' created successfully");
        
        // Check if empty, insert defaults
        const [rows] = await pool.query("SELECT COUNT(*) as count FROM themes");
        if (rows[0].count === 0) {
            await pool.query(`
                INSERT INTO themes (name, description, image_url, download_url, type, rating, downloads) VALUES 
                ('DarkMovie Pro', 'Giao diện tối giản tông màu tối, tập trung vào trải nghiệm xem phim. Tốc độ load cực nhanh và tương thích hoàn hảo di động.', 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Dark+Movie+Theme', '#', 'free', 4.9, 128),
                ('Anime Chill Light', 'Theme sáng, màu sắc tươi tắn phù hợp cho website anime hoặc phim bộ tuổi teen, tích hợp sẵn bình luận Facebook.', 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Light+Anime+Theme', '#', 'free', 4.7, 85),
                ('NetStream', 'Giao diện chuyên nghiệp lấy cảm hứng từ nền tảng stream số 1 thế giới. Hỗ trợ trailer popup, hiệu ứng hover mượt mà.', 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Netflix+Clone', '#', 'free', 5.0, 210)
            `);
            console.log("Inserted default themes");
        }
    } catch (err) {
        console.error(err);
    } finally {
        pool.end();
    }
}

run();
