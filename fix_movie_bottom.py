import re

file_path = 'themes/dark/movie.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace background and accent colors in the bottom section
content = content.replace('<div class="bg-[#111319] min-h-screen text-gray-200 font-sans pb-20 pt-8">', '<div class="bg-black min-h-screen text-gray-300 font-sans pb-20 pt-8">')
content = content.replace('text-[#ff8f00]', 'text-red-500')
content = content.replace('bg-[#ff8f00]', 'bg-red-600')
content = content.replace('hover:text-[#ff8f00]', 'hover:text-red-400')
content = content.replace('hover:bg-[#ff8f00]', 'hover:bg-red-500')
content = content.replace('text-[#ffaa33]', 'text-red-400')
content = content.replace('bg-[#181a20]', 'bg-gray-900/50')
content = content.replace('bg-[#22242d]', 'bg-gray-800')
content = content.replace('border-[#2d2f36]', 'border-gray-800')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
