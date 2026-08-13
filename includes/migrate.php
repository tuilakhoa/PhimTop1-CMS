<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logger.php';

function runMigrations() {
    $pdo = getPDO();
    if (!$pdo) {
        Logger::error("Migration failed: Unable to connect to database");
        return;
    }

    // Check if it's firestore. Migrations are SQL only.
    $config = getDbConfig();
    if (isset($config['type']) && $config['type'] === 'firestore') {
        return;
    }

    try {
        // Create migrations table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $pdo->query("SELECT migration FROM migrations");
        $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $migrationFiles = glob(__DIR__ . '/../migrations/*.sql');
        sort($migrationFiles);

        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            if (!in_array($filename, $executedMigrations)) {
                $sql = file_get_contents($file);
                try {
                    $pdo->exec($sql);
                    $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                    $stmt->execute([$filename]);
                    Logger::info("Migrated: $filename");
                } catch (PDOException $e) {
                    Logger::error("Error executing migration $filename: " . $e->getMessage());
                }
            }
        }
    } catch (PDOException $e) {
        Logger::error("Migration process error: " . $e->getMessage());
    }
}
