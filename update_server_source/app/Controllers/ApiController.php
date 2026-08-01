<?php
namespace App\Controllers;

use App\Models\Release;

class ApiController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function checkUpdate() {
        header('Content-Type: application/json');
        
        $clientVersion = $_GET['v'] ?? '1.0.0';
        
        $releaseModel = new Release($this->pdo);
        $latestRelease = $releaseModel->getLatestPublished();

        if (!$latestRelease) {
            echo json_encode([
                'success' => true,
                'latest' => $clientVersion,
                'current' => $clientVersion,
                'hasUpdate' => false
            ]);
            return;
        }

        $latestVersion = $latestRelease['version'];
        $hasUpdate = version_compare($clientVersion, $latestVersion, '<');

        if ($hasUpdate) {
            echo json_encode([
                'success' => true,
                'latest' => $latestVersion,
                'current' => $clientVersion,
                'hasUpdate' => true,
                'force' => (bool)$latestRelease['is_force'],
                'title' => $latestRelease['title'],
                'description' => $latestRelease['description'],
                'releaseDate' => $latestRelease['release_date'],
                'changelog' => $latestRelease['changelog'],
                'download' => $latestRelease['download_url']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'latest' => $latestVersion,
                'current' => $clientVersion,
                'hasUpdate' => false
            ]);
        }
    }
}
