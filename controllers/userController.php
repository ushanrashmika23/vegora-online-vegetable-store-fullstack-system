<?php
// controllers/userController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    $userId = intval($_GET['id'] ?? 0);
    $userModel = new User($pdo);

    if ($userId > 0 && $userModel->deleteUser($userId)) {
        $_SESSION['admin_success'] = "User account successfully deleted.";
    } else {
        $_SESSION['admin_error'] = "Could not delete user. Note: You cannot delete admin accounts.";
    }
    
    header('Location: ../admin/users.php');
    exit;
} else {
    header('Location: ../admin/users.php');
    exit;
}
?>
