import os

file_path = 'themes/phimhayok/header.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

turbo_css = """
    <style>
        .turbo-progress-bar {
            height: 3px;
            background-color: #fcc526;
        }
    </style>
"""

content = content.replace("</head>", turbo_css + "\n</head>")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched header.php with Turbo CSS")
