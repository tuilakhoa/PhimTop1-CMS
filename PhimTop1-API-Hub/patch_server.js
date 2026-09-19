const fs = require('fs');
let serverJs = fs.readFileSync('server.js', 'utf-8');

// Inject cookieParser require
serverJs = serverJs.replace("const path = require('path');", "const path = require('path');\nconst cookieParser = require('cookie-parser');");

// Inject middlewares
serverJs = serverJs.replace(
    "app.use(express.json());",
    "app.use(express.json());\napp.use(express.urlencoded({ extended: true }));\napp.use(cookieParser(process.env.ADMIN_COOKIE_SECRET || 'secret123'));"
);

const adminRoutes = `
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

app.post('/admin/login', (req, res) => {
    const { username, password } = req.body;
    if (username === process.env.ADMIN_USERNAME && password === process.env.ADMIN_PASSWORD) {
        res.cookie('admin_auth', 'true', { httpOnly: true, maxAge: 24 * 60 * 60 * 1000 }); // 1 day
        res.redirect('/admin');
    } else {
        res.render('admin/login', { error: 'Sai tài khoản hoặc mật khẩu' });
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
`;

// Insert admin routes before app.listen or at end
serverJs = serverJs.replace(
    "const PORT = process.env.PORT || 3000;",
    adminRoutes + "\n\nconst PORT = process.env.PORT || 3000;"
);

// Update /cms route to fetch themes
const newCmsRoute = `app.get('/cms', async (req, res) => {
    try {
        const [themes] = await pool.query("SELECT * FROM themes ORDER BY created_at DESC");
        res.render('cms', { themes });
    } catch (e) {
        console.error("Error fetching themes:", e);
        // Fallback dummy themes if DB query fails or table doesn't exist
        const defaultThemes = [
            { id: 1, name: 'DarkMovie Pro', description: 'Giao diện tối giản tông màu tối, tập trung vào trải nghiệm xem phim.', image_url: 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Dark+Movie+Theme', rating: 4.9, downloads: 128 },
            { id: 2, name: 'Anime Chill Light', description: 'Theme sáng, màu sắc tươi tắn phù hợp cho website anime.', image_url: 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Light+Anime+Theme', rating: 4.7, downloads: 85 },
            { id: 3, name: 'NetStream', description: 'Giao diện chuyên nghiệp lấy cảm hứng từ nền tảng stream số 1 thế giới.', image_url: 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Netflix+Clone', rating: 5.0, downloads: 210 }
        ];
        res.render('cms', { themes: defaultThemes });
    }
});`;

serverJs = serverJs.replace(
    /app\.get\('\/cms',\s*\(req,\s*res\)\s*=>\s*\{\s*res\.render\('cms'\);\s*\}\);/,
    newCmsRoute
);

fs.writeFileSync('server.js', serverJs);
console.log('Successfully patched server.js for admin panel');
