<?php

require_once ROOT . '/app/config/database.php';
class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password_hash, dietary_prefs, role)
             VALUES (:name, :email, :password_hash, :dietary_prefs, 'user')"
        );
        $stmt->execute([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'dietary_prefs' => json_encode($data['dietary_prefs'] ?? []),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $fields = [];
        $values = [];
        foreach (['name','bio','dietary_prefs','profile_pic_path'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $values[$col] = $data[$col];
            }
        }
        if (empty($fields)) return;
        $values['id'] = $id;
        $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id")
                 ->execute($values);
    }

    public function updatePassword(int $id, string $newPassword): void {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([password_hash($newPassword, PASSWORD_BCRYPT), $id]);
    }

    public function getPublishedRecipes(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*,
                    c.name AS category_name,
                    COALESCE(AVG(rv.rating),0) AS avg_rating,
                    COUNT(rv.id) AS review_count
             FROM recipes r
             LEFT JOIN categories c  ON c.id = r.category_id
             LEFT JOIN reviews rv    ON rv.recipe_id = r.id
             WHERE r.author_id = ? AND r.status = 'published'
             GROUP BY r.id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getSavedCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getRecipeCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM recipes WHERE author_id = ? AND status='published'");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}