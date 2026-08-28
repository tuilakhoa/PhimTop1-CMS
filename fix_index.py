import re

file_path = 'themes/dark/index.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update overall bg
content = content.replace('<div class="bg-[#000000] min-h-screen text-gray-200 font-sans pb-20">', '<div class="bg-black min-h-screen text-gray-200 font-sans pb-20">')

# 2. Hero height and gradients
content = content.replace('h-[60vh] md:h-[75vh]', 'h-[75vh] lg:h-[90vh]')
content = content.replace('bg-gradient-to-r from-black/90 via-black/40 to-transparent', 'bg-gradient-to-r from-black via-black/50 to-transparent')
content = content.replace('bg-gradient-to-t from-black/80 via-transparent to-transparent', 'bg-gradient-to-t from-black via-black/20 to-transparent')

# 3. Typography
content = content.replace('text-4xl md:text-5xl lg:text-7xl font-bold text-white mb-6 leading-[1.1] tracking-tight', 'text-5xl md:text-6xl lg:text-8xl font-black text-white mb-6 leading-[1.05] tracking-tighter drop-shadow-2xl')

# 4. Buttons
content = content.replace('bg-white text-black hover:bg-gray-200', 'bg-white text-black hover:bg-gray-200 hover:scale-105 transform duration-300 shadow-xl shadow-white/10')
content = content.replace('bg-white/10 hover:bg-white/20 text-white', 'bg-white/20 hover:bg-white/30 backdrop-blur-md text-white hover:scale-105 transform duration-300 border border-white/10')

# 5. Make sure the container for hero text is aligned
content = content.replace('px-6 md:px-16 lg:px-24 w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto w-full', 'w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto')

# 6. For single featured style
content = content.replace('class="absolute inset-0 z-20 flex flex-col justify-center px-4 md:px-12 lg:px-20 max-w-[1400px] mx-auto pt-20"', 'class="absolute inset-0 z-20 flex flex-col justify-center w-full px-4 md:px-8 lg:px-12 2xl:px-20 mx-auto pt-20"')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Hero section updated")
