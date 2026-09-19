const fs = require('fs');
let code = fs.readFileSync('server.js', 'utf8');

const target = `app.listen(PORT, () => {`;
const replacement = `// Tự động fix lỗi updated_at và cập nhật Themes
pool.query("ALTER TABLE movies MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP").catch(()=>{});
pool.query("SELECT COUNT(*) as c FROM themes WHERE download_url = '/downloads/phimtop1-theme.zip'").then(([r]) => {
    if(r[0].c == 0) {
        pool.query("TRUNCATE TABLE themes");
        pool.query("INSERT INTO themes (name, description, image_url, download_url, type, rating, downloads) VALUES ('PhimTop1 Default Theme', 'Giao diện WordPress mặc định chuẩn SEO, tương thích 100% với Crawler Plugin.', '/downloads/phimtop1-theme.zip', '/downloads/phimtop1-theme.zip', 'free', 5.0, 1500), ('DarkMovie Pro', 'Giao diện tối giản, tập trung trải nghiệm xem phim (Sắp ra mắt).', 'https://via.placeholder.com/600x400/1f2937/a1a1aa?text=Dark+Movie+Theme', '#', 'premium', 4.9, 0)");
    }
}).catch(()=>{});

app.listen(PORT, () => {`;

if (code.includes('// Tự động fix lỗi updated_at khi khởi động server')) {
    code = code.replace(/(\/\/ Tự động fix lỗi updated_at khi khởi động server[\s\S]*?)(?=app\.listen\(PORT)/, replacement);
} else {
    code = code.replace(target, replacement);
}

fs.writeFileSync('server.js', code);
console.log("Đã cập nhật auto-patch server.js");
