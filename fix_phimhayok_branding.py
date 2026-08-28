import re

# Fix Footer
file_path = 'themes/phimhayok/footer.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace('PhimHayOK', '<?= htmlspecialchars($siteName ?? "PhimTop1") ?>')
# Re-fix the footerText fallback just in case
content = content.replace("<?= htmlspecialchars($siteName ?? \"PhimTop1\") ?> - Nền tảng xem phim", "Hệ thống xem phim")
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# Fix Header
file_path = 'themes/phimhayok/header.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("'PhimHayOK - Xem Phim Online'", "'PhimTop1 - Xem Phim Online'")
content = content.replace("'PhimHayOK'", "'PhimTop1'")
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Branding fixed")
