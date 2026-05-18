<?php
require_once ROOT . '/app/config/database.php';

class BookmarkModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function isBookmarked(int $userId, int $recipeId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM bookmarks WHERE user_id = ? AND recipe_id = ?"
        );
        $stmt->execute([$userId, $recipeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function toggle(int $userId, int $recipeId): bool {
        if ($this->isBookmarked($userId, $recipeId)) {
            $this->db->prepare("DELETE FROM bookmarks WHERE user_id = ? AND recipe_id = ?")
                     ->execute([$userId, $recipeId]);
            return false;
        } else {
            $this->db->prepare("INSERT INTO bookmarks (user_id, recipe_id) VALUES (?,?)")
                     ->execute([$userId, $recipeId]);
            return true;
        }
    }

    public function getSavedRecipes(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, c.name AS category_name,
                    COALESCE(AVG(rv.rating),0) AS avg_rating,
                    COUNT(DISTINCT rv.id) AS review_count
             FROM bookmarks b
             JOIN recipes r     ON r.id = b.recipe_id
             LEFT JOIN categories c ON c.id = r.category_id
             LEFT JOIN reviews rv   ON rv.recipe_id = r.id
             WHERE b.user_id = ? AND r.status = 'published'
             GROUP BY r.id
             ORDER BY b.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}