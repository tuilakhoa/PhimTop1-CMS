import os

file_path = 'includes/repositories.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

repos = [
    'MovieRepository',
    'CategoryRepository',
    'CommentRepository',
    'SeoRepository'
]

for repo in repos:
    func_name = f"get{repo}()"
    old_func = f"function {func_name} {{\n    return new {repo}(getPDO(), getFirestoreInstance());\n}}"
    new_func = f"function {func_name} {{\n    static $instance = null;\n    if ($instance === null) {{\n        $instance = new {repo}(getPDO(), getFirestoreInstance());\n    }}\n    return $instance;\n}}"
    content = content.replace(old_func, new_func)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched repositories.php")
