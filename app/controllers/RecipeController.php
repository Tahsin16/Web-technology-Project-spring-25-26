<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/RecipeModel.php';
require_once ROOT . '/app/models/CategoryModel.php';

class RecipeController extends BaseController {

    private RecipeModel   $recipes;
    private CategoryModel $cats;

    public function __construct() {
        $this->recipes = new RecipeModel();
        $this->cats    = new CategoryModel();
    }

    // ── My Recipes listing ────────────────────────────
    public function myRecipes(array $p): void {
        $this->requireAuth();
        $recipes = $this->recipes->getByAuthor($this->currentUserId());
        $this->view('recipes/my-recipes', ['recipes' => $recipes]);
    }

    // ── Create form ───────────────────────────────────
    public function create(array $p): void {
        $this->requireAuth();
        $categories = $this->cats->all();
        $this->view('recipes/form', [
            'recipe'     => null,
            'categories' => $categories,
            'errors'     => [],
            'ingredients'=> [['name'=>'','quantity'=>'','unit'=>'']],
            'steps'      => array_fill(0, 10, ''),
        ]);
    }

    // ── Store new recipe ──────────────────────────────
    public function store(array $p): void {
        $this->requireAuth();
        [$data, $errors] = $this->parseForm();

        if (!empty($errors)) {
            $this->view('recipes/form', [
                'recipe'     => null,
                'categories' => $this->cats->all(),
                'errors'     => $errors,
                'ingredients'=> $_POST['ingredients'] ?? [['name'=>'','quantity'=>'','unit'=>'']],
                'steps'      => $_POST['steps'] ?? array_fill(0,10,''),
            ]);
            return;
        }

        $imagePath = $this->handleUpload('featured_image', 'uploads/recipes/', 3 * 1024 * 1024);
        if (!$imagePath && !empty($_FILES['featured_image']['tmp_name'])) {
            $errors['featured_image'] = 'Image must be JPEG/PNG under 3 MB.';
            $this->view('recipes/form', [
                'recipe'     => null,
                'categories' => $this->cats->all(),
                'errors'     => $errors,
                'ingredients'=> $_POST['ingredients'] ?? [],
                'steps'      => $_POST['steps'] ?? array_fill(0,10,''),
            ]);
            return;
        }

        $data['author_id']           = $this->currentUserId();
        $data['featured_image_path'] = $imagePath;

        $recipeId = $this->recipes->create($data);
        $this->recipes->saveIngredients($recipeId, $_POST['ingredients'] ?? []);
        $this->recipes->saveSteps($recipeId, $_POST['steps'] ?? []);

        // Redirect to detail if published, my-recipes if draft
        if ($data['status'] === 'published') {
            $this->redirect('/recipes/' . $recipeId);
        } else {
            $this->redirect('/my-recipes');
        }
    }

    // ── Edit form ─────────────────────────────────────
    public function edit(array $p): void {
        $this->requireAuth();
        $recipe = $this->recipes->findById((int)($p['id'] ?? 0));
        if (!$recipe || (int)$recipe['author_id'] !== $this->currentUserId()) {
            http_response_code(403); echo "<h1>Forbidden</h1>"; return;
        }
        $ingredients = $this->recipes->getIngredients((int)$recipe['id']);
        $stepsRaw    = $this->recipes->getSteps((int)$recipe['id']);
        $steps       = array_fill(0, 10, '');
        foreach ($stepsRaw as $s) $steps[$s['step_order']] = $s['instruction'];

        $this->view('recipes/form', [
            'recipe'     => $recipe,
            'categories' => $this->cats->all(),
            'errors'     => [],
            'ingredients'=> $ingredients ?: [['name'=>'','quantity'=>'','unit'=>'']],
            'steps'      => $steps,
        ]);
    }

    // ── Update ────────────────────────────────────────
    public function update(array $p): void {
        $this->requireAuth();
        $id     = (int)($p['id'] ?? 0);
        $recipe = $this->recipes->findById($id);
        if (!$recipe || (int)$recipe['author_id'] !== $this->currentUserId()) {
            http_response_code(403); return;
        }

        [$data, $errors] = $this->parseForm();

        if (!empty($errors)) {
            $ingredients = $this->recipes->getIngredients($id);
            $stepsRaw    = $this->recipes->getSteps($id);
            $steps       = array_fill(0, 10, '');
            foreach ($stepsRaw as $s) $steps[$s['step_order']] = $s['instruction'];

            $this->view('recipes/form', [
                'recipe'     => $recipe,
                'categories' => $this->cats->all(),
                'errors'     => $errors,
                'ingredients'=> $ingredients,
                'steps'      => $steps,
            ]);
            return;
        }

        // Handle image update
        $imagePath = $recipe['featured_image_path'];
        $uploaded  = $this->handleUpload('featured_image', 'uploads/recipes/', 3 * 1024 * 1024);
        if ($uploaded) {
            $imagePath = $uploaded;
        } elseif (!empty($_FILES['featured_image']['tmp_name'])) {
            $errors['featured_image'] = 'Image must be JPEG/PNG under 3 MB.';
        }

        $data['featured_image_path'] = $imagePath;
        $data['author_id']           = $this->currentUserId();

        $this->recipes->update($id, $data);
        $this->recipes->saveIngredients($id, $_POST['ingredients'] ?? []);
        $this->recipes->saveSteps($id, $_POST['steps'] ?? []);

        if ($data['status'] === 'published') {
            $this->redirect('/recipes/' . $id);
        } else {
            $this->redirect('/my-recipes');
        }
    }

    // ── Delete ────────────────────────────────────────
    public function destroy(array $p): void {
        $this->requireAuth();
        $id = (int)($p['id'] ?? 0);
        $this->recipes->delete($id, $this->currentUserId());
        $this->redirect('/my-recipes');
    }

    // ── Parse & validate recipe form fields ───────────
    private function parseForm(): array {
        $errors = [];
        $data   = [
            'category_id'    => (int)($_POST['category_id']    ?? 0),
            'title'          => trim($_POST['title']           ?? ''),
            'description'    => trim($_POST['description']     ?? ''),
            'diet_type'      => $_POST['diet_type']            ?? 'Any',
            'difficulty'     => $_POST['difficulty']           ?? 'easy',
            'prep_time_mins' => (int)($_POST['prep_time_mins'] ?? 0),
            'cook_time_mins' => (int)($_POST['cook_time_mins'] ?? 0),
            'servings'       => (int)($_POST['servings']       ?? 1),
            'status'         => $_POST['status']               ?? 'published',
        ];

        if ($data['title'] === '')   $errors['title']       = 'Title is required.';
        if ($data['category_id'] < 1) $errors['category_id'] = 'Select a cuisine.';
        if (!in_array($data['diet_type'], ['Any','Vegetarian','Vegan','Gluten-Free','Keto'], true))
            $data['diet_type'] = 'Any';
        if (!in_array($data['difficulty'], ['easy','medium','hard'], true))
            $data['difficulty'] = 'easy';
        if (!in_array($data['status'], ['draft','published'], true))
            $data['status'] = 'published';
        if ($data['servings'] < 1) $data['servings'] = 1;

        return [$data, $errors];
    }
}