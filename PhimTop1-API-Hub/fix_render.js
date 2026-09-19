const fs = require('fs');
let content = fs.readFileSync('server.js', 'utf8');

// Replace standard render block with async taxonomy fetch
content = content.replace(/res\.render\((['"])(\w+)\1,\s*\{([\s\S]*?)globalGenres:\s*GENRES,\s*globalCountries:\s*COUNTRIES\s*\}\);/g, (match, p1, viewName, p3) => {
    return `const taxonomies = await getDynamicTaxonomies();\n        res.render('${viewName}', {${p3}globalGenres: taxonomies.genres, globalCountries: taxonomies.countries});`;
});

// Since the search route was added and it's missing the comma before globalGenres in some places, let's just do a simpler regex.
content = content.replace(/globalGenres:\s*GENRES,?/g, 'globalGenres: (await getDynamicTaxonomies()).genres,');
content = content.replace(/globalCountries:\s*COUNTRIES/g, 'globalCountries: (await getDynamicTaxonomies()).countries');

fs.writeFileSync('server.js', content, 'utf8');
