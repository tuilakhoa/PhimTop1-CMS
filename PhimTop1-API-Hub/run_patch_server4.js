const fs = require('fs');
let code = fs.readFileSync('server.js', 'utf8');

const targetRegex = /const cmd = `[\s\S]*?`;/;
const replacement = `const cmd = \`
        cp new_header.php public/downloads/phimtop1-theme/header.php && 
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
        \`;`;

code = code.replace(targetRegex, replacement);
fs.writeFileSync('server.js', code);
console.log("Đã update server.js auto builder");
