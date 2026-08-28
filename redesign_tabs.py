import re

def redesign_tabs_dark(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    old_tabs = r'<div class="flex p-1 bg-zinc-800/50 rounded-xl mb-8 relative border border-white/5">.*?</div>'
    new_tabs = """<div class="relative flex p-1.5 bg-zinc-900/80 backdrop-blur-md rounded-full mb-8 border border-zinc-700/50 shadow-inner">
                        <div id="tab-indicator" class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] bg-gradient-to-r from-red-600 to-red-500 rounded-full shadow-lg shadow-red-500/20 transition-transform duration-300 ease-out <?= $mode === 'register' ? 'translate-x-full' : 'translate-x-0' ?>"></div>
                        
                        <button type="button" onclick="setMode('login')" id="tab-login" class="relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-zinc-400 hover:text-white' : 'text-white' ?>">Đăng Nhập</button>
                        
                        <button type="button" onclick="setMode('register')" id="tab-register" class="relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-white' : 'text-zinc-400 hover:text-white' ?>">Đăng Ký</button>
                    </div>"""
    content = re.sub(old_tabs, new_tabs, content, flags=re.DOTALL)
    
    # Update JS
    content = content.replace("translate-x-[100%]", "translate-x-full")
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

def redesign_tabs_phimhayok(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    old_tabs = r'<div class="flex p-1 bg-\[\#1a1a1a\] rounded-xl mb-8 relative border border-white/5">.*?</div>'
    new_tabs = """<div class="relative flex p-1.5 bg-black/40 backdrop-blur-md rounded-full mb-8 border border-white/10 shadow-inner">
                        <div id="tab-indicator" class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] bg-gradient-to-r from-phim-yellow to-yellow-400 rounded-full shadow-lg shadow-phim-yellow/20 transition-transform duration-300 ease-out <?= $mode === 'register' ? 'translate-x-full' : 'translate-x-0' ?>"></div>
                        
                        <button type="button" onclick="setMode('login')" id="tab-login" class="relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-gray-400 hover:text-white' : 'text-black' ?>">Đăng Nhập</button>
                        
                        <button type="button" onclick="setMode('register')" id="tab-register" class="relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 <?= $mode === 'register' ? 'text-black' : 'text-gray-400 hover:text-white' ?>">Đăng Ký</button>
                    </div>"""
    content = re.sub(old_tabs, new_tabs, content, flags=re.DOTALL)
    
    # Update JS
    content = content.replace("translate-x-[100%]", "translate-x-full")
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

redesign_tabs_dark('themes/dark/member.php')
redesign_tabs_phimhayok('themes/phimhayok/member.php')
