const express = require('express');
const xmljs = require('xml-js');

function sendApiResponse(req, res, data) {
    const format = req.query.format ? req.query.format.toLowerCase() : 'json';
    if (format === 'xml') {
        res.header('Content-Type', 'application/xml');
        // Try to handle null/undefined safely
        const cleanData = JSON.parse(JSON.stringify(data));
        const xmlData = {
            _declaration: { _attributes: { version: '1.0', encoding: 'utf-8' } },
            response: cleanData
        };
        try {
            const xmlString = xmljs.js2xml(xmlData, { compact: true, spaces: 4 });
            res.send(xmlString);
        } catch(e) {
            res.status(500).send('<error>XML Conversion Failed</error>');
        }
    } else {
        res.json(data);
    }
}
const cors = require('cors');
const axios = require('axios');
const mysql = require('mysql2/promise');
const cron = require('node-cron');
const path = require('path');
const cookieParser = require('cookie-parser');
require('dotenv').config();


const GENRES = [
    {name: "Hành Động", slug: "hanh-dong"}, {name: "Tình Cảm", slug: "tinh-cam"}, {name: "Hài Hước", slug: "hai-huoc"},
    {name: "Cổ Trang", slug: "co-trang"}, {name: "Tâm Lý", slug: "tam-ly"}, {name: "Hình Sự", slug: "hinh-su"},
    {name: "Chiến Tranh", slug: "chien-tranh"}, {name: "Thể Thao", slug: "the-thao"}, {name: "Võ Thuật", slug: "vo-thuat"},
    {name: "Viễn Tưởng", slug: "vien-tuong"}, {name: "Phiêu Lưu", slug: "phieu-luu"}, {name: "Khoa Học", slug: "khoa-hoc"},
    {name: "Kinh Dị", slug: "kinh-di"}, {name: "Âm Nhạc", slug: "am-nhac"}, {name: "Thần Thoại", slug: "than-thoai"},
    {name: "Tài Liệu", slug: "tai-lieu"}, {name: "Gia Đình", slug: "gia-dinh"}, {name: "Chính kịch", slug: "chinh-kich"},
    {name: "Bí ẩn", slug: "bi-an"}, {name: "Học Đường", slug: "hoc-duong"}, {name: "Kinh Điển", slug: "kinh-dien"},
    {name: "Thanh Xuân", slug: "thanh-xuan"}, {name: "Trinh Thám", slug: "trinh-tham"}
];

const COUNTRIES = [
    {name: "Hàn Quốc", slug: "han-quoc"}, {name: "Trung Quốc", slug: "trung-quoc"}, {name: "Thái Lan", slug: "thai-lan"},
    {name: "Việt Nam", slug: "viet-nam"}, {name: "Âu Mỹ", slug: "au-my"}, {name: "Đài Loan", slug: "dai-loan"},
    {name: "Hồng Kông", slug: "hong-kong"}, {name: "Nhật Bản", slug: "nhat-ban"}, {name: "Ấn Độ", slug: "an-do"},
    {name: "Khác", slug: "khac"}
];

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser(process.env.ADMIN_COOKIE_SECRET || 'secret123'));
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

// Route Trang chủ: Render trang danh sách phim
app.get('/', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 36;
        const offset = (page - 1) * limit;

        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies');
        const [todayRes] = await pool.query('SELECT COUNT(id) as today_total FROM movies WHERE updated_at >= NOW() - INTERVAL 24 HOUR');
        const [movies] = await pool.query('SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at, tmdb_vote, imdb_vote, countries_json FROM movies ORDER BY updated_at DESC LIMIT ? OFFSET ?', [limit, offset]);
        
        const taxonomies = await getDynamicTaxonomies();
        res.render('index', { 
            totalMovies: countRes[0].total,
            todayMovies: todayRes[0].today_total,
            movies: movies,
            page: page,
            isSearch: false,
            keyword: ''
        , globalGenres: taxonomies.genres, globalCountries: taxonomies.countries});
    } catch (err) {
        res.status(500).send(err.message);
    }
});


