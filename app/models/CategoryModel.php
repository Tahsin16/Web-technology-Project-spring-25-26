<?php
require_once ROOT . '/app/config/database.php';

class CategoryModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function all(): array {
        return $this->db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name): void {
        $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([trim($name)]);
    }

    public function update(int $id, string $name): void {
        $stmt = $this->db->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([trim($name), $id]);
    }

    public function delete(int $id): bool {
        // Block if recipes reference this category
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM recipes WHERE category_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return false;

        $this->db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        return true;
    }
}