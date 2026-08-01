import os
import glob

f_path = 'themes/netflix/index.php'
with open(f_path, 'r', encoding='utf-8') as file:
    content = file.read()

# Make hero full screen
content = content.replace('h-[50vh] md:h-[60vh] rounded-2xl', 'h-[80vh] rounded-none')
content = content.replace('px-4', 'px-12') # wider container
content = content.replace('container mx-auto px-4', 'w-full px-4 md:px-12')

with open(f_path, 'w', encoding='utf-8') as file:
    file.write(content)

print("Netflix theme tweaked.")
