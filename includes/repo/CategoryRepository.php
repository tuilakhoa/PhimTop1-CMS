<?php

class CategoryRepository {
    private $pdo;
    private $fs;

    public function __construct($pdo, $fs) {
        $this->pdo = $pdo;
        $this->fs = $fs;
    }

    public function isFirestore() {
        return $this->fs !== null;
    }

    public function getCategories() {
        if ($this->isFirestore()) {
            return $this->fs->getAllDocuments('categories');
        } else {
            $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function saveCategory($slug, $name, $type) {
        if ($this->isFirestore()) {
            return $this->fs->setDocument('categories', $slug, [
                'slug' => $slug,
                'name' => $name,
                'type' => $type,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO categories (slug, name, type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name)");
            $stmt->execute([$slug, $name, $type]);
            return $stmt->rowCount() > 0;
        }
    }
}
