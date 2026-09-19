const fs = require('fs');
let code = fs.readFileSync('server.js', 'utf8');

const replacement = `// Tự động fix lỗi updated_at và cập nhật Themes
pool.query("ALTER TABLE movies MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP").catch(()=>{});
pool.query("SELECT COUNT(*) as c FROM themes WHERE download_url = '/downloads/phimtop1-theme.zip'").then(([r]) => {
    if(r[0].c == 0) {
        pool.query("TRUNCATE TABLE themes");
        pool.query("INSERT INTO themes (name, description, image_url, download_url, type, rating, downloads) VALUES ('PhimTop1 Default Theme', 'Giao diện WordPress mặc định chuẩn SEO, tương thích 100% với Crawler Plugin.', '/downloads/phimtop1-theme.zip', '/downloads/phimtop1-theme.zip', 'free', 5.0, 1500), ('DarkMovie Pro', 'Giao diện tối giản tông màu đen (#000000), tốc độ load siêu tốc.', 'https://via.placeholder.com/600x400/000000/a1a1aa?text=Dark+Movie+Pro', '/downloads/darkmovie-pro.zip', 'premium', 4.9, 850), ('Anime Chill Light', 'Giao diện sáng (Light Mode), tông màu tươi tắn phù hợp cho Anime.', 'https://via.placeholder.com/600x400/f3f4f6/333333?text=Anime+Chill+Light', '/downloads/anime-chill-light.zip', 'free', 4.7, 420)");
    }
}).catch(()=>{});

app.listen(PORT, () => {`;

code = code.replace(/(\/\/ Tự động fix lỗi updated_at và cập nhật Themes[\s\S]*?)(?=app\.listen\(PORT)/, replacement);
fs.writeFileSync('server.js', code);
console.log("Đã cập nhật lại server.js");
