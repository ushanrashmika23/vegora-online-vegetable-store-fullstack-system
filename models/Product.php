<?php
// models/Product.php

class Product {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch all products
    public function getAll() {
        $stmt = $this->pdo->query("SELECT p.*, pc.name AS category, CASE WHEN p.discounted_price IS NOT NULL AND p.discounted_price > 0 AND p.discounted_price < p.price THEN p.discounted_price ELSE p.price END AS effective_price FROM products p LEFT JOIN product_categories pc ON pc.id = p.category_id ORDER BY p.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Find a single product by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, pc.name AS category, CASE WHEN p.discounted_price IS NOT NULL AND p.discounted_price > 0 AND p.discounted_price < p.price THEN p.discounted_price ELSE p.price END AS effective_price FROM products p LEFT JOIN product_categories pc ON pc.id = p.category_id WHERE p.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get top-selling products, optionally excluding one product
    public function getMostSelling($limit = 4, $excludeProductId = null) {
        $limit = max(1, (int)$limit);

        $sql = "
                 SELECT p.*, pc.name AS category,
                   CASE WHEN p.discounted_price IS NOT NULL AND p.discounted_price > 0 AND p.discounted_price < p.price THEN p.discounted_price ELSE p.price END AS effective_price,
                   SUM(oi.quantity) AS total_sold
            FROM products p
                 LEFT JOIN product_categories pc ON pc.id = p.category_id
            JOIN order_items oi ON oi.product_id = p.id
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status != 'Cancelled'
        ";

        $params = [];
        if ($excludeProductId !== null) {
            $sql .= " AND p.id != :exclude_id";
            $params['exclude_id'] = (int)$excludeProductId;
        }

        $sql .= " GROUP BY p.id ORDER BY total_sold DESC LIMIT {$limit}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback to latest products if no orders exist yet.
        if (empty($items)) {
            $fallbackSql = "SELECT p.*, pc.name AS category, CASE WHEN p.discounted_price IS NOT NULL AND p.discounted_price > 0 AND p.discounted_price < p.price THEN p.discounted_price ELSE p.price END AS effective_price, 0 AS total_sold FROM products p LEFT JOIN product_categories pc ON pc.id = p.category_id";
            $fallbackParams = [];
            if ($excludeProductId !== null) {
                $fallbackSql .= " WHERE id != :exclude_id";
                $fallbackParams['exclude_id'] = (int)$excludeProductId;
            }
            $fallbackSql .= " ORDER BY created_at DESC LIMIT {$limit}";

            $fallbackStmt = $this->pdo->prepare($fallbackSql);
            $fallbackStmt->execute($fallbackParams);
            return $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $items;
    }

    // Get aggregate rating summary for one product
    public function getRatingSummary($productId) {
        $stmt = $this->pdo->prepare("SELECT COALESCE(AVG(rating), 0) AS average_rating, COUNT(*) AS review_count FROM product_reviews WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'average_rating' => $summary ? (float)$summary['average_rating'] : 0.0,
            'review_count' => $summary ? (int)$summary['review_count'] : 0
        ];
    }

    // Get latest reviews for a product
    public function getReviews($productId, $limit = 20) {
        $limit = max(1, (int)$limit);
        $stmt = $this->pdo->prepare("SELECT pr.*, u.name AS user_name FROM product_reviews pr JOIN users u ON u.id = pr.user_id WHERE pr.product_id = :product_id ORDER BY pr.created_at DESC LIMIT {$limit}");
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch logged-in user's existing review for this product (if any)
    public function getUserReview($productId, $userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_reviews WHERE product_id = :product_id AND user_id = :user_id LIMIT 1");
        $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create or update review (1 review per user per product)
    public function saveReview($productId, $userId, $rating, $reviewText) {
        $existing = $this->getUserReview($productId, $userId);

        if ($existing) {
            $stmt = $this->pdo->prepare("UPDATE product_reviews SET rating = :rating, review_text = :review_text WHERE id = :id");
            return $stmt->execute([
                'rating' => $rating,
                'review_text' => $reviewText,
                'id' => $existing['id']
            ]);
        }

        $stmt = $this->pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, review_text) VALUES (:product_id, :user_id, :rating, :review_text)");
        return $stmt->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $rating,
            'review_text' => $reviewText
        ]);
    }

    // Add a new product
    public function add($name, $price, $discountedPrice, $image, $stock, $stockLimit, $categoryId, $description = null) {
        $stmt = $this->pdo->prepare("INSERT INTO products (name, price, discounted_price, image, stock, stock_limit, category_id, description) VALUES (:name, :price, :discounted_price, :image, :stock, :stock_limit, :category_id, :description)");
        return $stmt->execute([
            'name' => $name,
            'price' => $price,
            'discounted_price' => $discountedPrice,
            'image' => $image,
            'stock' => $stock,
            'stock_limit' => $stockLimit,
            'category_id' => (int)$categoryId,
            'description' => $description
        ]);
    }

    // Update an existing product
    public function update($id, $name, $price, $discountedPrice, $image, $stock, $stockLimit, $categoryId, $description = null) {
        $stmt = $this->pdo->prepare("UPDATE products SET name = :name, price = :price, discounted_price = :discounted_price, image = :image, stock = :stock, stock_limit = :stock_limit, category_id = :category_id, description = :description WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'discounted_price' => $discountedPrice,
            'image' => $image,
            'stock' => $stock,
            'stock_limit' => $stockLimit,
            'category_id' => (int)$categoryId,
            'description' => $description
        ]);
    }

    // Delete a product
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Increase stock by a positive quantity.
    public function increaseStock($id, $amount) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock + :amount WHERE id = :id");
        return $stmt->execute([
            'id' => (int)$id,
            'amount' => (int)$amount
        ]);
    }
}
?>
