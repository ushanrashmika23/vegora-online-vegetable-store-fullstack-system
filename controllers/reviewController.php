<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../shop.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['review_error'] = 'Please login to submit a review.';
    header('Location: ../login.php');
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$reviewText = trim((string)($_POST['review_text'] ?? ''));

if ($productId <= 0) {
    $_SESSION['review_error'] = 'Invalid product selected for review.';
    header('Location: ../shop.php');
    exit;
}

if ($rating < 1 || $rating > 5) {
    $_SESSION['review_error'] = 'Rating must be between 1 and 5 stars.';
    header('Location: ../product.php?id=' . $productId);
    exit;
}

if (strlen($reviewText) > 1200) {
    $_SESSION['review_error'] = 'Review is too long. Please keep it under 1200 characters.';
    header('Location: ../product.php?id=' . $productId);
    exit;
}

$productModel = new Product($pdo);
$product = $productModel->findById($productId);
if (!$product) {
    $_SESSION['review_error'] = 'Product not found.';
    header('Location: ../shop.php');
    exit;
}

$success = $productModel->saveReview($productId, (int)$_SESSION['user_id'], $rating, $reviewText === '' ? null : $reviewText);

if ($success) {
    $_SESSION['review_success'] = 'Your review has been saved successfully.';
} else {
    $_SESSION['review_error'] = 'Could not save your review. Please try again.';
}

header('Location: ../product.php?id=' . $productId);
exit;
