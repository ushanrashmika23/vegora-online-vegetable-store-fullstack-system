<?php

class Category {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, name, created_at FROM product_categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT id, name FROM product_categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByName($name) {
        $stmt = $this->pdo->prepare("SELECT id, name FROM product_categories WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => trim((string)$name)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name) {
        $stmt = $this->pdo->prepare("INSERT INTO product_categories (name) VALUES (:name)");
        return $stmt->execute(['name' => trim((string)$name)]);
    }

    public function update($id, $name) {
        $stmt = $this->pdo->prepare("UPDATE product_categories SET name = :name WHERE id = :id");
        return $stmt->execute([
            'id' => (int)$id,
            'name' => trim((string)$name)
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_categories WHERE id = :id");
        return $stmt->execute(['id' => (int)$id]);
    }

    public function getProductCount($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS cnt FROM products WHERE category_id = :id");
        $stmt->execute(['id' => (int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }
}
