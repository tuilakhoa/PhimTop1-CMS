import re
with open('includes/repo/MovieRepository.php', 'r') as f:
    text = f.read()

# Remove view=VALUES(view)
text = text.replace("view=VALUES(view), ", "")

# Add incrementView function
increment_func = """
    public function incrementView($slug) {
        if ($this->isFirestore()) {
            $doc = $this->fs->getDocument('movies', $slug);
            if ($doc) {
                $doc['view'] = ($doc['view'] ?? 0) + 1;
                $this->fs->setDocument('movies', $slug, $doc);
            }
        } else {
            if (!$this->pdo) return;
            $stmt = $this->pdo->prepare("UPDATE movies SET view = view + 1 WHERE slug = ?");
            $stmt->execute([$slug]);
        }
    }
"""

# Insert before closing brace of the class
text = re.sub(r"\}\s*$", increment_func + "\n}", text)

with open('includes/repo/MovieRepository.php', 'w') as f:
    f.write(text)
