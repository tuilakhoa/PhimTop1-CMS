const fs = require('fs');
let serverJs = fs.readFileSync('server.js', 'utf-8');

// Replace admin auth to use database instead of env
const oldAuth = `    const { username, password } = req.body;
    if (username === process.env.ADMIN_USERNAME && password === process.env.ADMIN_PASSWORD) {
        res.cookie('admin_auth', 'true', { httpOnly: true, maxAge: 24 * 60 * 60 * 1000 }); // 1 day
        res.redirect('/admin');
    } else {
        res.render('admin/login', { error: 'Sai tài khoản hoặc mật khẩu' });
    }`;

const newAuth = `    try {
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
    }`;

serverJs = serverJs.replace(oldAuth, newAuth);

// Notice: app.post('/admin/login', (req, res) => { needs to be async
serverJs = serverJs.replace("app.post('/admin/login', (req, res) => {", "app.post('/admin/login', async (req, res) => {");

// Now we add the new endpoints
const newRoutes = `
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
`;

// Insert the new routes before the app.get('/cms') override at the bottom of the file
const insertionPoint = "const PORT = process.env.PORT || 3000;";
serverJs = serverJs.replace(insertionPoint, newRoutes + "\n\n" + insertionPoint);

// Update /cms route to pass settings
serverJs = serverJs.replace(
    /app\.get\('\/cms',\s*async\s*\(req,\s*res\)\s*=>\s*\{[\s\S]*?res\.render\('cms',\s*\{\s*themes:?\s*defaultThemes\s*\}\);\s*\}\s*\n\}\);/m,
    `app.get('/cms', async (req, res) => {
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
});`
);

// We should also patch the / route to fetch and pass settings
// The existing route is `app.get('/', async (req, res) => { res.render('index'); });`
serverJs = serverJs.replace(
    "app.get('/', async (req, res) => {\n    res.render('index');\n});",
    `app.get('/', async (req, res) => {\n    const settings = await getSettings();\n    res.render('index', { settings });\n});`
);


fs.writeFileSync('server.js', serverJs);
console.log("Patched server.js for advanced admin features");
