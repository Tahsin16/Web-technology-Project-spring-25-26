<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/CategoryModel.php';

class CategoryController extends BaseController {

    private CategoryModel $cats;

    public function __construct() {
        $this->cats = new CategoryModel();
    }

    public function index(array $p): void {
        $this->requireAuth();
        $categories = $this->cats->all();
        $this->view('recipes/categories', ['categories' => $categories, 'errors' => [], 'old' => '']);
    }

    public function store(array $p): void {
        $this->requireAuth();
        $name   = trim($_POST['name'] ?? '');
        $errors = [];
        if ($name === '') $errors['name'] = 'Category name is required.';

        if (!empty($errors)) {
            $this->view('recipes/categories', ['categories' => $this->cats->all(), 'errors' => $errors, 'old' => $name]);
            return;
        }
        $this->cats->create($name);
        $this->redirect('/categories');
    }

    public function update(array $p): void {
        $this->requireAuth();
        $id   = (int)($p['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') $this->cats->update($id, $name);
        $this->redirect('/categories');
    }

    public function destroy(array $p): void {
        $this->requireAuth();
        $id = (int)($p['id'] ?? 0);
        if (!$this->cats->delete($id)) {
            $categories = $this->cats->all();
            $this->view('recipes/categories', [
                'categories' => $categories,
                'errors'     => ['delete' => 'Cannot delete: recipes are using this category.'],
                'old'        => '',
            ]);
            return;
        }
        $this->redirect('/categories');
    }
}