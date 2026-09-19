<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();
$pdo->exec("CREATE TABLE IF NOT EXISTS actor_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_name VARCHAR(255) NOT NULL,
    evidence_text TEXT NOT NULL,
    evidence_url VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reported_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "Table created";
