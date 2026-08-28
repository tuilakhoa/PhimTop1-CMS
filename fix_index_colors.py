import re

file_path = 'themes/dark/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace('text-[#ff8f00]', 'text-red-500')
content = content.replace('bg-[#ff8f00]', 'bg-red-600')
content = content.replace('hover:text-[#ff8f00]', 'hover:text-red-400')
content = content.replace('hover:bg-[#ff8f00]', 'hover:bg-red-500')
content = content.replace('text-[#ffaa33]', 'text-red-400')
content = content.replace('bg-[#111319]', 'bg-black')
content = content.replace('bg-[#181a20]', 'bg-gray-900/50')
content = content.replace('bg-[#22242d]', 'bg-gray-800')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
