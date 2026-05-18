<?php
require_once ROOT . '/app/config/database.php';

class ReviewModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getByRecipe(int $recipeId): array {
        $stmt = $this->db->prepare(
            "SELECT rv.*, u.name AS reviewer_name, u.profile_pic_path AS reviewer_avatar
             FROM reviews rv
             JOIN users u ON u.id = rv.user_id
             WHERE rv.recipe_id = ?
             ORDER BY rv.created_at DESC"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function userReviewed(int $userId, int $recipeId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reviews WHERE user_id = ? AND recipe_id = ?"
        );
        $stmt->execute([$userId, $recipeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(int $recipeId, int $userId, int $rating, string $text): array {
        $stmt = $this->db->prepare(
            "INSERT INTO reviews (recipe_id, user_id, rating, review_text)
             VALUES (?,?,?,?)"
        );
        $stmt->execute([$recipeId, $userId, $rating, $text]);
        $id = (int)$this->db->lastInsertId();

        // Return the full review row with user info
        $stmt = $this->db->prepare(
            "SELECT rv.*, u.name AS reviewer_name, u.profile_pic_path AS reviewer_avatar
             FROM reviews rv JOIN users u ON u.id = rv.user_id WHERE rv.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addReply(int $reviewId, int $authorId, string $replyText): bool {
        // Verify the reply is coming from the recipe's author
        $stmt = $this->db->prepare(
            "SELECT rv.id FROM reviews rv
             JOIN recipes r ON r.id = rv.recipe_id
             WHERE rv.id = ? AND r.author_id = ?"
        );
        $stmt->execute([$reviewId, $authorId]);
        if (!$stmt->fetch()) return false;

        $this->db->prepare("UPDATE reviews SET reply_text = ? WHERE id = ?")
                 ->execute([$replyText, $reviewId]);
        return true;
    }

    public function getAvgAndCount(int $recipeId): array {
        $stmt = $this->db->prepare(
            "SELECT ROUND(COALESCE(AVG(rating),0),1) AS new_avg, COUNT(*) AS count
             FROM reviews WHERE recipe_id = ?"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetch();
    }
}