// Route danh sách theo định dạng (Phim bộ, Phim lẻ, Hoạt hình, TV Shows)
app.get('/danh-sach/:type', async (req, res) => {
    try {
        const typeMap = { 'series': 'series', 'single': 'single', 'hoathinh': 'hoathinh', 'tvshows': 'tvshows' };
        const dbType = typeMap[req.params.type];
        if (!dbType) return res.status(404).send('Không tìm thấy danh sách');

        const page = parseInt(req.query.page) || 1;
        const limit = 36;
        const offset = (page - 1) * limit;

        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies WHERE type = ?', [dbType]);
        const [movies] = await pool.query('SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at, tmdb_vote, imdb_vote, countries_json FROM movies WHERE type = ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [dbType, limit, offset]);
        
        let typeName = req.params.type === 'series' ? 'Phim Bộ' : (req.params.type === 'single' ? 'Phim Lẻ' : 'Hoạt Hình');

        const taxonomies = await getDynamicTaxonomies();
        res.render('index', { 
            totalMovies: countRes[0].total,
            todayMovies: 0,
            movies: movies,
            page: page,
            isSearch: false,
            isCategory: true,
            categoryType: 'list',
            categoryName: typeName,
            baseUrl: '/danh-sach/' + req.params.type,
            keyword: '',
            globalGenres: taxonomies.genres, globalCountries: taxonomies.countries});
    } catch (err) {
        res.status(500).send(err.message);
    }
});

// API Cung cấp dữ liệu (danh sách)
app.get('/api/danh-sach', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 24;
        const offset = (page - 1) * limit;
        
        const [rows] = await pool.query('SELECT name, slug, year, origin_name, status, type, thumb_url, updated_at as modified FROM movies ORDER BY updated_at DESC LIMIT ? OFFSET ?', [limit, offset]);
        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies');
        
        sendApiResponse(req, res, {
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

// Route trang Tải CMS / Plugins
app.get('/cms', async (req, res) => {
    const settings = await getSettings();
    try {
        const [themes] = await pool.query("SELECT * FROM themes ORDER BY created_at DESC");
        res.render('cms', { themes, settings });
    } catch (e) {
        console.error("Error fetching themes:", e);
        const defaultThemes = [
            { id: 1, name: 'DarkMovie Pro', description: 'Giao diện tối giản tông màu tối...', image_url: '', rating: 4.9, downloads: 128 }
        ];
        res.render('cms', { themes: defaultThemes, settings });
    }
});

// Route trang tài liệu API
app.get('/api-document', async (req, res) => {
    const taxonomies = await getDynamicTaxonomies();
    res.render('api-document', { globalGenres: taxonomies.genres, globalCountries: taxonomies.countries });
});

// API Chi tiết phim (JSON)
app.get('/api/phim/:slug', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM movies WHERE slug = ? LIMIT 1', [req.params.slug]);
        if (rows.length === 0) return sendApiResponse(req, res, { status: 'error', message: 'Không tìm thấy phim' });
        
        const movie = rows[0];
        const [episodes] = await pool.query('SELECT * FROM episodes WHERE movie_slug = ?', [movie.slug]);
        
        // Group episodes by server_name
        const groupedMap = {};
        episodes.forEach(ep => {
            if (!groupedMap[ep.server_name]) {
                groupedMap[ep.server_name] = {
                    server_name: ep.server_name,
                    server_data: []
                };
            }
            groupedMap[ep.server_name].server_data.push({
                name: ep.name,
                slug: ep.slug,
                link_embed: ep.embed_url,
                link_m3u8: ep.m3u8_url
            });
        });
        const parsedEpisodes = Object.values(groupedMap);

        sendApiResponse(req, res, {
            status: 'success',
            movie: movie,
            episodes: parsedEpisodes
        });
    } catch (err) {
        res.status(500).json({ status: 'error', message: err.message });
    }
});

// Route trang chi tiết phim (Web)
app.get('/phim/:slug', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM movies WHERE slug = ? LIMIT 1', [req.params.slug]);
        if (rows.length === 0) return res.status(404).send('Không tìm thấy phim');
        
        const movie = rows[0];
        const [episodes] = await pool.query('SELECT * FROM episodes WHERE movie_slug = ? ORDER BY id ASC', [movie.slug]);
        
        // Group episodes by server_name
        const groupedMap = {};
        episodes.forEach(ep => {
            if (!groupedMap[ep.server_name]) {
                groupedMap[ep.server_name] = {
                    server_name: ep.server_name,
                    server_data: []
                };
            }
            groupedMap[ep.server_name].server_data.push({
                name: ep.name,
                slug: ep.slug,
                link_embed: ep.embed_url,
                link_m3u8: ep.m3u8_url
            });
        });
        const episodesGrouped = Object.values(groupedMap);
        
        const taxonomies = await getDynamicTaxonomies();
        res.render('detail', { movie: movie, episodesGrouped: episodesGrouped, globalGenres: taxonomies.genres, globalCountries: taxonomies.countries});
    } catch (err) {
        res.status(500).send(err.message);
    }
});


