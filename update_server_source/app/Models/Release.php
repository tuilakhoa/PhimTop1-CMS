<?php
namespace App\Models;

class Release {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getLatestPublished() {
        $stmt = $this->pdo->query("SELECT * FROM `releases` WHERE status = 'published' ORDER BY release_date DESC, id DESC LIMIT 1");
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM `releases` ORDER BY release_date DESC, id DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM `releases` WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO `releases` (version, title, description, changelog, download_url, is_force, status, release_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['version'],
            $data['title'],
            $data['description'],
            $data['changelog'] ?? '',
            $data['download_url'] ?? '',
            $data['is_force'] ?? 0,
            $data['status'] ?? 'draft',
            $data['release_date']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE `releases` SET version = ?, title = ?, description = ?, changelog = ?, 
                download_url = ?, is_force = ?, status = ?, release_date = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['version'],
            $data['title'],
            $data['description'],
            $data['changelog'] ?? '',
            $data['download_url'] ?? '',
            $data['is_force'] ?? 0,
            $data['status'] ?? 'draft',
            $data['release_date'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM `releases` WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
