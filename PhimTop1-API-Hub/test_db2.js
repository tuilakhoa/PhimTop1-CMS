const mysql = require('mysql2/promise');
async function test() {
    const pool = mysql.createPool({
        host: '127.0.0.1',
        user: 'phimtop1',
        password: 'phimtop1',
        database: 'phimtop1'
    });
    const [rows] = await pool.query('SHOW COLUMNS FROM movies');
    console.log(rows);
    pool.end();
}
test();
