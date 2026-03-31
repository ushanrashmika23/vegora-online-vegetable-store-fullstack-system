<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT DEFAULT NULL");
    echo "Added description column.\n";
} catch (Exception $e) {
    echo "Description column might already exist: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("UPDATE orders SET status = 'Placed' WHERE status = 'Pending'");
    $pdo->exec("UPDATE orders SET status = 'Delivered' WHERE status = 'Completed'");
    echo "Updated order statuses.\n";
} catch (Exception $e) {
    echo "Failed to update order statuses: " . $e->getMessage() . "\n";
}
