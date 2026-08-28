import os

file_path = 'includes/repositories.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_func = """function getFirestoreInstance() {
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'firestore') {
        require_once __DIR__ . '/firestore_helper.php';
        return new FirestoreClient($config['projectId'], $config['serviceAccount']);
    }
    return null;
}"""

new_func = """function getFirestoreInstance() {
    static $instance = null;
    if ($instance !== null) return $instance;
    
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'firestore') {
        require_once __DIR__ . '/firestore_helper.php';
        $instance = new FirestoreClient($config['projectId'], $config['serviceAccount']);
        return $instance;
    }
    return null;
}"""

content = content.replace(old_func, new_func)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched getFirestoreInstance")
