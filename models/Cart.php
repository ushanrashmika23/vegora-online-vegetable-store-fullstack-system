<?php
// models/Cart.php

class Cart {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get all items in user's cart
    public function getItems($userId) {
        $stmt = $this->pdo->prepare("
            SELECT c.id as cart_id, c.quantity, p.*, pc.name AS category,
                   CASE WHEN p.discounted_price IS NOT NULL AND p.discounted_price > 0 AND p.discounted_price < p.price THEN p.discounted_price ELSE p.price END AS effective_price
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            LEFT JOIN product_categories pc ON pc.id = p.category_id
            WHERE c.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add product to cart or increment quantity
    public function add($userId, $productId, $qty = 1) {
        // Check if item already exists
        $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update quantity
            $newQty = $existing['quantity'] + $qty;
            $update = $this->pdo->prepare("UPDATE cart SET quantity = :qty WHERE id = :id");
            return $update->execute(['qty' => $newQty, 'id' => $existing['id']]);
        } else {
            // Insert new cart item
            $insert = $this->pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :qty)");
            return $insert->execute([
                'user_id' => $userId, 
                'product_id' => $productId, 
                'qty' => $qty
            ]);
        }
    }

    // Update specific cart item quantity
    public function updateQuantity($cartId, $userId, $qty) {
        if ($qty <= 0) {
            return $this->remove($cartId, $userId);
        }
        $stmt = $this->pdo->prepare("UPDATE cart SET quantity = :qty WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'qty' => $qty,
            'id' => $cartId,
            'user_id' => $userId
        ]);
    }

    // Remove item from cart
    public function remove($cartId, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id' => $cartId,
            'user_id' => $userId
        ]);
    }

    // Get total cart count
    public function getCount($userId) {
        $stmt = $this->pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Clear user cart after checkout
    public function clear($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        return $stmt->execute(['user_id' => $userId]);
    }

    // Get available stock for a product
    public function getProductStock($productId) {
        $stmt = $this->pdo->prepare("SELECT stock FROM products WHERE id = :product_id LIMIT 1");
        $stmt->execute(['product_id' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['stock'] : null;
    }

    // Get current quantity of a product already in a user's cart
    public function getCartProductQuantity($userId, $productId) {
        $stmt = $this->pdo->prepare("SELECT quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id LIMIT 1");
        $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['quantity'] : 0;
    }

    // Fetch cart item with linked product stock for guarded quantity updates
    public function getCartItemWithStock($cartId, $userId) {
        $stmt = $this->pdo->prepare("SELECT c.id, c.quantity, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = :id AND c.user_id = :user_id LIMIT 1");
        $stmt->execute([
            'id' => $cartId,
            'user_id' => $userId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
