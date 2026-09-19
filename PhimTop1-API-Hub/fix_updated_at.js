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
        await pool.query("ALTER TABLE movies MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
        console.log("Thành công: Đã cấu hình updated_at tự động cập nhật khi có thay đổi (thêm tập mới, đổi thông tin).");
    } catch(e) {
        console.error("Lỗi:", e);
    }
    process.exit();
}
run();
