<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Category.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$categoryModel = new Category($pdo);
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $name = trim((string)($_POST['name'] ?? ''));

        if ($name === '') {
            $_SESSION['admin_error'] = 'Category name is required.';
        } elseif ($categoryModel->findByName($name)) {
            $_SESSION['admin_error'] = 'Category already exists.';
        } elseif ($categoryModel->create($name)) {
            $_SESSION['admin_success'] = 'Category created successfully.';
        } else {
            $_SESSION['admin_error'] = 'Failed to create category.';
        }

        header('Location: ../admin/categories.php');
        exit;
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));

        if ($id <= 0 || $name === '') {
            $_SESSION['admin_error'] = 'Invalid category data.';
        } else {
            $existing = $categoryModel->findByName($name);
            if ($existing && (int)$existing['id'] !== $id) {
                $_SESSION['admin_error'] = 'Another category already uses this name.';
            } elseif ($categoryModel->update($id, $name)) {
                $_SESSION['admin_success'] = 'Category updated successfully.';
            } else {
                $_SESSION['admin_error'] = 'Failed to update category.';
            }
        }

        header('Location: ../admin/categories.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        $_SESSION['admin_error'] = 'Invalid category.';
    } elseif ($categoryModel->getProductCount($id) > 0) {
        $_SESSION['admin_error'] = 'Cannot delete category because products are assigned to it.';
    } elseif ($categoryModel->delete($id)) {
        $_SESSION['admin_success'] = 'Category deleted successfully.';
    } else {
        $_SESSION['admin_error'] = 'Failed to delete category.';
    }

    header('Location: ../admin/categories.php');
    exit;
}

header('Location: ../admin/categories.php');
exit;
