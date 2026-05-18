<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/ReviewModel.php';
require_once ROOT . '/app/models/RecipeModel.php';

class ApiReviewController extends BaseController {

    private ReviewModel  $reviews;
    private RecipeModel  $recipes;

    public function __construct() {
        $this->reviews = new ReviewModel();
        $this->recipes = new RecipeModel();
    }

    // POST /api/reviews   { recipe_id, rating, review_text }
    public function store(array $p): void {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Login required'], 401);
        }

        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $recipeId = (int)($body['recipe_id']  ?? 0);
        $rating   = (int)($body['rating']     ?? 0);
        $text     = trim($body['review_text'] ?? '');
        $userId   = (int)$_SESSION['user_id'];

        if ($recipeId < 1 || $rating < 1 || $rating > 5) {
            $this->json(['error' => 'Invalid data'], 422);
        }

        // Prevent author from reviewing own recipe
        $recipe = $this->recipes->findById($recipeId);
        if (!$recipe) $this->json(['error' => 'Recipe not found'], 404);
        if ((int)$recipe['author_id'] === $userId) {
            $this->json(['error' => 'You cannot review your own recipe.'], 403);
        }

        // One review per user per recipe (DB unique index will also enforce)
        if ($this->reviews->userReviewed($userId, $recipeId)) {
            $this->json(['error' => 'You have already reviewed this recipe.'], 409);
        }

        $review  = $this->reviews->create($recipeId, $userId, $rating, $text);
        $avgData = $this->reviews->getAvgAndCount($recipeId);

        $this->json([
            'review'  => $review,
            'new_avg' => (float)$avgData['new_avg'],
            'count'   => (int)$avgData['count'],
        ]);
    }

    // POST /api/reviews/{id}/reply   { reply_text }
    public function reply(array $p): void {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Login required'], 401);
        }

        $reviewId  = (int)($p['id'] ?? 0);
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $replyText = trim($body['reply_text'] ?? '');
        $userId    = (int)$_SESSION['user_id'];

        if ($reviewId < 1 || $replyText === '') {
            $this->json(['error' => 'Invalid data'], 422);
        }

        $ok = $this->reviews->addReply($reviewId, $userId, $replyText);
        if (!$ok) {
            $this->json(['error' => 'Not authorized or review not found'], 403);
        }

        $this->json(['reply_text' => $replyText]);
    }
}