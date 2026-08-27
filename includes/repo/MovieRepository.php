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

            $where = "slug NOT IN (SELECT slug FROM blocked_movies)";
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
            
            try {
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
            } catch (PDOException $e) {
                // Tự động fix lỗi nếu table blocked_movies bị thiếu do lỗi migration
                if (strpos($e->getMessage(), 'blocked_movies') !== false || $e->getCode() == '42S02') {
                    try {
                        $this->pdo->exec("CREATE TABLE IF NOT EXISTS blocked_movies (
                            slug VARCHAR(255) PRIMARY KEY,
                            name VARCHAR(255) NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
                        
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
                    } catch (PDOException $ex) {
                        error_log("Database error in getMovies after table creation: " . $ex->getMessage());
                    }
                }
                
                error_log("Database error in getMovies: " . $e->getMessage());
                return [
                    'total' => 0,
                    'items' => [],
                    'totalPages' => 1
                ];
            }
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
            
            $sql = "INSERT INTO movies (id, name, origin_name, slug, thumb_url, poster_url, year, type, status, episode_current, quality, lang, chieu_rap, content, actor, director, view, updated_at)
                VALUES (:id, :name, :origin_name, :slug, :thumb_url, :poster_url, :year, :type, :status, :episode_current, :quality, :lang, :chieu_rap, :content, :actor, :director, :view, :updated_at)
                ON DUPLICATE KEY UPDATE 
                name=VALUES(name), origin_name=VALUES(origin_name), thumb_url=VALUES(thumb_url), poster_url=VALUES(poster_url), 
                year=VALUES(year), type=VALUES(type), status=VALUES(status), episode_current=VALUES(episode_current), 
                quality=VALUES(quality), lang=VALUES(lang), chieu_rap=VALUES(chieu_rap), content=VALUES(content), actor=VALUES(actor), director=VALUES(director), updated_at=VALUES(updated_at)";
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
                ':actor' => $data['actor'] ?? '',
                ':director' => $data['director'] ?? '',
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

    public function deleteAllMovies() {
        if ($this->isFirestore()) {
            $allMovies = $this->fs->getAllDocuments('movies');
            foreach ($allMovies as $m) {
                if (isset($m['slug'])) {
                    $this->fs->deleteDocument('movies', $m['slug']);
                }
            }
            return true;
        } else {
            if (!$this->pdo) return false;
            $stmt = $this->pdo->prepare("DELETE FROM movies");
            return $stmt->execute();
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

    public function blockMovie($slug, $name) {
        if (!$this->pdo) return false;
        try {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO blocked_movies (slug, name) VALUES (?, ?)");
            return $stmt->execute([$slug, $name]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function restoreMovie($slug) {
        if (!$this->pdo) return false;
        try {
            $stmt = $this->pdo->prepare("DELETE FROM blocked_movies WHERE slug = ?");
            return $stmt->execute([$slug]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getBlockedSlugs() {
        if (!$this->pdo) return [];
        try {
            $stmt = $this->pdo->query("SELECT slug FROM blocked_movies");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function isMovieBlocked($slug) {
        if (!$this->pdo) return false;
        try {
            $stmt = $this->pdo->prepare("SELECT 1 FROM blocked_movies WHERE slug = ?");
            $stmt->execute([$slug]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getBlockedMoviesList() {
        if (!$this->pdo) return [];
        try {
            $stmt = $this->pdo->query("SELECT * FROM blocked_movies ORDER BY created_at DESC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllCasts() {
        $casts = [];
        
        if ($this->isFirestore()) {
            $allMovies = $this->fs->getAllDocuments('movies');
            foreach ($allMovies as $row) {
                $this->extractCastsFromRow($row, $casts);
            }
        } else {
            if (!$this->pdo) return [];
            try {
                $stmt = $this->pdo->query("SELECT actor, director FROM movies WHERE actor != '' OR director != ''");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->extractCastsFromRow($row, $casts);
                }
            } catch (PDOException $e) {
                // Ignore
            }
        }
        
        return array_values($casts);
    }

    private function extractCastsFromRow($row, &$casts) {
        if (!empty($row['actor'])) {
            $actors = array_map('trim', explode(',', $row['actor']));
            foreach ($actors as $a) {
                if ($a === '' || strtolower($a) === 'đang cập nhật') continue;
                if (!isset($casts[$a])) {
                    $casts[$a] = ['name' => $a, 'role' => 'Diễn Viên', 'count' => 0];
                }
                $casts[$a]['count']++;
            }
        }
        if (!empty($row['director'])) {
            $directors = array_map('trim', explode(',', $row['director']));
            foreach ($directors as $d) {
                if ($d === '' || strtolower($d) === 'đang cập nhật') continue;
                if (!isset($casts[$d])) {
                    $casts[$d] = ['name' => $d, 'role' => 'Đạo Diễn', 'count' => 0];
                } else {
                    $casts[$d]['role'] = 'Đạo/Diễn Viên';
                }
                $casts[$d]['count']++;
            }
        }
    }

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

}