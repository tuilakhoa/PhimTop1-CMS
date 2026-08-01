<?php
namespace App\Controllers;

use App\Models\Release;

class AdminController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login() {
        if ($this->isLoggedIn()) {
            header("Location: /admin/dashboard");
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $stmt = $this->pdo->prepare("SELECT * FROM `admin` WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                header("Location: /admin/dashboard");
                exit;
            } else {
                $error = 'Sai tên đăng nhập hoặc mật khẩu.';
            }
        }

        require __DIR__ . '/../Views/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: /admin");
        exit;
    }

    public function dashboard() {
        $this->requireLogin();
        
        $releaseModel = new Release($this->pdo);
        $releases = $releaseModel->getAll();
        
        require __DIR__ . '/../Views/dashboard.php';
    }

    public function createRelease() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'version' => $_POST['version'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'changelog' => $_POST['changelog'],
                'is_force' => isset($_POST['is_force']) ? 1 : 0,
                'status' => $_POST['status'],
                'release_date' => $_POST['release_date']
            ];

            $data['download_url'] = $_POST['download_url'] ?? '';

            $releaseModel = new Release($this->pdo);
            $releaseModel->create($data);
            
            header("Location: /admin/dashboard?msg=created");
            exit;
        }
    }

    public function deleteRelease() {
        $this->requireLogin();
        
        if (isset($_GET['id'])) {
            $releaseModel = new Release($this->pdo);
            $releaseModel->delete($_GET['id']);
        }
        
        header("Location: /admin/dashboard?msg=deleted");
        exit;
    }

    private function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    private function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: /admin");
            exit;
        }
    }
}
