<?php

class CommentRepository {
    private $pdo;
    private $fs;

    public function __construct($pdo, $fs) {
        $this->pdo = $pdo;
        $this->fs = $fs;
    }

    public function isFirestore() {
        return $this->fs !== null;
    }

    public function getCommentsByMovie($movieSlug, $onlyApproved = true) {
        if ($this->isFirestore()) {
            $all = $this->fs->getAllDocuments('comments');
            $filtered = [];
            foreach ($all as $c) {
                if (($c['movie_slug'] ?? '') === $movieSlug) {
                    if (!$onlyApproved || ($c['status'] ?? 'approved') === 'approved') {
                        $filtered[] = $c;
                    }
                }
            }
            usort($filtered, function($a, $b) {
                return strtotime($b['created_at'] ?? '0') <=> strtotime($a['created_at'] ?? '0');
            });
            return $filtered;
        } else {
            $sql = "SELECT * FROM comments WHERE movie_slug = ?";
            if ($onlyApproved) {
                $sql .= " AND status = 'approved'";
            }
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$movieSlug]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getAllComments($page, $limit, $search = '') {
        $offset = ($page - 1) * $limit;
        
        if ($this->isFirestore()) {
            $all = $this->fs->getAllDocuments('comments');
            if ($search) {
                $search = mb_strtolower($search);
                $all = array_filter($all, function($c) use ($search) {
                    return str_contains(mb_strtolower($c['content'] ?? ''), $search) || 
                           str_contains(mb_strtolower($c['user_name'] ?? ''), $search) ||
                           str_contains(mb_strtolower($c['movie_slug'] ?? ''), $search);
                });
            }
            usort($all, function($a, $b) {
                return strtotime($b['created_at'] ?? '0') <=> strtotime($a['created_at'] ?? '0');
            });
            
            $total = count($all);
            $items = array_slice($all, $offset, $limit);
            
            return [
                'total' => $total,
                'items' => $items,
                'totalPages' => ceil($total / $limit)
            ];
        } else {
            $where = "1=1";
            $params = [];
            if ($search) {
                $where .= " AND (content LIKE ? OR user_name LIKE ? OR movie_slug LIKE ?)";
                $searchLike = "%{$search}%";
                $params = [$searchLike, $searchLike, $searchLike];
            }
            
            $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM comments WHERE $where");
            $stmtTotal->execute($params);
            $total = $stmtTotal->fetchColumn();
            
            $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $total,
                'items' => $items,
                'totalPages' => ceil($total / $limit)
            ];
        }
    }

    public function addComment($movieSlug, $userName, $content, $status = 'approved') {
        if ($this->isFirestore()) {
            $id = uniqid();
            $data = [
                'id' => $id,
                'movie_slug' => $movieSlug,
                'user_name' => $userName,
                'content' => $content,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->fs->setDocument('comments', $id, $data);
            return $id;
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO comments (movie_slug, user_name, content, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$movieSlug, $userName, $content, $status]);
            return $this->pdo->lastInsertId();
        }
    }

    public function updateStatus($id, $status) {
        if ($this->isFirestore()) {
            $doc = $this->fs->getDocument('comments', $id);
            if ($doc) {
                $doc['status'] = $status;
                return $this->fs->setDocument('comments', $id, $doc);
            }
            return false;
        } else {
            $stmt = $this->pdo->prepare("UPDATE comments SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            return $stmt->rowCount() > 0;
        }
    }

    public function deleteComment($id) {
        if ($this->isFirestore()) {
            return $this->fs->deleteDocument('comments', $id);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        }
    }
}
