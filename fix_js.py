import re

def fix_js(file_path, theme):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Fix translate-x-[100%] to translate-x-full
    content = content.replace("translate-x-[100%]", "translate-x-full")
    
    if theme == "dark":
        # Fix register branch
        content = content.replace(
            "tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-zinc-400 hover:text-white'",
            "tabLogin.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-zinc-400 hover:text-white'"
        )
        content = content.replace(
            "tabRegister.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-white'",
            "tabRegister.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-white'"
        )
        # Fix login branch
        content = content.replace(
            "tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-white'",
            "tabLogin.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-white'"
        )
        content = content.replace(
            "tabRegister.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-zinc-400 hover:text-white'",
            "tabRegister.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-zinc-400 hover:text-white'"
        )
        
    elif theme == "phimhayok":
        # Fix register branch
        content = content.replace(
            "tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-gray-400 hover:text-white'",
            "tabLogin.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-gray-400 hover:text-white'"
        )
        content = content.replace(
            "tabRegister.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-black'",
            "tabRegister.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-black'"
        )
        # Fix login branch
        content = content.replace(
            "tabLogin.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-black'",
            "tabLogin.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-black'"
        )
        content = content.replace(
            "tabRegister.className = 'flex-1 py-2.5 text-sm font-bold rounded-lg transition-colors duration-300 z-10 text-gray-400 hover:text-white'",
            "tabRegister.className = 'relative flex-1 py-3 text-sm font-bold rounded-full transition-colors duration-300 z-10 text-gray-400 hover:text-white'"
        )

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

fix_js('themes/dark/member.php', 'dark')
fix_js('themes/phimhayok/member.php', 'phimhayok')
