const fs = require('fs');
let code = fs.readFileSync('server.js', 'utf8');

const target = `app.listen(PORT, () => {`;
const replacement = `// Tự động build themes zip
try {
    const { execSync } = require('child_process');
    const fs = require('fs');
    if (fs.existsSync('public/downloads/phimtop1-theme') && !fs.existsSync('public/downloads/phimtop1-theme.zip')) {
        console.log("Đang tự động tạo các bản zip của Themes...");
        const cmd = \`
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
        \`;
        execSync(cmd, { stdio: 'ignore' });
        console.log("Đã tạo xong 3 file zip Themes.");
    }
} catch (e) {
    console.log("Lỗi tạo themes:", e.message);
}

app.listen(PORT, () => {`;

code = code.replace(target, replacement);
fs.writeFileSync('server.js', code);
console.log("Đã chèn lệnh build themes vào server.js");
