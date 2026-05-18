<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/BookmarkModel.php';

class ApiBookmarkController extends BaseController {

    private BookmarkModel $bookmarks;

    public function __construct() {
        $this->bookmarks = new BookmarkModel();
    }

    // POST /api/bookmarks/toggle   { recipe_id }
    public function toggle(array $p): void {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Login required'], 401);
        }

        $body     = json_decode(file_get_contents('php://input'), true);
        $recipeId = (int)($body['recipe_id'] ?? $_POST['recipe_id'] ?? 0);

        if ($recipeId < 1) {
            $this->json(['error' => 'Invalid recipe_id'], 400);
        }

        $bookmarked = $this->bookmarks->toggle((int)$_SESSION['user_id'], $recipeId);
        $this->json(['bookmarked' => $bookmarked]);
    }
}