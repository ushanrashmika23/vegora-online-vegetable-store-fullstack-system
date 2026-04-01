<?php
// controllers/productController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

$productModel = new Product($pdo);
$categoryModel = new Category($pdo);

/**
 * Fetch all available products from the database for the shop grid.
 * @return array
 */
function getAllProducts() {
    global $productModel;
    return $productModel->getAll();
}

/**
 * ADMIN ACTIONS (Add, Edit, Delete)
 */
$action = $_GET['action'] ?? '';

// Enforce admin permission for modifying data
if ($action !== '' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $discountedPriceRaw = trim((string)($_POST['discounted_price'] ?? ''));
        $discountedPrice = $discountedPriceRaw === '' ? null : (float)$discountedPriceRaw;
        $stock = (int)($_POST['stock'] ?? 0);
        $stockLimit = (int)($_POST['stock_limit'] ?? 20);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $id = $_POST['id'] ?? null;

        // Image upload handling
        $image = $_POST['existing_image'] ?? 'https://via.placeholder.com/400?text=No+Image'; // default
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/images/';
            
            // Create folder if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate clean unique filename
            $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('prod_') . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Determine the public relative path
                $image = 'assets/images/' . $fileName;
            }
        }

        // Basic validation
        if (empty($name) || $price <= 0 || $categoryId <= 0) {
            $_SESSION['admin_error'] = "Name, valid price, and category are required.";
            $redirect = $action === 'edit' ? "../admin/edit_product.php?id=$id" : "../admin/add_product.php";
            header("Location: $redirect");
            exit;
        }

        if (!$categoryModel->findById($categoryId)) {
            $_SESSION['admin_error'] = "Selected category is invalid.";
            $redirect = $action === 'edit' ? "../admin/edit_product.php?id=$id" : "../admin/add_product.php";
            header("Location: $redirect");
            exit;
        }

        if ($stockLimit < 0) {
            $_SESSION['admin_error'] = "Stock limit cannot be negative.";
            $redirect = $action === 'edit' ? "../admin/edit_product.php?id=$id" : "../admin/add_product.php";
            header("Location: $redirect");
            exit;
        }

        if ($discountedPrice !== null) {
            if ($discountedPrice <= 0) {
                $_SESSION['admin_error'] = "Discounted price must be greater than 0, or leave it empty.";
                $redirect = $action === 'edit' ? "../admin/edit_product.php?id=$id" : "../admin/add_product.php";
                header("Location: $redirect");
                exit;
            }
            if ($discountedPrice >= $price) {
                $_SESSION['admin_error'] = "Discounted price must be lower than the actual price.";
                $redirect = $action === 'edit' ? "../admin/edit_product.php?id=$id" : "../admin/add_product.php";
                header("Location: $redirect");
                exit;
            }
        }

        // Database commit
        if ($action === 'add') {
            if ($productModel->add($name, $price, $discountedPrice, $image, $stock, $stockLimit, $categoryId, $description)) {
                $_SESSION['admin_success'] = "Product successfully created!";
            } else {
                $_SESSION['admin_error'] = "Database error while adding product.";
            }
        } else {
            if ($productModel->update($id, $name, $price, $discountedPrice, $image, $stock, $stockLimit, $categoryId, $description)) {
                $_SESSION['admin_success'] = "Product updated successfully!";
            } else {
                $_SESSION['admin_error'] = "Failed to update product.";
            }
        }
        
        header('Location: ../admin/products.php');
        exit;
    }

    if ($action === 'restock') {
        $id = (int)($_POST['id'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);

        if ($id <= 0 || $amount <= 0) {
            $_SESSION['admin_error'] = 'Invalid restock request.';
            header('Location: ../admin/products.php');
            exit;
        }

        if ($productModel->increaseStock($id, $amount)) {
            $_SESSION['admin_success'] = "Stock updated: +{$amount} units added.";
        } else {
            $_SESSION['admin_error'] = 'Could not update product stock.';
        }

        header('Location: ../admin/products.php');
        exit;
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0 && $productModel->delete($id)) {
        $_SESSION['admin_success'] = "Product has been deleted.";
    } else {
        $_SESSION['admin_error'] = "Could not delete product.";
    }
    header('Location: ../admin/products.php');
    exit;
}
?>
