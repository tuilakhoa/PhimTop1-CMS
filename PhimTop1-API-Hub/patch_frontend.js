const fs = require('fs');

function patchFile(file) {
    if (!fs.existsSync(file)) return;
    let content = fs.readFileSync(file, 'utf-8');
    
    // Replace <title>
    content = content.replace(/<title>.*?<\/title>/, '<title><%= typeof settings !== "undefined" && settings.site_title ? settings.site_title : "PhimTop1 API Hub" %></title>');
    
    // Replace Telegram link
    content = content.replace(/href="https:\/\/t.me\/[^"]*"/g, 'href="<%= typeof settings !== "undefined" && settings.telegram_link ? settings.telegram_link : "https://t.me/" %>"');
    
    // Replace Meta description if exists, or insert it
    if(content.includes('<meta name="description"')) {
        content = content.replace(/<meta name="description" content=".*?"/, '<meta name="description" content="<%= typeof settings !== "undefined" && settings.site_description ? settings.site_description : "" %>"');
    } else {
        content = content.replace('</head>', '    <meta name="description" content="<%= typeof settings !== "undefined" && settings.site_description ? settings.site_description : "" %>">\n</head>');
    }

    fs.writeFileSync(file, content);
}

patchFile('views/cms.ejs');
patchFile('views/index.ejs');

console.log("Patched frontend EJS files");
