<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/RecipeModel.php';
require_once ROOT . '/app/models/CategoryModel.php';
require_once ROOT . '/app/models/BookmarkModel.php';
require_once ROOT . '/app/models/ReviewModel.php';

class DiscoveryController extends BaseController {

    private RecipeModel   $recipes;
    private CategoryModel $cats;
    private BookmarkModel $bookmarks;
    private ReviewModel   $reviews;

    public function __construct() {
        $this->recipes   = new RecipeModel();
        $this->cats      = new CategoryModel();
        $this->bookmarks = new BookmarkModel();
        $this->reviews   = new ReviewModel();
    }

    // ── Browse page ───────────────────────────────────
    public function browse(array $p): void {
        $categories = $this->cats->all();
        $recipes    = $this->recipes->filter([]);   // Initial load — all published
        $this->view('discovery/browse', compact('categories','recipes'));
    }

    // ── Recipe detail page ────────────────────────────
    public function detail(array $p): void {
        $id     = (int)($p['id'] ?? 0);
        $recipe = $this->recipes->findById($id);
        $userId  = $this->currentUserId();
        // Allow owner to preview their own draft; block everyone else from drafts
        if (!$recipe || ($recipe['status'] !== 'published' && (int)$recipe['author_id'] !== $userId)) {
            http_response_code(404); echo "<h1>Recipe not found</h1>"; return;
        }

        $ingredients  = $this->recipes->getIngredients($id);
        $steps        = $this->recipes->getSteps($id);
        $reviews      = $this->reviews->getByRecipe($id);
        $avgData      = $this->recipes->getAvgRating($id);
        $isBookmarked = $userId ? $this->bookmarks->isBookmarked($userId, $id) : false;
        $userReviewed = $userId ? $this->reviews->userReviewed($userId, $id) : false;
        $isAuthor     = $userId && (int)$recipe['author_id'] === $userId;

        $this->view('discovery/detail', compact(
            'recipe','ingredients','steps','reviews','avgData',
            'isBookmarked','userReviewed','isAuthor','userId'
        ));
    }

    // ── Saved recipes ─────────────────────────────────
    public function saved(array $p): void {
        $this->requireAuth();
        $recipes = $this->bookmarks->getSavedRecipes($this->currentUserId());
        $this->view('discovery/saved', ['recipes' => $recipes]);
    }
}