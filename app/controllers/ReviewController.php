<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/RecipeModel.php';

class ReviewController extends BaseController {

    public function trending(array $p): void {
        $this->view('reviews/trending', []);
    }
}