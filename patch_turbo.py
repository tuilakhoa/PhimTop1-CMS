import os

file_path = 'themes/phimhayok/header.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

turbo_script = """
    <!-- Turbo for SPA feel -->
    <script type="module" src="https://cdn.skypack.dev/@hotwired/turbo"></script>
    <script>
        document.addEventListener("turbo:load", function() {
            // Re-trigger DOMContentLoaded for existing scripts to work
            window.document.dispatchEvent(new Event("DOMContentLoaded", {
              bubbles: true,
              cancelable: true
            }));
            // Re-init lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
"""

# Insert before </head>
content = content.replace("</head>", turbo_script + "\n</head>")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched header.php with Turbo")
