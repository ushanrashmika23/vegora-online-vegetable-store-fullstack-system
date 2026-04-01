<?php
require_once __DIR__ . '/config/db.php';

try {
    // Ensure legacy databases have description column used by product add/edit forms.
    $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT DEFAULT NULL");
    echo "Added description column.\n";
} catch (Exception $e) {
    echo "Description column already exists or could not be added: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN discounted_price DECIMAL(10,2) DEFAULT NULL AFTER price");
    echo "Added discounted_price column.\n";
} catch (Exception $e) {
    echo "discounted_price column already exists or could not be added: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN stock_limit INT NOT NULL DEFAULT 20 AFTER stock");
    echo "Added stock_limit column.\n";
} catch (Exception $e) {
    echo "stock_limit column already exists or could not be added: " . $e->getMessage() . "\n";
}

try {
    // Cleanup invalid historical data so the app can rely on discounted_price semantics.
    $pdo->exec("UPDATE products SET discounted_price = NULL WHERE discounted_price IS NOT NULL AND (discounted_price <= 0 OR discounted_price >= price)");
    echo "Normalized invalid discounted_price rows.\n";
} catch (Exception $e) {
    echo "Could not normalize discounted prices: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("UPDATE products SET stock_limit = 20 WHERE stock_limit IS NULL OR stock_limit < 0");
    echo "Normalized invalid stock_limit rows.\n";
} catch (Exception $e) {
    echo "Could not normalize stock_limit values: " . $e->getMessage() . "\n";
}
