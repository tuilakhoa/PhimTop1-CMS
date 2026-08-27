import re
with open('admin/ajax_email.php', 'r') as f:
    text = f.read()

text = re.sub(r"\} else \{\s*// Crawl Mode \(DB\)[\s\S]*?\}\s*if \(\!empty\(\$movies\)\) \{", "}\n\n    if (!empty($movies)) {", text)
text = text.replace("    if (true) {\n        require_once", "    require_once")

with open('admin/ajax_email.php', 'w') as f:
    f.write(text)
