const fs = require('fs');

let content = fs.readFileSync('server.js', 'utf8');

// The target query might be using CURDATE()
const targetStr1 = "SELECT COUNT(id) as today_total FROM movies WHERE DATE(updated_at) = CURDATE()";
const newStr1 = "SELECT COUNT(id) as today_total FROM movies WHERE updated_at >= NOW() - INTERVAL 24 HOUR";

if (content.includes(targetStr1)) {
    content = content.replace(targetStr1, newStr1);
    fs.writeFileSync('server.js', content, 'utf8');
    console.log("Patched server.js with 24 HOUR interval");
} else {
    console.log("Target query not found in server.js");
}
