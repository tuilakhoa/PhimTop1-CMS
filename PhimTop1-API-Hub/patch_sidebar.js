const fs = require('fs');

const oldSidebarStart = '<aside class="w-64 border-r border-card bg-card flex flex-col">';
const sidebarEnd = '</aside>';

const newSidebarTemplate = `
    <aside class="w-64 border-r border-card bg-card flex flex-col shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-card">
            <h1 class="text-xl font-bold text-white tracking-tight">PhimTop1<span class="text-cyan-400">Admin</span></h1>
        </div>
        <nav class="flex-1 py-4 px-3 flex flex-col gap-1">
            <a href="/admin" class="!!DASHBOARD_CLASS!! px-3 py-2 rounded-lg transition font-medium">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Tổng quan
            </a>
            <a href="/admin/themes" class="!!THEMES_CLASS!! px-3 py-2 rounded-lg transition">
                <i data-lucide="palette" class="w-5 h-5"></i> Quản lý Themes
            </a>
            <a href="/admin/settings" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition">
                <i data-lucide="settings" class="w-5 h-5"></i> Cấu hình chung
            </a>
            <a href="/admin/account" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition">
                <i data-lucide="user" class="w-5 h-5"></i> Đổi mật khẩu
            </a>
        </nav>
        <div class="p-4 border-t border-card">
            <a href="/admin/logout" class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-400 hover:bg-red-500/10 transition">
                <i data-lucide="log-out" class="w-5 h-5"></i> Đăng xuất
            </a>
        </div>
    </aside>
`;

function patchSidebar(file, activeMenu) {
    let content = fs.readFileSync(file, 'utf-8');
    const startIdx = content.indexOf(oldSidebarStart);
    const endIdx = content.indexOf(sidebarEnd, startIdx) + sidebarEnd.length;
    
    if(startIdx !== -1) {
        let replacement = newSidebarTemplate;
        if(activeMenu === 'dashboard') {
            replacement = replacement.replace('!!DASHBOARD_CLASS!!', 'flex items-center gap-3 bg-cyan-500/10 text-cyan-400');
            replacement = replacement.replace('!!THEMES_CLASS!!', 'flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800');
        } else if (activeMenu === 'themes') {
            replacement = replacement.replace('!!DASHBOARD_CLASS!!', 'flex items-center gap-3 text-gray-400 hover:text-white hover:bg-gray-800');
            replacement = replacement.replace('!!THEMES_CLASS!!', 'flex items-center gap-3 bg-cyan-500/10 text-cyan-400');
        }
        
        content = content.substring(0, startIdx) + replacement + content.substring(endIdx);
        fs.writeFileSync(file, content);
    }
}

patchSidebar('views/admin/dashboard.ejs', 'dashboard');
patchSidebar('views/admin/themes.ejs', 'themes');

// Also, in themes.ejs, add "Sửa" button to the table
let themesEjs = fs.readFileSync('views/admin/themes.ejs', 'utf-8');
themesEjs = themesEjs.replace('<button type="submit" class="text-red-400 hover:text-red-300 transition">Xóa</button>',
'<a href="/admin/themes/<%= theme.id %>/edit" class="text-cyan-400 hover:text-cyan-300 transition mr-3">Sửa</a>\n                                    <button type="submit" class="text-red-400 hover:text-red-300 transition">Xóa</button>');
fs.writeFileSync('views/admin/themes.ejs', themesEjs);

console.log("Patched sidebars");
