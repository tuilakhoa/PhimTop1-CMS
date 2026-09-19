const fs = require('fs');

['views/index.ejs', 'views/detail.ejs', 'views/api-document.ejs', 'views/cms.ejs'].forEach(file => {
    if (fs.existsSync(file)) {
        let content = fs.readFileSync(file, 'utf8');
        // Replace max-w-7xl with max-w-[1440px]
        content = content.replace(/max-w-7xl/g, 'max-w-[1440px]');
        // If it was max-w-6xl somewhere, maybe change that too for consistency
        content = content.replace(/max-w-6xl/g, 'max-w-[1440px]');
        fs.writeFileSync(file, content, 'utf8');
    }
});
console.log("Updated widths!");
