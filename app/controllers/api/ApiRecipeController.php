<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/RecipeModel.php';

class ApiRecipeController extends BaseController {

    private RecipeModel $recipes;

    public function __construct() {
        $this->recipes = new RecipeModel();
    }

    // GET /api/recipes?cuisine_id=&diet=&difficulty=&max_cook=
    public function filter(array $p): void {
        $filters = [
            'cuisine_id' => $_GET['cuisine_id'] ?? '',
            'diet'       => $_GET['diet']        ?? '',
            'difficulty' => $_GET['difficulty']  ?? '',
            'max_cook'   => $_GET['max_cook']    ?? '',
        ];
        $recipes = $this->recipes->filter($filters);
        $this->json(['recipes' => $this->formatCards($recipes)]);
    }

    // GET /api/recipes/search?q=
    public function search(array $p): void {
        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            $this->json(['recipes' => []]);
        }
        $recipes = $this->recipes->search($q);
        $this->json(['recipes' => $this->formatCards($recipes)]);
    }

    // GET /api/recipes/trending
    public function trending(array $p): void {
        $recipes = $this->recipes->trending(10);
        $this->json(['recipes' => $this->formatCards($recipes, true)]);
    }

    // POST /api/recipes/{id}/toggle-publish
    public function togglePublish(array $p): void {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthenticated'], 401);
        }
        try {
            $newStatus = $this->recipes->toggleStatus(
                (int)($p['id'] ?? 0),
                (int)$_SESSION['user_id']
            );
            $this->json(['status' => $newStatus]);
        } catch (RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 404);
        }
    }

    // ── Format recipe rows to card arrays ─────────────
    private function formatCards(array $rows, bool $withScore = false): array {
        $base = defined('APP_BASE') ? APP_BASE : '';
        return array_map(function ($r) use ($withScore, $base) {
            $card = [
                'id'            => $r['id'],
                'title'         => $r['title'],
                'category_name' => $r['category_name'] ?? '',
                'difficulty'    => $r['difficulty'],
                'cook_time_mins'=> (int)$r['cook_time_mins'],
                'avg_rating'    => round((float)$r['avg_rating'], 1),
                'review_count'  => (int)$r['review_count'],
                'image'         => $r['featured_image_path']
                                    ? $base . '/uploads/recipes/' . basename($r['featured_image_path'])
                                    : null,
                'url'           => $base . '/recipes/' . $r['id'],
            ];
            if ($withScore) {
                $card['score'] = round((float)($r['score'] ?? 0), 2);
            }
            return $card;
        }, $rows);
    }
}