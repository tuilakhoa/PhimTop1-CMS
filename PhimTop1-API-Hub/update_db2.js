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
        // Table for general settings
        await pool.query(`
            CREATE TABLE IF NOT EXISTS settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT
            )
        `);
        console.log("Table 'settings' created/verified.");
        
        // Insert default settings if empty
        const [rows] = await pool.query("SELECT COUNT(*) as count FROM settings");
        if (rows[0].count === 0) {
            await pool.query(`
                INSERT INTO settings (setting_key, setting_value) VALUES 
                ('site_title', 'PhimTop1 API Hub - Cung cấp API Phim miễn phí'),
                ('site_description', 'Hệ thống API PhimTop1 cung cấp dữ liệu phim khổng lồ, cập nhật liên tục hàng giờ.'),
                ('telegram_link', 'https://t.me/')
            `);
            console.log("Inserted default settings.");
        }

        // Table for admin users
        await pool.query(`
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL
            )
        `);
        console.log("Table 'admin_users' created/verified.");

        // Check if admin exists
        const [adminRows] = await pool.query("SELECT COUNT(*) as count FROM admin_users");
        if (adminRows[0].count === 0) {
            // Default password 'admin123'
            await pool.query(`
                INSERT INTO admin_users (username, password) VALUES 
                (?, ?)
            `, [process.env.ADMIN_USERNAME || 'admin', process.env.ADMIN_PASSWORD || 'admin123']);
            console.log("Inserted default admin user from .env.");
        }

    } catch (err) {
        console.error(err);
    } finally {
        pool.end();
    }
}

run();
