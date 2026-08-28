import os

file_path = 'includes/repo/MovieRepository.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

old_func = """    public function getBlockedSlugs() {
        if (!$this->pdo) return [];
        try {
            $stmt = $this->pdo->query("SELECT slug FROM blocked_movies");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        } catch (PDOException $e) {
            return [];
        }
    }"""

new_func = """    public function getBlockedSlugs() {
        static $cached = null;
        if ($cached !== null) return $cached;
        if (!$this->pdo) return [];
        try {
            $stmt = $this->pdo->query("SELECT slug FROM blocked_movies");
            $cached = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
            return $cached;
        } catch (PDOException $e) {
            return [];
        }
    }"""

content = content.replace(old_func, new_func)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patched getBlockedSlugs")
