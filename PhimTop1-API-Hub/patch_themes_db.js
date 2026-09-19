const mysql = require('mysql2/promise');
require('dotenv').config();

async function run() {
    const pool = mysql.createPool({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'phimtop1_cms'
    });

    try {
        console.log("Đang cập nhật thư viện themes...");
        await pool.query("TRUNCATE TABLE themes");
        
        await pool.query(`
            INSERT INTO themes (name, description, image_url, download_url, type, rating, downloads) VALUES 
            ('PhimTop1 Default Theme', 'Giao diện mặc định của PhimTop1, tương thích hoàn hảo với Plugin Crawler. Tối ưu SEO và chuẩn di động.', '/downloads/phimtop1-theme.zip', '/downloads/phimtop1-theme.zip', 'free', 5.0, 1500),
            ('DarkMovie Pro', 'Giao diện tối giản tông màu tối, tốc độ load cực nhanh. Hiện đang trong quá trình hoàn thiện.', 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Dark+Movie+Theme', '#', 'premium', 4.9, 0),
            ('Anime Chill Light', 'Theme sáng, màu sắc tươi tắn phù hợp cho anime. Hiện đang trong quá trình hoàn thiện.', 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Light+Anime+Theme', '#', 'free', 4.7, 0)
        `);
        console.log("Thành công: Đã cập nhật thư viện themes với 1 theme hoàn thiện có thể tải về.");
    } catch(e) {
        console.error("Lỗi:", e.message);
    }
    process.exit();
}
run();
