<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/config/database.php';

class AdminController extends BaseController {

    public function index(array $p): void {
        $this->requireAdmin();
        $db = getDB();

        $recipes = $db->query(
            "SELECT r.*, u.name AS author_name, c.name AS category_name
             FROM recipes r
             JOIN users u      ON u.id = r.author_id
             JOIN categories c ON c.id = r.category_id
             ORDER BY r.created_at DESC"
        )->fetchAll();

        $this->view('admin/index', ['recipes' => $recipes]);
    }

    public function moderate(array $p): void {
        $this->requireAdmin();
        $id     = (int)($p['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['published','draft'], true)) {
            $this->json(['error' => 'Invalid status'], 400);
        }

        getDB()->prepare("UPDATE recipes SET status = ? WHERE id = ?")
               ->execute([$status, $id]);

        $this->redirect('/admin');
    }
}