import os
import glob

files = glob.glob('themes/light/**/*.php', recursive=True)
for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    # Replace dark classes with light classes
    content = content.replace('bg-gray-900', 'bg-gray-50')
    content = content.replace('bg-gray-800', 'bg-white')
    content = content.replace('bg-gray-700', 'bg-gray-200')
    content = content.replace('text-white', 'text-gray-900')
    content = content.replace('text-gray-400', 'text-gray-600')
    content = content.replace('text-gray-300', 'text-gray-700')
    content = content.replace('border-gray-800', 'border-gray-200')
    content = content.replace('border-gray-700', 'border-gray-300')
    
    with open(f, 'w', encoding='utf-8') as file:
        file.write(content)
print("Light theme created.")
