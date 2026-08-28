import re

def strip_tailwind_config(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        
    # Replace CDN link
    content = content.replace('<script src="https://cdn.tailwindcss.com"></script>', '<link rel="stylesheet" href="/assets/css/style.min.css">')
    
    # Remove tailwind.config script block (lazy match any character, until </script>)
    content = re.sub(r'<script>\s*tailwind\.config = \{.*?</script>', '', content, flags=re.DOTALL)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

strip_tailwind_config('themes/phimhayok/member.php')
strip_tailwind_config('themes/phimhayok/header.php')
strip_tailwind_config('admin/includes/header.php')
