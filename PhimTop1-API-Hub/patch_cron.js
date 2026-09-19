const fs = require('fs');

let content = fs.readFileSync('server.js', 'utf8');

// Find the cron schedule
const targetCron = `cron.schedule('*/10 * * * *', async () => {
    console.log('[Crawler] Đang quét phim mới từ KKPhim, Nguồn C, VsMov...');
    // TODO: Chuyển logic cào từ PhimTop1-CMS sang đây
});`;

const newCron = `cron.schedule('*/5 * * * *', async () => {
    try {
        // Tự động enforce trigger để đảm bảo updated_at luôn tự động cập nhật khi crawler sửa data
        await pool.query("ALTER TABLE movies MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    } catch(e) {}
});`;

if (content.includes(targetCron)) {
    content = content.replace(targetCron, newCron);
    fs.writeFileSync('server.js', content, 'utf8');
    console.log("Patched server.js with cron DB trigger enforcer");
} else {
    console.log("Cron not found or already patched");
}
