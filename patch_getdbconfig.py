import os

file_path = 'includes/db.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_func = """function getDbConfig() {
    global $dbConfigPath;
    clearstatcache(true, $dbConfigPath);
    if (file_exists($dbConfigPath)) {
        return json_decode(file_get_contents($dbConfigPath), true);
    }
    return null;
}"""

new_func = """function getDbConfig() {
    static $cachedConfig = null;
    if ($cachedConfig !== null) {
        return $cachedConfig;
    }
    global $dbConfigPath;
    if (file_exists($dbConfigPath)) {
        $cachedConfig = json_decode(file_get_contents($dbConfigPath), true);
        return $cachedConfig;
    }
    return null;
}"""

content = content.replace(old_func, new_func)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched getDbConfig()")
