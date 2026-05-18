<?php
require_once ROOT . '/app/config/database.php';

class RecipeModel {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // ── Single recipe ──────────────────────────────────
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT r.*, c.name AS category_name,
                    u.name AS author_name, u.profile_pic_path AS author_avatar
             FROM recipes r
             LEFT JOIN categories c ON c.id = r.category_id
             LEFT JOIN users u      ON u.id = r.author_id
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ── Create ─────────────────────────────────────────
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO recipes
               (author_id, category_id, title, description, diet_type, difficulty,
                prep_time_mins, cook_time_mins, servings, featured_image_path, status)
             VALUES
               (:author_id,:category_id,:title,:description,:diet_type,:difficulty,
                :prep_time_mins,:cook_time_mins,:servings,:featured_image_path,:status)"
        );
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    // ── Update ─────────────────────────────────────────
    public function update(int $id, array $data): void {
        $data['id'] = $id;
        $stmt = $this->db->prepare(
            "UPDATE recipes SET
               category_id=:category_id, title=:title, description=:description,
               diet_type=:diet_type, difficulty=:difficulty, prep_time_mins=:prep_time_mins,
               cook_time_mins=:cook_time_mins, servings=:servings,
               featured_image_path=:featured_image_path, status=:status
             WHERE id=:id AND author_id=:author_id"
        );
        $stmt->execute($data);
    }

    // ── Delete ─────────────────────────────────────────
    public function delete(int $id, int $authorId): void {
        $stmt = $this->db->prepare("DELETE FROM recipes WHERE id = ? AND author_id = ?");
        $stmt->execute([$id, $authorId]);
    }

    // ── Toggle publish ─────────────────────────────────
    public function toggleStatus(int $id, int $authorId): string {
        $stmt = $this->db->prepare("SELECT status FROM recipes WHERE id=? AND author_id=?");
        $stmt->execute([$id, $authorId]);
        $row = $stmt->fetch();
        if (!$row) throw new RuntimeException('Not found');
        $newStatus = $row['status'] === 'published' ? 'draft' : 'published';
        $this->db->prepare("UPDATE recipes SET status=? WHERE id=?")->execute([$newStatus, $id]);
        return $newStatus;
    }

    // ── My recipes ────────────────────────────────────
    public function getByAuthor(int $authorId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, c.name AS category_name,
                    COALESCE(AVG(rv.rating),0) AS avg_rating,
                    COUNT(DISTINCT rv.id) AS review_count
             FROM recipes r
             LEFT JOIN categories c ON c.id = r.category_id
             LEFT JOIN reviews rv   ON rv.recipe_id = r.id
             WHERE r.author_id = ?
             GROUP BY r.id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute([$authorId]);
        return $stmt->fetchAll();
    }

    // ── Filtered browse (AJAX) ────────────────────────
    public function filter(array $f): array {
        $where  = ["r.status = 'published'"];
        $params = [];

        if (!empty($f['cuisine_id'])) {
            $where[] = "r.category_id = :cuisine_id";
            $params['cuisine_id'] = $f['cuisine_id'];
        }
        if (!empty($f['diet'])) {
            $where[] = "r.diet_type = :diet";
            $params['diet'] = $f['diet'];
        }
        if (!empty($f['difficulty'])) {
            $where[] = "r.difficulty = :difficulty";
            $params['difficulty'] = $f['difficulty'];
        }
        if (!empty($f['max_cook'])) {
            $where[] = "r.cook_time_mins <= :max_cook";
            $params['max_cook'] = (int)$f['max_cook'];
        }

        $sql = "SELECT r.*, c.name AS category_name,
                       COALESCE(AVG(rv.rating),0) AS avg_rating,
                       COUNT(DISTINCT rv.id) AS review_count
                FROM recipes r
                LEFT JOIN categories c ON c.id = r.category_id
                LEFT JOIN reviews rv   ON rv.recipe_id = r.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY r.id
                ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Live search (AJAX) ────────────────────────────
    public function search(string $q): array {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            "SELECT DISTINCT r.*, c.name AS category_name,
                    COALESCE(AVG(rv.rating),0) AS avg_rating,
                    COUNT(DISTINCT rv.id) AS review_count
             FROM recipes r
             LEFT JOIN categories c    ON c.id = r.category_id
             LEFT JOIN ingredients ing ON ing.recipe_id = r.id
             LEFT JOIN reviews rv      ON rv.recipe_id = r.id
             WHERE r.status = 'published'
               AND (r.title LIKE :q1 OR ing.name LIKE :q2)
             GROUP BY r.id
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['q1' => $like, 'q2' => $like]);
        return $stmt->fetchAll();
    }

    
    // ── Trending (Fixed) ─────────────────────────────
    public function trending(int $limit = 10): array {

        $sql = "SELECT recipes.*,
                       COUNT(reviews.id) AS review_count,
                       COALESCE(AVG(reviews.rating), 0) AS avg_rating,
                       (COALESCE(AVG(reviews.rating),0) * LOG(COUNT(reviews.id) + 1)) AS trending_score

                FROM recipes

                LEFT JOIN reviews
                    ON recipes.id = reviews.recipe_id

                WHERE recipes.status = 'published'

                GROUP BY recipes.id

                ORDER BY trending_score DESC
                LIMIT " . (int)$limit;

        $result = mysqli_query($this->conn, $sql);

        $recipes = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $recipes[] = $row;
        }

        return $recipes;
    }

// ── Detail page: ingredients + steps ─────────────
    public function getIngredients(int $recipeId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM ingredients WHERE recipe_id = ? ORDER BY order_index"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getSteps(int $recipeId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM steps WHERE recipe_id = ? ORDER BY step_order"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    // ── Save ingredients & steps ───────────────────────
    public function saveIngredients(int $recipeId, array $ingredients): void {
        $this->db->prepare("DELETE FROM ingredients WHERE recipe_id = ?")->execute([$recipeId]);
        $stmt = $this->db->prepare(
            "INSERT INTO ingredients (recipe_id, name, quantity, unit, order_index)
             VALUES (?,?,?,?,?)"
        );
        foreach ($ingredients as $i => $ing) {
            $name = trim($ing['name'] ?? '');
            if ($name === '') continue;
            $stmt->execute([
                $recipeId,
                $name,
                trim($ing['quantity'] ?? ''),
                trim($ing['unit']     ?? ''),
                $i,
            ]);
        }
    }

    public function saveSteps(int $recipeId, array $steps): void {
        $this->db->prepare("DELETE FROM steps WHERE recipe_id = ?")->execute([$recipeId]);
        $stmt = $this->db->prepare(
            "INSERT INTO steps (recipe_id, instruction, step_order) VALUES (?,?,?)"
        );
        $order = 0;
        foreach ($steps as $instruction) {
            $instruction = trim($instruction);
            if ($instruction === '') continue;
            $stmt->execute([$recipeId, $instruction, $order++]);
        }
    }

    // ── Avg rating for a single recipe ────────────────
    public function getAvgRating(int $recipeId): array {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS count
             FROM reviews WHERE recipe_id = ?"
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetch();
    }
}