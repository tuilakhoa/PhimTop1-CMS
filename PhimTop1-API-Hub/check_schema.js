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
        const [rows] = await pool.query("SHOW CREATE TABLE movies");
        console.log(rows[0]['Create Table']);
    } catch(e) {
        console.error(e);
    }
    process.exit();
}
run();
