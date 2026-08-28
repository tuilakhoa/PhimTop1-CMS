import os
import re

file_path = 'includes/db.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_func = """function getSettings() {
    $pdo = getPDO();"""
new_func = """function getSettings() {
    static $cachedSettings = null;
    if ($cachedSettings !== null) {
        return $cachedSettings;
    }
    
    $pdo = getPDO();"""

old_return = """return array_merge($defaultSettings, $row);"""
new_return = """$cachedSettings = array_merge($defaultSettings, $row);
            return $cachedSettings;"""

old_default = """return $defaultSettings;
}

function updateSettings"""
new_default = """$cachedSettings = $defaultSettings;
    return $defaultSettings;
}

function updateSettings"""

content = content.replace(old_func, new_func)
content = content.replace(old_return, new_return)
content = content.replace(old_default, new_default)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched getSettings()")
