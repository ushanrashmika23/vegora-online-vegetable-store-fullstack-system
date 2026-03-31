<?php
// models/Order.php

class Order {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Process checkout: create order and order_items transactionally
    public function checkout($userId, $cartItems, $total) {
        if (empty($cartItems)) {
            return false;
        }

        try {
            // Begin transaction
            $this->pdo->beginTransaction();

            // Insert into orders table
            $stmtOrder = $this->pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (:user_id, :total, 'Placed')");
            $stmtOrder->execute([
                'user_id' => $userId,
                'total' => $total
            ]);
            $orderId = $this->pdo->lastInsertId();

            // Insert into order_items table
            $stmtItems = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
            
            // Adjust inventory for each product
            $stmtStock = $this->pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :product_id AND stock >= :qty");

            foreach ($cartItems as $item) {
                // Insert item details
                $stmtItems->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['effective_price']
                ]);

                // Reduce product stock
                $stmtStock->execute([
                    'qty' => $item['quantity'],
                    'product_id' => $item['id']
                ]);
            }

            // Commit transaction
            $this->pdo->commit();
            return $orderId;

        } catch (Exception $e) {
            // Rollback if something failed
            $this->pdo->rollBack();
            return false;
        }
    }

    // Get order history for user
    public function getUserOrders($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper to quickly refund or deduct inventory for an entire order
    private function adjustInventory($orderId, $operation) {
        $items = $this->getOrderDetails($orderId);
        // $operation should be '+' or '-'
        $stmtStock = $this->pdo->prepare("UPDATE products SET stock = stock {$operation} :qty WHERE id = :product_id");
        foreach($items as $i) {
            $stmtStock->execute([
                'qty' => $i['quantity'],
                'product_id' => $i['product_id']
            ]);
        }
    }

    // Cancel an active order by user (only if 'Placed')
    public function cancelUserOrder($orderId, $userId) {
        $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order && $order['status'] === 'Placed') {
            $update = $this->pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = :id");
            if ($update->execute(['id' => $orderId])) {
                $this->adjustInventory($orderId, '+'); // refund stock
                return true;
            }
        }
        return false;
    }

    // --- ADMIN METHODS ---

    // Get all orders with user details
    public function getAllOrders() {
        $stmt = $this->pdo->query("
            SELECT o.*, u.name as user_name, u.email as user_email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get specific order details (products inside)
    public function getOrderDetails($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single order record
    public function getOrderById($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.name as user_name, u.email as user_email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = :id
        ");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update order status explicitly handles stock logic
    public function updateStatus($orderId, $status) {
        $current = $this->getOrderById($orderId);
        if (!$current || $current['status'] === $status) {
            return false; // nothing to do
        }

        $stmt = $this->pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $success = $stmt->execute([
            'status' => $status,
            'id' => $orderId
        ]);

        if ($success) {
            if ($current['status'] !== 'Cancelled' && $status === 'Cancelled') {
                // Refund inventory
                $this->adjustInventory($orderId, '+');
            } elseif ($current['status'] === 'Cancelled' && $status !== 'Cancelled') {
                // Deduct inventory (order revived)
                $this->adjustInventory($orderId, '-');
            }
        }
        
        return $success;
    }
}
?>
