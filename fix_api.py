import re
with open('includes/api_client.php', 'r') as f:
    content = f.read()

old_logic = """    } else if ($type === 'the-loai' && $slug) {
        $where[] = "m.categories_json LIKE ?";
        $params[] = '%"slug":"' . $slug . '"%';
    } else if ($type === 'quoc-gia' && $slug) {"""

new_logic = """    } else if ($type === 'the-loai' && $slug) {
        $where[] = "(m.categories_json LIKE ? OR (m.categories_json LIKE ? AND m.categories_json NOT LIKE '%\"slug\"%'))";
        $params[] = '%"slug":"' . $slug . '"%';
        // Also support NguonC format which only has name without slug
        // Determine the category name from slug roughly
        $pdo2 = getPDO();
        $stmtCat = $pdo2->prepare("SELECT name FROM categories WHERE slug = ? LIMIT 1");
        $stmtCat->execute([$slug]);
        $catName = $stmtCat->fetchColumn();
        if ($catName) {
            $params[] = '%"name":"' . $catName . '"%';
        } else {
            // Fallback
            $params[] = '%"name":"' . str_replace('-', ' ', $slug) . '"%';
        }
    } else if ($type === 'quoc-gia' && $slug) {"""

if old_logic in content:
    content = content.replace(old_logic, new_logic)
    with open('includes/api_client.php', 'w') as f:
        f.write(content)
    print("Updated api_client.php")
else:
    print("Logic not found in api_client.php")
