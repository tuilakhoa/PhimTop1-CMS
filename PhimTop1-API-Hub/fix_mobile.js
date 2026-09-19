const fs = require('fs');

['views/index.ejs', 'views/detail.ejs'].forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    
    content = content.replace(/<% if\(locals\.globalGenres\)\s*\{\s*globalGenres\.forEach\(g\s*=>\s*\{\s*%>/g, 
        `<% if(locals.globalGenres) { 
                        const sorted = [...globalGenres].sort((a,b) => a.name.localeCompare(b.name, 'vi'));
                        sorted.forEach(g => { %>`);
    
    content = content.replace(/<% if\(locals\.globalCountries\)\s*\{\s*globalCountries\.forEach\(c\s*=>\s*\{\s*%>/g, 
        `<% if(locals.globalCountries) { 
                        const sorted = [...globalCountries].sort((a,b) => a.name.localeCompare(b.name, 'vi'));
                        sorted.forEach(c => { %>`);
    
    // Add capitalize
    content = content.replace(/class="text-gray-400 text-\[13px\] hover:text-white truncate"/g, 'class="text-gray-400 text-[13px] hover:text-white truncate capitalize"');

    fs.writeFileSync(file, content, 'utf8');
});

console.log("Updated Mobile EJS files!");
