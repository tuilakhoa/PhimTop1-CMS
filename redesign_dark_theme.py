import os
import re

def update_header():
    file_path = 'themes/dark/header.php'
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Update theme classes to be pure black
    content = content.replace("'dark' => 'bg-gray-950 text-gray-100'", "'dark' => 'bg-black text-gray-200 selection:bg-red-600 selection:text-white'")
    
    # Change nav class to use backdrop blur on scroll
    content = content.replace('<nav class="glass-nav fixed w-full top-0 z-50">', '<nav id="mainNav" class="fixed w-full top-0 z-50 transition-all duration-500 bg-gradient-to-b from-black/80 to-transparent">')
    
    # Add JS script for scroll effect to nav
    script = """
    <script>
    document.addEventListener('scroll', () => {
        const nav = document.getElementById('mainNav');
        if (window.scrollY > 50) {
            nav.classList.remove('bg-gradient-to-b', 'from-black/80', 'to-transparent');
            nav.classList.add('bg-black/70', 'backdrop-blur-md', 'shadow-lg');
        } else {
            nav.classList.add('bg-gradient-to-b', 'from-black/80', 'to-transparent');
            nav.classList.remove('bg-black/70', 'backdrop-blur-md', 'shadow-lg');
        }
    });
    </script>
    <div class="pt-0 pb-12">
    """
    content = content.replace('<div class="pt-20 pb-12">', script)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Header updated.")

def update_index():
    file_path = 'themes/dark/index.php'
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Redesign Hero Banner
    old_hero = """<!-- Featured Movies Carousel -->
<?php if (!empty($featuredMovies)): ?>
    <?php if ($featuredStyle === 'slider' && count($featuredMovies) > 1): ?>
"""
    # Just to be safe, I'll use regex to replace the entire hero section until the first <!-- Container -->
    # Let's inspect index.php structure first.
update_header()
