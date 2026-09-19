const express = require('express');
const cors = require('cors');
const axios = require('axios');
const mysql = require('mysql2/promise');
const cron = require('node-cron');
const path = require('path');
require('dotenv').config();

const app = express();
app.use(cors());
app.use(express.json());
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.static('public'));

// Database Connection
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'phimtop1_cms',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Route Giao diện Landing Page (giống kkphim2.com)
app.get('/', async (req, res) => {
    try {
        // Thống kê nhanh từ DB
        const [movieCount] = await pool.query('SELECT COUNT(id) as total FROM movies');
        const [recentMovies] = await pool.query('SELECT name, slug, year, origin_name, status, type, thumb_url, modified FROM movies ORDER BY modified DESC LIMIT 10');
        
        res.render('index', {
            totalMovies: movieCount[0].total,
            recentMovies: recentMovies
        });
    } catch (err) {
        // Fallback nếu chưa cấu hình DB
        res.render('index', {
            totalMovies: 0,
            recentMovies: []
        });
    }
});

// API Cung cấp dữ liệu (danh sách)
app.get('/api/danh-sach', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 24;
        const offset = (page - 1) * limit;
        
        const [rows] = await pool.query('SELECT name, slug, year, origin_name, status, type, thumb_url, modified FROM movies ORDER BY modified DESC LIMIT ? OFFSET ?', [limit, offset]);
        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies');
        
        res.json({
            status: 'success',
            data: {
                items: rows,
                total: countRes[0].total,
                page: page
            }
        });
    } catch (err) {
        res.status(500).json({ status: 'error', message: err.message });
    }
});

// API Chi tiết phim
app.get('/api/phim/:slug', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM movies WHERE slug = ? LIMIT 1', [req.params.slug]);
        if (rows.length === 0) return res.status(404).json({ status: 'error', message: 'Không tìm thấy phim' });
        
        const movie = rows[0];
        // TODO: Lấy thêm episodes từ bảng episodes (nếu cấu trúc DB của PhimTop1 giống Ophim)
        const [episodes] = await pool.query('SELECT * FROM episodes WHERE movie_id = ?', [movie.id]);
        
        res.json({
            status: 'success',
            movie: movie,
            episodes: episodes
        });
    } catch (err) {
        res.status(500).json({ status: 'error', message: err.message });
    }
});

// Node-Cron: Tự động cào dữ liệu ngầm từ 3 nguồn (chạy mỗi 10 phút)
cron.schedule('*/10 * * * *', async () => {
    console.log('[Crawler] Đang quét phim mới từ KKPhim, Nguồn C, VsMov...');
    // TODO: Chuyển logic cào từ PhimTop1-CMS sang đây
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`🚀 PhimTop1 API Hub đang chạy tại http://localhost:${PORT}`);
});
