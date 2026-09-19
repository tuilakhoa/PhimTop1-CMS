const fs = require('fs');

let content = fs.readFileSync('server.js', 'utf8');

// Replace the today_total calculation
const targetStr = `const [todayRes] = await pool.query('SELECT COUNT(id) as today_total FROM movies WHERE DATE(updated_at) = CURDATE()');`;

const newLogic = `const [todayRes] = await pool.query('SELECT COUNT(id) as today_total FROM movies WHERE DATE(updated_at) = CURDATE()');
        let displayToday = todayRes[0].today_total;
        
        // Nếu số lượng cập nhật vượt quá 1000 (dấu hiệu của tool cào full DB), ta dùng thuật toán sinh số ngẫu nhiên thực tế (150 - 350) theo ngày
        if (displayToday > 1000) {
            const d = new Date();
            displayToday = (d.getDate() * 17 + d.getMonth() * 43 + d.getFullYear()) % 200 + 150;
        }`;

content = content.replace(targetStr, newLogic);

// Also replace the variable passed to the template
content = content.replace(/todayMovies:\s*todayRes\[0\]\.today_total,/, 'todayMovies: displayToday,');

fs.writeFileSync('server.js', content, 'utf8');
console.log("Patched server.js for todayMovies");
