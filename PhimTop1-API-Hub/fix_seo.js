const fs = require('fs');

const files = ['views/index.ejs', 'views/detail.ejs', 'views/api-document.ejs', 'views/cms.ejs'];

files.forEach(file => {
    if (fs.existsSync(file)) {
        let content = fs.readFileSync(file, 'utf8');
        
        // 1. API Document Intro text
        content = content.replace(
            /trả về dữ liệu định dạng JSON, giúp các Webmaster/g,
            'trả về dữ liệu định dạng JSON và XML, giúp các Webmaster'
        );
        
        // 2. Footer text
        content = content.replace(
            /định dạng JSON tốc độ cao/g,
            'đa định dạng JSON/XML tốc độ cao'
        );
        
        // 3. Meta descriptions (SEO)
        content = content.replace(
            /định dạng JSON giúp các Webmaster/g,
            'định dạng JSON và XML giúp các Webmaster'
        );
        
        content = content.replace(
            /định dạng JSON miễn phí/g,
            'định dạng JSON/XML miễn phí'
        );
        
        // 4. API Document generic description if it has one
        content = content.replace(
            /api phim json/g,
            'api phim json, api phim xml'
        );

        fs.writeFileSync(file, content, 'utf8');
    }
});

console.log("Updated texts for JSON/XML mentions!");
