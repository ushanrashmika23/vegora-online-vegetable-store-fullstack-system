<?php
// controllers/cartController.php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Cart.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login to use the cart']);
    exit;
}

$userId = $_SESSION['user_id'];
$cartModel = new Cart($pdo);

// Read JSON input for AJAX requests
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($input['action'] ?? '');

try {
    switch ($action) {
        case 'add':
            $productId = intval($input['product_id'] ?? 0);
            $qty = intval($input['quantity'] ?? 1);
            
            if ($productId <= 0 || $qty <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid product']);
                exit;
            }

            $availableStock = $cartModel->getProductStock($productId);
            if ($availableStock === null) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }

            $alreadyInCart = $cartModel->getCartProductQuantity($userId, $productId);
            if (($alreadyInCart + $qty) > $availableStock) {
                $remaining = max($availableStock - $alreadyInCart, 0);
                if ($remaining === 0) {
                    echo json_encode(['success' => false, 'error' => 'You already reached the available stock for this item.']);
                } else {
                    echo json_encode(['success' => false, 'error' => "Only {$remaining} more item(s) available in stock."]);
                }
                exit;
            }

            if ($cartModel->add($userId, $productId, $qty)) {
                $totalCount = $cartModel->getCount($userId);
                echo json_encode(['success' => true, 'message' => 'Item added to cart', 'cart_count' => $totalCount]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to add item to cart']);
            }
            break;

        case 'update':
            $cartId = intval($input['cart_id'] ?? 0);
            $qty = intval($input['quantity'] ?? 0);
            
            if ($cartId <= 0 || $qty < 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid update parameters']);
                exit;
            }

            if ($qty > 0) {
                $cartItem = $cartModel->getCartItemWithStock($cartId, $userId);
                if (!$cartItem) {
                    echo json_encode(['success' => false, 'error' => 'Cart item not found']);
                    exit;
                }

                if ($qty > (int)$cartItem['stock']) {
                    echo json_encode(['success' => false, 'error' => "Only {$cartItem['stock']} item(s) available in stock."]);
                    exit;
                }
            }
            
            if ($cartModel->updateQuantity($cartId, $userId, $qty)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update quantity']);
            }
            break;

        case 'remove':
            $cartId = intval($input['cart_id'] ?? 0);
            
            if ($cartId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid item']);
                exit;
            }

            if ($cartModel->remove($cartId, $userId)) {
                $totalCount = $cartModel->getCount($userId);
                echo json_encode(['success' => true, 'cart_count' => $totalCount]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to remove item']);
            }
            break;

        case 'count':
            $totalCount = $cartModel->getCount($userId);
            echo json_encode(['success' => true, 'cart_count' => $totalCount]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
