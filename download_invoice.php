<?php
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Order.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    http_response_code(400);
    echo 'Invalid order id.';
    exit;
}

$orderModel = new Order($pdo);
$order = $orderModel->getOrderById($orderId);
if (!$order) {
    http_response_code(404);
    echo 'Order not found.';
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isOwner = (int)$order['user_id'] === (int)$_SESSION['user_id'];
if (!$isAdmin && !$isOwner) {
    http_response_code(403);
    echo 'You are not allowed to download this invoice.';
    exit;
}

$filePath = __DIR__ . '/assets/invoices/invoice_' . $orderId . '.pdf';
if (!is_file($filePath)) {
    http_response_code(404);
    echo 'Invoice not generated yet for this order.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="invoice_' . $orderId . '.pdf"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
