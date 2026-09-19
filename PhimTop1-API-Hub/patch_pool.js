const fs = require('fs');

let content = fs.readFileSync('server.js', 'utf8');

const targetStr = `queueLimit: 0\n});`;
const newLogic = `queueLimit: 0\n});\n\n// Tự động đảm bảo updated_at luôn được cập nhật (phòng trường hợp DB bị import lại)\npool.query("ALTER TABLE movies MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP").catch(e => { /* Bỏ qua lỗi nếu bảng chưa có hoặc đã cấu hình */ });\n`;

if (!content.includes('ALTER TABLE movies MODIFY updated_at')) {
    content = content.replace(targetStr, newLogic);
    fs.writeFileSync('server.js', content, 'utf8');
    console.log("Patched server.js with auto ALTER TABLE");
} else {
    console.log("Already patched");
}
