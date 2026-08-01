import os
import re

files_to_refactor = {
    'index.php': 23,
    'movie.php': 57,
    'watch.php': 92,
    'category.php': 53,
    'search.php': 36
}

for filename, split_line in files_to_refactor.items():
    with open(filename, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    # Logic part
    logic_part = lines[:split_line]
    # Remove include header from logic part if it's there
    logic_part = [line for line in logic_part if 'includes/header.php' not in line]
    
    # Append theme router to logic part
    logic_part.append("\n$theme = $settings['theme'] ?? 'dark';\n")
    logic_part.append("$themeFile = __DIR__ . \"/themes/{$theme}/\" . basename(__FILE__);\n")
    logic_part.append("if (file_exists($themeFile)) {\n    require $themeFile;\n} else {\n    require __DIR__ . \"/themes/dark/\" . basename(__FILE__);\n}\n")
    
    with open(filename, 'w', encoding='utf-8') as f:
        f.writelines(logic_part)
        
    # View part
    view_part = lines[split_line:]
    # Replace includes/header.php and includes/footer.php with local header.php and footer.php
    view_content = "".join(view_part)
    view_content = view_content.replace("include __DIR__ . '/includes/header.php';", "include __DIR__ . '/header.php';")
    view_content = view_content.replace("include __DIR__ . '/includes/footer.php';", "include __DIR__ . '/footer.php';")
    view_content = view_content.replace("require __DIR__ . '/includes/header.php';", "require __DIR__ . '/header.php';")
    view_content = view_content.replace("require __DIR__ . '/includes/footer.php';", "require __DIR__ . '/footer.php';")
    view_content = view_content.replace("<?php\n\ninclude __DIR__ . '/header.php';", "<?php include __DIR__ . '/header.php';")
    view_content = view_content.replace("<?php\ninclude __DIR__ . '/header.php';", "<?php include __DIR__ . '/header.php';")

    if not view_content.strip().startswith('<?php') and 'include __DIR__ . \'/header.php\';' not in view_content:
        view_content = "<?php include __DIR__ . '/header.php'; ?>\n" + view_content

    with open(f"themes/dark/{filename}", 'w', encoding='utf-8') as f:
        f.write(view_content)

# Move header and footer
os.rename('includes/header.php', 'themes/dark/header.php')
os.rename('includes/footer.php', 'themes/dark/footer.php')

print("Refactoring complete.")