// Route Tìm kiếm
app.get('/search', async (req, res) => {
    try {
        const keyword = req.query.keyword || '';
        const page = parseInt(req.query.page) || 1;
        const limit = 36;
        const offset = (page - 1) * limit;

        let totalMovies = 0;
        let movies = [];
        const taxonomies = await getDynamicTaxonomies();
        
        try {
            // Attempt to search with Meilisearch
            const { MeiliSearch } = require('meilisearch');
            const client = new MeiliSearch({
                host: process.env.MEILI_HOST || 'http://127.0.0.1:7700',
                apiKey: process.env.MEILI_MASTER_KEY || 'masterKey123'
            });
            const index = client.index('movies');
            const searchRes = await index.search(keyword, {
                limit: limit,
                offset: offset
            });
            
            totalMovies = searchRes.estimatedTotalHits;
            movies = searchRes.hits.map(h => ({
                name: h.name, slug: h.id, year: h.year, origin_name: h.origin_name,
                status: h.status, type: h.type, thumb_url: h.thumb_url, tmdb_vote: h.tmdb_vote,
                // Meilisearch returns id as slug based on sync_meilisearch.js mapping
                actor: h.actor
            }));
        } catch (meiliError) {
            console.log('Meilisearch not available, falling back to MySQL search...');
            // Fallback to MySQL
            const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies WHERE name LIKE ? OR origin_name LIKE ? OR actor LIKE ?', [`%${keyword}%`, `%${keyword}%`, `%${keyword}%`]);
            const [sqlMovies] = await pool.query('SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at, tmdb_vote, imdb_vote, countries_json FROM movies WHERE name LIKE ? OR origin_name LIKE ? OR actor LIKE ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [`%${keyword}%`, `%${keyword}%`, `%${keyword}%`, limit, offset]);
            totalMovies = countRes[0].total;
            movies = sqlMovies;
        }
        
        res.render('index', { 
            totalMovies: totalMovies,
            todayMovies: 0,
            movies: movies,
            page: page,
            isSearch: true,
            keyword: keyword,
            globalGenres: taxonomies.genres, globalCountries: taxonomies.countries});
    } catch (err) {
        res.status(500).send(err.message);
    }
});

// Node-Cron: Tự động cào dữ liệu ngầm từ 3 nguồn (chạy mỗi 10 phút)
cron.schedule('*/5 * * * *', async () => {
    try {
        // Tự động enforce trigger để đảm bảo updated_at luôn tự động cập nhật khi crawler sửa data
        await pool.query("ALTER TABLE movies MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    } catch(e) {}
});


// Admin Middleware
function requireAdmin(req, res, next) {
    if (req.cookies.admin_auth === 'true') {
        return next();
    }
    res.redirect('/admin/login');
}

// Admin Routes
app.get('/admin/login', (req, res) => {
    res.render('admin/login', { error: null });
});

app.post('/admin/login', async (req, res) => {
    try {
        const { username, password } = req.body;
        const [users] = await pool.query("SELECT * FROM admin_users WHERE username = ? AND password = ?", [username, password]);
        if (users.length > 0) {
            res.cookie('admin_auth', 'true', { httpOnly: true, maxAge: 24 * 60 * 60 * 1000 }); // 1 day
            res.redirect('/admin');
        } else {
            res.render('admin/login', { error: 'Sai tài khoản hoặc mật khẩu' });
        }
    } catch(e) {
        // Fallback to env if DB fails
        const { username, password } = req.body;
        if (username === (process.env.ADMIN_USERNAME || 'admin') && password === (process.env.ADMIN_PASSWORD || 'admin123')) {
            res.cookie('admin_auth', 'true', { httpOnly: true, maxAge: 24 * 60 * 60 * 1000 });
            res.redirect('/admin');
        } else {
            res.render('admin/login', { error: 'Sai tài khoản hoặc mật khẩu' });
        }
    }
});

app.get('/admin/logout', (req, res) => {
    res.clearCookie('admin_auth');
    res.redirect('/admin/login');
});

app.get('/admin', requireAdmin, async (req, res) => {
    try {
        const [rows] = await pool.query("SELECT COUNT(*) as count FROM themes");
        res.render('admin/dashboard', { stats: { themes: rows[0].count } });
    } catch (e) {
        res.render('admin/dashboard', { stats: { themes: 0 } });
    }
});

app.get('/admin/themes', requireAdmin, async (req, res) => {
    try {
        const [themes] = await pool.query("SELECT * FROM themes ORDER BY created_at DESC");
        res.render('admin/themes', { themes });
    } catch (e) {
        res.render('admin/themes', { themes: [] });
    }
});

app.post('/admin/themes', requireAdmin, async (req, res) => {
    try {
        const { name, description, image_url, download_url, type, rating } = req.body;
        await pool.query(
            "INSERT INTO themes (name, description, image_url, download_url, type, rating, downloads) VALUES (?, ?, ?, ?, ?, ?, 0)",
            [name, description, image_url, download_url, type, rating || 5.0]
        );
    } catch (e) {
        console.error("Lỗi thêm theme:", e);
    }
    res.redirect('/admin/themes');
});

app.post('/admin/themes/:id/delete', requireAdmin, async (req, res) => {
    try {
        await pool.query("DELETE FROM themes WHERE id = ?", [req.params.id]);
    } catch (e) {
        console.error("Lỗi xóa theme:", e);
    }
    res.redirect('/admin/themes');
});



// --- New Admin Features ---

// Edit Theme UI
app.get('/admin/themes/:id/edit', requireAdmin, async (req, res) => {
    try {
        const [themes] = await pool.query("SELECT * FROM themes WHERE id = ?", [req.params.id]);
        if (themes.length > 0) {
            res.render('admin/theme_edit', { theme: themes[0] });
        } else {
            res.redirect('/admin/themes');
        }
    } catch (e) {
        res.redirect('/admin/themes');
    }
});

// Edit Theme Post
app.post('/admin/themes/:id/edit', requireAdmin, async (req, res) => {
    try {
        const { name, description, image_url, download_url, type, rating } = req.body;
        await pool.query(
            "UPDATE themes SET name=?, description=?, image_url=?, download_url=?, type=?, rating=? WHERE id=?",
            [name, description, image_url, download_url, type, rating, req.params.id]
        );
    } catch (e) {
        console.error("Lỗi sửa theme:", e);
    }
    res.redirect('/admin/themes');
});

// Settings UI
app.get('/admin/settings', requireAdmin, async (req, res) => {
    try {
        const [rows] = await pool.query("SELECT * FROM settings");
        const settings = {};
        rows.forEach(r => settings[r.setting_key] = r.setting_value);
        res.render('admin/settings', { settings });
    } catch (e) {
        res.render('admin/settings', { settings: {} });
    }
});

// Settings Post
app.post('/admin/settings', requireAdmin, async (req, res) => {
    try {
        const { site_title, site_description, telegram_link } = req.body;
        const updates = [
            { key: 'site_title', val: site_title },
            { key: 'site_description', val: site_description },
            { key: 'telegram_link', val: telegram_link }
        ];
        
        for (let u of updates) {
            await pool.query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?", [u.key, u.val, u.val]);
        }
    } catch (e) {
        console.error("Lỗi sửa settings:", e);
    }
    res.redirect('/admin/settings');
});

// Account UI
app.get('/admin/account', requireAdmin, async (req, res) => {
    res.render('admin/account', { error: null, success: null });
});

// Account Post
app.post('/admin/account', requireAdmin, async (req, res) => {
    try {
        const { old_password, new_password, confirm_password } = req.body;
        
        if (new_password !== confirm_password) {
            return res.render('admin/account', { error: 'Mật khẩu xác nhận không khớp', success: null });
        }
        
        const [users] = await pool.query("SELECT * FROM admin_users");
        if (users.length > 0) {
            const admin = users[0];
            if (admin.password !== old_password) {
                return res.render('admin/account', { error: 'Mật khẩu cũ không đúng', success: null });
            }
            
            await pool.query("UPDATE admin_users SET password = ? WHERE id = ?", [new_password, admin.id]);
            return res.render('admin/account', { error: null, success: 'Đổi mật khẩu thành công!' });
        } else {
             return res.render('admin/account', { error: 'Không tìm thấy tài khoản admin trong DB', success: null });
        }
    } catch (e) {
        return res.render('admin/account', { error: 'Có lỗi xảy ra', success: null });
    }
});

// Helper for generic settings fetching
async function getSettings() {
    try {
        const [rows] = await pool.query("SELECT * FROM settings");
        const s = {};
        rows.forEach(r => s[r.setting_key] = r.setting_value);
        return s;
    } catch(e) {
        return { 
            site_title: 'PhimTop1 API Hub - Cung cấp API Phim miễn phí', 
            site_description: 'Hệ thống API PhimTop1 cung cấp dữ liệu phim khổng lồ, cập nhật liên tục hàng giờ.', 
            telegram_link: 'https://t.me/' 
        };
    }
}


const PORT = process.env.PORT || 3000;
// Tự động fix lỗi updated_at và cập nhật Themes
pool.query("ALTER TABLE movies MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP").catch(()=>{});
pool.query("SELECT COUNT(*) as c FROM themes WHERE download_url = '/downloads/phimtop1-theme.zip'").then(([r]) => {
    if(r[0].c == 0) {
        pool.query("TRUNCATE TABLE themes");
        pool.query("INSERT INTO themes (name, description, image_url, download_url, type, rating, downloads) VALUES ('PhimTop1 Default Theme', 'Giao diện WordPress mặc định chuẩn SEO, tương thích 100% với Crawler Plugin.', '/downloads/phimtop1-theme.zip', '/downloads/phimtop1-theme.zip', 'free', 5.0, 1500), ('DarkMovie Pro', 'Giao diện tối giản tông màu đen (#000000), tốc độ load siêu tốc.', 'https://via.placeholder.com/600x400/000000/a1a1aa?text=Dark+Movie+Pro', '/downloads/darkmovie-pro.zip', 'premium', 4.9, 850), ('Anime Chill Light', 'Giao diện sáng (Light Mode), tông màu tươi tắn phù hợp cho Anime.', 'https://via.placeholder.com/600x400/f3f4f6/333333?text=Anime+Chill+Light', '/downloads/anime-chill-light.zip', 'free', 4.7, 420)");
    }
}).catch(()=>{});

// Tự động build themes zip
try {
    const { execSync } = require('child_process');
    const fs = require('fs');
    if (fs.existsSync('public/downloads/phimtop1-theme') && !fs.existsSync('public/downloads/phimtop1-theme.zip')) {
        console.log("Đang tự động tạo các bản zip của Themes...");
        const cmd = `
        cp new_header.php public/downloads/phimtop1-theme/header.php && 
        cd public/downloads && 
        zip -r phimtop1-theme.zip phimtop1-theme/ && 
        cp -r phimtop1-theme darkmovie-pro && 
        sed -i 's/#0f1115/#000000/g' darkmovie-pro/header.php && 
        sed -i 's/#181a20/#0a0a0a/g' darkmovie-pro/header.php && 
        sed -i 's/text-kk-red/text-blue-500/g' darkmovie-pro/header.php && 
        zip -r darkmovie-pro.zip darkmovie-pro/ && 
        cp -r phimtop1-theme anime-chill-light && 
        sed -i 's/#0f1115/#f3f4f6/g' anime-chill-light/header.php && 
        sed -i 's/#181a20/#ffffff/g' anime-chill-light/header.php && 
        sed -i 's/text-gray-300/text-gray-800/g' anime-chill-light/header.php && 
        sed -i 's/text-white/text-gray-900/g' anime-chill-light/header.php && 
        sed -i 's/#272a30/#e5e7eb/g' anime-chill-light/header.php && 
        sed -i 's/text-kk-red/text-pink-500/g' anime-chill-light/header.php && 
        zip -r anime-chill-light.zip anime-chill-light/
        `;
        execSync(cmd, { stdio: 'ignore' });
        console.log("Đã tạo xong 3 file zip Themes.");
    }
} catch (e) {
    console.log("Lỗi tạo themes:", e.message);
}

app.listen(PORT, () => {
    console.log(`🚀 PhimTop1 API Hub đang chạy tại http://localhost:${PORT}`);
});

// Memory Cache
let cachedData = {
    genres: [],
    countries: [],
    lastFetch: 0
};

async function getDynamicTaxonomies() {
    if (Date.now() - cachedData.lastFetch < 3600000 && cachedData.genres.length > 0) {
        return cachedData;
    }
    
    try {
        const [rows] = await pool.query('SELECT categories_json, countries_json FROM movies');
        const genreSet = new Map();
        const countrySet = new Map();
        
        rows.forEach(row => {
            if (row.categories_json) {
                try {
                    let cats = JSON.parse(row.categories_json);
                    cats.forEach(c => {
                        if (c.name && c.slug) genreSet.set(c.slug, c.name);
                    });
                } catch(e) {}
            }
            if (row.countries_json) {
                try {
                    let countries = JSON.parse(row.countries_json);
                    countries.forEach(c => {
                        if (c.name && c.slug) countrySet.set(c.slug, c.name);
                    });
                } catch(e) {}
            }
        });
        
        cachedData.genres = Array.from(genreSet, ([slug, name]) => ({ name, slug }));
        cachedData.countries = Array.from(countrySet, ([slug, name]) => ({ name, slug }));
        cachedData.lastFetch = Date.now();
    } catch (e) {
        console.error(e);
    }
    return cachedData;
}

// API Thể loại
app.get('/api/the-loai', async (req, res) => {
    try {
        const data = await getDynamicTaxonomies();
        sendApiResponse(req, res, {
            status: true,
            data: { items: data.genres }
        });
    } catch (err) {
        sendApiResponse(req, res, { status: false, message: err.message, data: { items: [] } });
    }
});

// API Quốc gia
app.get('/api/quoc-gia', async (req, res) => {
    try {
        const data = await getDynamicTaxonomies();
        sendApiResponse(req, res, {
            status: true,
            data: { items: data.countries }
        });
    } catch (err) {
        sendApiResponse(req, res, { status: false, message: err.message, data: { items: [] } });
    }
});

// Route Thể loại
app.get('/the-loai/:slug', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 36;
        const offset = (page - 1) * limit;

        const taxonomies = await getDynamicTaxonomies();
        const genre = taxonomies.genres.find(g => g.slug === req.params.slug);
        const typeName = genre ? genre.name : req.params.slug;

        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies WHERE categories_json LIKE ?', [`%${req.params.slug}%`]);
        const [movies] = await pool.query('SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at, tmdb_vote, imdb_vote, countries_json FROM movies WHERE categories_json LIKE ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [`%${req.params.slug}%`, limit, offset]);

        res.render('index', { 
            totalMovies: countRes[0].total,
            todayMovies: 0,
            movies: movies,
            page: page,
            isSearch: false,
            isCategory: true,
            categoryType: 'genre',
            categoryName: typeName,
            baseUrl: '/the-loai/' + req.params.slug,
            keyword: '',
            globalGenres: taxonomies.genres, globalCountries: taxonomies.countries});
    } catch (err) {
        res.status(500).send(err.message);
    }
});

// Route Quốc gia
app.get('/quoc-gia/:slug', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 36;
        const offset = (page - 1) * limit;

        const taxonomies = await getDynamicTaxonomies();
        const country = taxonomies.countries.find(c => c.slug === req.params.slug);
        const typeName = country ? country.name : req.params.slug;

        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies WHERE countries_json LIKE ?', [`%${req.params.slug}%`]);
        const [movies] = await pool.query('SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at, tmdb_vote, imdb_vote, countries_json FROM movies WHERE countries_json LIKE ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [`%${req.params.slug}%`, limit, offset]);

        res.render('index', { 
            totalMovies: countRes[0].total,
            todayMovies: 0,
            movies: movies,
            page: page,
            isSearch: false,
            isCategory: true,
            categoryType: 'country',
            categoryName: typeName,
            baseUrl: '/quoc-gia/' + req.params.slug,
            keyword: '',
            globalGenres: taxonomies.genres, globalCountries: taxonomies.countries});
    } catch (err) {
        res.status(500).send(err.message);
    }
});
