const fs = require('fs');

let content = fs.readFileSync('views/index.ejs', 'utf8');

// Update the generic pageDesc in index.ejs
content = content.replace(
    'let pageDesc = "PhimTop1 API";',
    'let pageDesc = "PhimTop1 API Hub cung cấp nền tảng dữ liệu phim đa định dạng JSON/XML tốc độ cao, miễn phí và không giới hạn băng thông dành cho Developer & Webmaster.";'
);
content = content.replace(
    '<meta name="keywords" content="api phim, api phim mien phi, code phim, api phim moi, phimtop1">',
    '<meta name="keywords" content="api phim, api phim mien phi, code phim, api phim json, api phim xml, phimtop1">'
);

fs.writeFileSync('views/index.ejs', content, 'utf8');
console.log('Fixed index SEO');
