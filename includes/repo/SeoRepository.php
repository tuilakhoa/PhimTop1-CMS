<?php

class SeoRepository {
    private $pdo;
    private $fs;

    public function __construct($pdo, $fs) {
        $this->pdo = $pdo;
        $this->fs = $fs;
    }

    public function isFirestore() {
        return $this->fs !== null;
    }

    public function getAllSeoMetadata() {
        if ($this->isFirestore()) {
            return $this->fs->getAllDocuments('seo_metadata');
        } else {
            $stmt = $this->pdo->query("SELECT * FROM seo_metadata");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getSeoMetadata($type, $itemId) {
        if ($this->isFirestore()) {
            $all = $this->getAllSeoMetadata();
            foreach ($all as $item) {
                if ($item['type'] === $type && $item['item_id'] === $itemId) {
                    return $item;
                }
            }
            return null;
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM seo_metadata WHERE type = ? AND item_id = ? LIMIT 1");
            $stmt->execute([$type, $itemId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function saveSeoMetadata($data) {
        if ($this->isFirestore()) {
            // Use provided id or generate a new one based on type and item_id
            $id = $data['id'] ?? md5($data['type'] . '_' . $data['item_id']);
            $data['id'] = $id;
            return $this->fs->setDocument('seo_metadata', $id, $data);
        } else {
            if (!empty($data['id'])) {
                $stmt = $this->pdo->prepare("UPDATE seo_metadata SET type=?, item_id=?, custom_slug=?, seo_title=?, seo_desc=?, seo_keywords=? WHERE id=?");
                $stmt->execute([
                    $data['type'], $data['item_id'], $data['custom_slug'] ?? null, 
                    $data['seo_title'] ?? '', $data['seo_desc'] ?? '', $data['seo_keywords'] ?? '', 
                    $data['id']
                ]);
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO seo_metadata (type, item_id, custom_slug, seo_title, seo_desc, seo_keywords) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['type'], $data['item_id'], $data['custom_slug'] ?? null,
                    $data['seo_title'] ?? '', $data['seo_desc'] ?? '', $data['seo_keywords'] ?? ''
                ]);
            }
            return $stmt->rowCount() > 0;
        }
    }

    public function resolveCustomSlug($type, $currentSlug) {
        if ($this->isFirestore()) {
            $all = $this->getAllSeoMetadata();
            foreach ($all as $item) {
                if ($item['type'] === $type && $item['custom_slug'] === $currentSlug) {
                    return $item['item_id'];
                }
            }
            return $currentSlug;
        } else {
            $stmt = $this->pdo->prepare("SELECT item_id FROM seo_metadata WHERE type = ? AND custom_slug = ? LIMIT 1");
            $stmt->execute([$type, $currentSlug]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['item_id'] : $currentSlug;
        }
    }

    public function deleteSeoMetadata($id) {
        if ($this->isFirestore()) {
            return $this->fs->deleteDocument('seo_metadata', $id);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM seo_metadata WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        }
    }
}
