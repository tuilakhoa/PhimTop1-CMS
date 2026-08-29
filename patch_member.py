import re

with open('themes/phimhayok/member.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the tabs HTML
old_tabs_html = """<div class="relative flex p-1.5 bg-black/40 backdrop-blur-md rounded-full mb-8 border border-white/10 shadow-inner">
                        <div id="tab-indicator" class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] bg-gradient-to-r from-phim-yellow to-yellow-400 rounded-full shadow-lg shadow-phim-yellow/20 transition-transform duration-300 ease-out <?= $mode === 'register' ? 'translate-x-full' : 'translate-x-0' ?>"></div>
                        
                        <button type="button" onclick="setMode('login')" id="tab-login" class="relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-gray-400 hover:text-white' : 'text-black' ?>">Đăng Nhập</button>
                        
                        <button type="button" onclick="setMode('register')" id="tab-register" class="relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-black' : 'text-gray-400 hover:text-white' ?>">Đăng Ký</button>
                    </div>"""

new_tabs_html = """<div class="flex gap-2 mb-8">
                        <button type="button" onclick="setMode('login')" id="tab-login" class="flex-1 py-3 text-sm font-bold rounded-xl transition-colors duration-300 <?= $mode === 'register' ? 'bg-[#1a1a1a] text-gray-400 hover:text-white border border-white/10' : 'bg-gradient-to-r from-phim-yellow to-yellow-400 text-black shadow-lg shadow-phim-yellow/20' ?>">Đăng Nhập</button>
                        
                        <button type="button" onclick="setMode('register')" id="tab-register" class="flex-1 py-3 text-sm font-bold rounded-xl transition-colors duration-300 <?= $mode === 'register' ? 'bg-gradient-to-r from-phim-yellow to-yellow-400 text-black shadow-lg shadow-phim-yellow/20' : 'bg-[#1a1a1a] text-gray-400 hover:text-white border border-white/10' ?>">Đăng Ký</button>
                    </div>"""

content = content.replace(old_tabs_html, new_tabs_html)

# Replace JS logic
old_js_register = """var indicator = document.getElementById('tab-indicator');
                if(indicator) {
                    indicator.classList.remove('translate-x-0');
                    indicator.classList.add('translate-x-full');
                }
                var tabLogin = document.getElementById('tab-login');
                if(tabLogin) tabLogin.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-gray-400 hover:text-white';
                var tabRegister = document.getElementById('tab-register');
                if(tabRegister) tabRegister.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-black';"""

new_js_register = """var tabLogin = document.getElementById('tab-login');
                if(tabLogin) tabLogin.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-colors duration-300 bg-[#1a1a1a] text-gray-400 hover:text-white border border-white/10';
                var tabRegister = document.getElementById('tab-register');
                if(tabRegister) tabRegister.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-colors duration-300 bg-gradient-to-r from-phim-yellow to-yellow-400 text-black shadow-lg shadow-phim-yellow/20';"""

content = content.replace(old_js_register, new_js_register)

old_js_login = """var indicator = document.getElementById('tab-indicator');
                if(indicator) {
                    indicator.classList.remove('translate-x-full');
                    indicator.classList.add('translate-x-0');
                }
                var tabLogin = document.getElementById('tab-login');
                if(tabLogin) tabLogin.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-black';
                var tabRegister = document.getElementById('tab-register');
                if(tabRegister) tabRegister.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-gray-400 hover:text-white';"""

new_js_login = """var tabLogin = document.getElementById('tab-login');
                if(tabLogin) tabLogin.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-colors duration-300 bg-gradient-to-r from-phim-yellow to-yellow-400 text-black shadow-lg shadow-phim-yellow/20';
                var tabRegister = document.getElementById('tab-register');
                if(tabRegister) tabRegister.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-colors duration-300 bg-[#1a1a1a] text-gray-400 hover:text-white border border-white/10';"""

content = content.replace(old_js_login, new_js_login)

with open('themes/phimhayok/member.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched member.php")
