import os

file_path = 'includes/db.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_pdo = """function getPDO() {
    $config = getDbConfig();"""
new_pdo = """function getPDO() {
    static $pdoInstance = null;
    if ($pdoInstance !== null) {
        return $pdoInstance;
    }
    
    $config = getDbConfig();"""

old_return_pdo = """        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;"""
new_return_pdo = """        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdoInstance = $pdo;
        return $pdoInstance;"""

content = content.replace(old_pdo, new_pdo)
content = content.replace(old_return_pdo, new_return_pdo)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched getPDO()")
