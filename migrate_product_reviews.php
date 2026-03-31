<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL,
            review_text TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_product_user_review (product_id, user_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CHECK (rating BETWEEN 1 AND 5)
        )"
    );
    echo "product_reviews table created or already exists.\n";
} catch (Exception $e) {
    echo "Could not create product_reviews table: " . $e->getMessage() . "\n";
}
