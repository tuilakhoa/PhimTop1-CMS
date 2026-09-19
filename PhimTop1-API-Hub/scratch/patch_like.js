const fs = require('fs');
let code = fs.readFileSync('server.js', 'utf8');

code = code.replace(/categories_json LIKE \?.*?\`%\$\{typeName\}%\`/g, 'categories_json LIKE \?', `%\${req.params.slug}%`);
code = code.replace(/countries_json LIKE \?.*?\`%\$\{typeName\}%\`/g, 'countries_json LIKE \?', `%\${req.params.slug}%`);

// Since exact replacement is tricky with regex, let's just do standard string replacements.
code = code.replace(/categories_json LIKE \?', \[`%\$\{typeName\}%`\]/g, "categories_json LIKE ?', [`%${req.params.slug}%`]");
code = code.replace(/categories_json LIKE \? ORDER BY updated_at DESC LIMIT \? OFFSET \?', \[`%\$\{typeName\}%`/g, "categories_json LIKE ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [`%${req.params.slug}%`");

code = code.replace(/countries_json LIKE \?', \[`%\$\{typeName\}%`\]/g, "countries_json LIKE ?', [`%${req.params.slug}%`]");
code = code.replace(/countries_json LIKE \? ORDER BY updated_at DESC LIMIT \? OFFSET \?', \[`%\$\{typeName\}%`/g, "countries_json LIKE ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [`%${req.params.slug}%`");

fs.writeFileSync('server.js', code);
