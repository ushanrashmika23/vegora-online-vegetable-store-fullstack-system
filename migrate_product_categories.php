<?php
require_once __DIR__ . '/config/db.php';

$dbName = (string)$pdo->query("SELECT DATABASE()")->fetchColumn();

function hasColumn(PDO $pdo, $dbName, $table, $column) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND COLUMN_NAME = :column");
    $stmt->execute([
        'db' => $dbName,
        'table' => $table,
        'column' => $column
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

function hasIndex(PDO $pdo, $dbName, $table, $indexName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND INDEX_NAME = :idx");
    $stmt->execute([
        'db' => $dbName,
        'table' => $table,
        'idx' => $indexName
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

function hasConstraint(PDO $pdo, $dbName, $constraintName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = :db AND CONSTRAINT_NAME = :name");
    $stmt->execute([
        'db' => $dbName,
        'name' => $constraintName
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS product_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);
echo "product_categories table ready.\n";

$defaultCategories = ['Organic', 'Root Vegetables', 'Greens', 'Onions & Garlic', 'Uncategorized'];
$stmtInsertCategory = $pdo->prepare("INSERT IGNORE INTO product_categories (name) VALUES (:name)");
foreach ($defaultCategories as $catName) {
    $stmtInsertCategory->execute(['name' => $catName]);
}
echo "Default categories ensured.\n";

$hasLegacyCategory = hasColumn($pdo, $dbName, 'products', 'category');
$hasCategoryId = hasColumn($pdo, $dbName, 'products', 'category_id');

if ($hasLegacyCategory) {
    $legacyCategories = $pdo->query("SELECT DISTINCT TRIM(category) AS legacy_name FROM products WHERE category IS NOT NULL AND TRIM(category) != ''")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($legacyCategories as $row) {
        $stmtInsertCategory->execute(['name' => $row['legacy_name']]);
    }
    echo "Legacy category names imported.\n";
} else {
    echo "Legacy category column not found; skipping legacy import.\n";
}

if (!$hasCategoryId) {
    $pdo->exec("ALTER TABLE products ADD COLUMN category_id INT NULL AFTER stock_limit");
    echo "Added products.category_id.\n";
} else {
    echo "products.category_id already exists.\n";
}

if ($hasLegacyCategory) {
    $pdo->exec(
        "UPDATE products p
         LEFT JOIN product_categories pc ON pc.name = p.category
         SET p.category_id = pc.id
         WHERE p.category_id IS NULL"
    );
    echo "Mapped legacy category text to category_id.\n";
} else {
    echo "No legacy category text mapping needed.\n";
}

$uncategorizedId = $pdo->query("SELECT id FROM product_categories WHERE name = 'Uncategorized' LIMIT 1")->fetchColumn();
if ($uncategorizedId) {
    $stmtFill = $pdo->prepare("UPDATE products SET category_id = :id WHERE category_id IS NULL");
    $stmtFill->execute(['id' => (int)$uncategorizedId]);
    echo "Filled NULL category_id with Uncategorized.\n";
}

$pdo->exec("ALTER TABLE products MODIFY category_id INT NOT NULL");
echo "Enforced NOT NULL on products.category_id.\n";

if (!hasIndex($pdo, $dbName, 'products', 'idx_products_category_id')) {
    $pdo->exec("ALTER TABLE products ADD INDEX idx_products_category_id (category_id)");
    echo "Added index idx_products_category_id.\n";
} else {
    echo "Index idx_products_category_id already exists.\n";
}

if (!hasConstraint($pdo, $dbName, 'fk_products_category_id')) {
    $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_products_category_id FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT");
    echo "Added foreign key fk_products_category_id.\n";
} else {
    echo "Foreign key fk_products_category_id already exists.\n";
}

if ($hasLegacyCategory) {
    $pdo->exec("ALTER TABLE products DROP COLUMN category");
    echo "Dropped legacy products.category column.\n";
} else {
    echo "Legacy products.category column already removed.\n";
}
