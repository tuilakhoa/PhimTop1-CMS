<?php

class MovieRepository {
    private $pdo;
    private $fs;

    public function __construct($pdo, $fs) {
        $this->pdo = $pdo;
        $this->fs = $fs;
    }

    public function isFirestore() {
        return $this->fs !== null;
    }

    public function getMovies($page, $limit, $search = '', $type = '') {
        $offset = ($page - 1) * $limit;
        
        if ($this->isFirestore()) {
            $allMovies = $this->fs->getAllDocuments('movies');
            
            if ($type) {
                $allMovies = array_filter($allMovies, function($m) use ($type) {
                    return ($m['type'] ?? '') === $type;
                });
            }
            
            if ($search) {
                $search = mb_strtolower($search);
                $allMovies = array_filter($allMovies, function($m) use ($search) {
                    return str_contains(mb_strtolower($m['name'] ?? ''), $search) || 
                           str_contains(mb_strtolower($m['origin_name'] ?? ''), $search) || 
                           str_contains(mb_strtolower($m['slug'] ?? ''), $search);
                });
            }
            // Sort by updated_at desc
            usort($allMovies, function($a, $b) {
                $timeA = strtotime($a['updated_at'] ?? '0');
                $timeB = strtotime($b['updated_at'] ?? '0');
                return $timeB <=> $timeA;
            });
            
            $total = count($allMovies);
            $items = array_slice($allMovies, $offset, $limit);
            
            return [
                'total' => $total,
                'items' => $items,
                'totalPages' => ceil($total / $limit)
            ];
        } else {
            if (!$this->pdo) {
                return [
                    'total' => 0,
                    'items' => [],
                    'totalPages' => 1
                ];
            }

            $where = "1=1";
            $params = [];
            
            if ($type) {
                $where .= " AND type = ?";
                $params[] = $type;
            }
            
            if ($search) {
                $where .= " AND (name LIKE ? OR origin_name LIKE ? OR slug LIKE ?)";
                $searchLike = "%{$search}%";
                $params[] = $searchLike;
                $params[] = $searchLike;
                $params[] = $searchLike;
            }
            
            $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM movies WHERE $where");
            $stmtTotal->execute($params);
            $total = $stmtTotal->fetchColumn();
            
            $stmt = $this->pdo->prepare("SELECT * FROM movies WHERE $where ORDER BY updated_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $total,
                'items' => $items,
                'totalPages' => ceil($total / $limit)
            ];
        }
    }

    public function saveMovie($data) {
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        if ($this->isFirestore()) {
            return $this->fs->setDocument('movies', $data['slug'], $data);
        } else {
            if (!$this->pdo) return false;
            
            $sql = "INSERT INTO movies (id, name, origin_name, slug, thumb_url, poster_url, year, type, status, episode_current, quality, lang, chieu_rap, content, view, updated_at)
                VALUES (:id, :name, :origin_name, :slug, :thumb_url, :poster_url, :year, :type, :status, :episode_current, :quality, :lang, :chieu_rap, :content, :view, :updated_at)
                ON DUPLICATE KEY UPDATE 
                name=VALUES(name), origin_name=VALUES(origin_name), thumb_url=VALUES(thumb_url), poster_url=VALUES(poster_url), 
                year=VALUES(year), type=VALUES(type), status=VALUES(status), episode_current=VALUES(episode_current), 
                quality=VALUES(quality), lang=VALUES(lang), chieu_rap=VALUES(chieu_rap), content=VALUES(content), view=VALUES(view), updated_at=VALUES(updated_at)";
            $stmt = $this->pdo->prepare($sql);
            
            // Lọc các trường có trong SQL
            $params = [
                ':id' => $data['id'] ?? uniqid(),
                ':name' => $data['name'] ?? '',
                ':origin_name' => $data['origin_name'] ?? '',
                ':slug' => $data['slug'],
                ':thumb_url' => $data['thumb_url'] ?? '',
                ':poster_url' => $data['poster_url'] ?? '',
                ':year' => $data['year'] ?? 0,
                ':type' => $data['type'] ?? '',
                ':status' => $data['status'] ?? '',
                ':episode_current' => $data['episode_current'] ?? '',
                ':quality' => $data['quality'] ?? '',
                ':lang' => $data['lang'] ?? '',
                ':chieu_rap' => $data['chieu_rap'] ?? 0,
                ':content' => $data['content'] ?? '',
                ':view' => $data['view'] ?? 0,
                ':updated_at' => $data['updated_at']
            ];
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        }
    }

    public function deleteMovie($slug) {
        if ($this->isFirestore()) {
            return $this->fs->deleteDocument('movies', $slug);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM movies WHERE slug = ?");
            $stmt->execute([$slug]);
            return $stmt->rowCount() > 0;
        }
    }

    public function getMovieBySlug($slug) {
        if ($this->isFirestore()) {
            return $this->fs->getDocument('movies', $slug);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM movies WHERE slug = ?");
            $stmt->execute([$slug]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
