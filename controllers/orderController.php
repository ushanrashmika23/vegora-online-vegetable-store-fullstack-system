<?php
// controllers/orderController.php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../services/InvoicePdfService.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'checkout') {
    $cartModel = new Cart($pdo);
    $orderModel = new Order($pdo);

    // Get all items in user's cart
    $cartItems = $cartModel->getItems($userId);

    if (empty($cartItems)) {
        $_SESSION['checkout_error'] = "Your cart is empty.";
        header('Location: ../cart.php');
        exit;
    }

    // Calculate total layout
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += ($item['effective_price'] * $item['quantity']);
    }

    $discount_amount = 0;
    $coupon_id = null;
    if (isset($_SESSION['coupon'])) {
        $coupon_code = $_SESSION['coupon']['code'];
        $stmtC = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $stmtC->execute([$coupon_code]);
        $c = $stmtC->fetch();
        if ($c) {
            $stmtU = $pdo->prepare("SELECT id FROM user_coupons WHERE user_id = ? AND coupon_id = ?");
            $stmtU->execute([$userId, $c['id']]);
            if (!$stmtU->fetch()) {
                $coupon_id = $c['id'];
                if ($c['discount_type'] === 'percent') {
                     $discount_amount = $subtotal * ($c['discount_value'] / 100);
                } else {
                     $discount_amount = $c['discount_value'];
                }
                if ($discount_amount > $subtotal) $discount_amount = $subtotal;
            }
        }
    }

    $shipping = $subtotal > 0 ? 5.00 : 0.00;
    $tax = (($subtotal - $discount_amount) > 0 ? ($subtotal - $discount_amount) : 0) * 0.10;
    $total = ($subtotal - $discount_amount) + $shipping + $tax;

    $shippingAddress = trim((string)($_POST['shipping_address'] ?? ''));
    $shippingCity = trim((string)($_POST['shipping_city'] ?? ''));
    $shippingZip = trim((string)($_POST['shipping_zip'] ?? ''));
    $shippingPhone = trim((string)($_POST['shipping_phone'] ?? ''));
    $shippingNotes = trim((string)($_POST['shipping_notes'] ?? ''));

    // Process order inside transaction (Order model handles making order items too)
    $orderId = $orderModel->checkout($userId, $cartItems, $total);

    if ($orderId) {
        // Log coupon usage
        if ($coupon_id) {
            $stmtUC = $pdo->prepare("INSERT INTO user_coupons (user_id, coupon_id, order_id) VALUES (?, ?, ?)");
            $stmtUC->execute([$userId, $coupon_id, $orderId]);
            unset($_SESSION['coupon']);
        }

        // Clear cart after successful checkout
        $cartModel->clear($userId);

        // Generate invoice PDF and save to assets/invoices/invoice_{orderId}.pdf
        try {
            $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
            $stmtUser->execute([$userId]);
            $user = $stmtUser->fetch();

            $invoiceItems = [];
            foreach ($cartItems as $item) {
                $invoiceItems[] = [
                    'name' => $item['name'],
                    'quantity' => (int)$item['quantity'],
                    'price' => (float)$item['effective_price']
                ];
            }

            $invoiceService = new InvoicePdfService();
            $invoiceService->generateInvoice(
                __DIR__ . '/../assets/invoices/invoice_' . $orderId . '.pdf',
                [
                    'order_id' => $orderId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'customer_name' => $user['name'] ?? ($_SESSION['user_name'] ?? 'Customer'),
                    'customer_email' => $user['email'] ?? '',
                    'shipping_address' => $shippingAddress,
                    'shipping_city' => $shippingCity,
                    'shipping_zip' => $shippingZip,
                    'shipping_phone' => $shippingPhone,
                    'shipping_notes' => $shippingNotes,
                    'items' => $invoiceItems,
                    'subtotal' => $subtotal,
                    'discount' => $discount_amount,
                    'shipping' => $shipping,
                    'tax' => $tax,
                    'total' => $total
                ]
            );
        } catch (Exception $e) {
            // Keep checkout successful even if invoice generation fails.
        }

        // Redirect to success page (or back to cart with success message)
        $_SESSION['checkout_success'] = "Order #$orderId placed successfully! Thank you for shopping with Vegora.";
        header('Location: ../checkout_success.php?order_id=' . $orderId);
        exit;
    } else {
        $_SESSION['checkout_error'] = "Something went wrong while processing your order.";
        header('Location: ../checkout.php');
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    // Admin only action
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../login.php');
        exit;
    }

    $orderId = intval($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $orderModel = new Order($pdo);

    if ($orderId > 0 && !empty($status)) {
        if ($orderModel->updateStatus($orderId, $status)) {
            $_SESSION['admin_success'] = "Order #$orderId status updated to " . htmlspecialchars($status) . ".";
        } else {
            $_SESSION['admin_error'] = "Failed to update order status.";
        }
    } else {
        $_SESSION['admin_error'] = "Invalid order data provided.";
    }
    
    header('Location: ../admin/orders.php');
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'cancel_user') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $orderModel = new Order($pdo);
    
    if ($orderId > 0 && $orderModel->cancelUserOrder($orderId, $userId)) {
        $_SESSION['orders_success'] = "Order #$orderId has been successfully cancelled.";
    } else {
        $_SESSION['orders_error'] = "Unable to cancel this order. It may have already been processed or shipped.";
    }
    header('Location: ../orders.php');
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'apply_coupon') {
    $code = trim($_POST['coupon_code'] ?? '');
    
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    
    if ($coupon) {
        $stmtU = $pdo->prepare("SELECT id FROM user_coupons WHERE user_id = ? AND coupon_id = ?");
        $stmtU->execute([$userId, $coupon['id']]);
        if ($stmtU->fetch()) {
            $_SESSION['checkout_error'] = "You have already used this coupon code.";
        } else {
            $_SESSION['coupon'] = [
                'id' => $coupon['id'],
                'code' => $coupon['code'],
                'type' => $coupon['discount_type'],
                'value' => $coupon['discount_value']
            ];
            $_SESSION['checkout_success'] = "Coupon '$code' applied successfully!";
        }
    } else {
        $_SESSION['checkout_error'] = "Invalid or expired coupon code.";
    }
    header('Location: ../checkout.php');
    exit;

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'remove_coupon') {
    unset($_SESSION['coupon']);
    $_SESSION['checkout_success'] = "Coupon removed.";
    header('Location: ../checkout.php');
    exit;

} else {
    // If not a valid POST action
    header('Location: ../cart.php');
    exit;
}
?>
