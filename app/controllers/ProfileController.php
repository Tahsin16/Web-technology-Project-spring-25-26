<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/UserModel.php';

class ProfileController extends BaseController {

    private UserModel $users;

    public function __construct() {
        $this->users = new UserModel();
    }

    // GET /users/{id}
    public function show(array $p): void {
        $user = $this->users->findById((int)($p['id'] ?? 0));
        if (!$user) { http_response_code(404); echo "<h1>User not found</h1>"; return; }

        $recipes      = $this->users->getPublishedRecipes((int)$user['id']);
        $recipeCount  = $this->users->getRecipeCount((int)$user['id']);
        $savedCount   = $this->users->getSavedCount((int)$user['id']);
        $dietPrefs    = json_decode($user['dietary_prefs'] ?? '[]', true) ?: [];
        $isOwner      = $this->currentUserId() === (int)$user['id'];

        $this->view('profile/show', compact('user','recipes','recipeCount','savedCount','dietPrefs','isOwner'));
    }

    // GET /profile/edit
    public function edit(array $p): void {
        $this->requireAuth();
        $user      = $this->users->findById($this->currentUserId());
        $dietPrefs = json_decode($user['dietary_prefs'] ?? '[]', true) ?: [];
        $this->view('profile/edit', ['user' => $user, 'dietPrefs' => $dietPrefs, 'errors' => []]);
    }

    // POST /profile/edit
    public function update(array $p): void {
        $this->requireAuth();
        $userId    = $this->currentUserId();
        $user      = $this->users->findById($userId);
        $errors    = [];

        $bio       = trim($_POST['bio'] ?? '');
        $dietPrefs = $_POST['dietary_prefs'] ?? [];
        $allowedDiets = ['Vegetarian','Vegan','Gluten-Free','Dairy-Free','Keto','Halal'];
        $dietPrefs = array_values(array_intersect($dietPrefs, $allowedDiets));

        // Password change (optional)
        $currPass = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $newPass2 = $_POST['new_password2'] ?? '';

        $changePassword = $currPass !== '' || $newPass !== '';
        if ($changePassword) {
            if (!password_verify($currPass, $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            } elseif (strlen($newPass) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters.';
            } elseif ($newPass !== $newPass2) {
                $errors['new_password2'] = 'Passwords do not match.';
            }
        }

        // Avatar upload
        $avatarPath = $user['profile_pic_path'];
        $uploaded   = $this->handleUpload('profile_pic', 'uploads/avatars/', 1 * 1024 * 1024);
        if ($uploaded) {
            $avatarPath = $uploaded;
        } elseif (!empty($_FILES['profile_pic']['tmp_name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $errors['profile_pic'] = 'Avatar must be JPEG or PNG and under 1 MB.';
        }

        if (!empty($errors)) {
            $currentDietPrefs = json_decode($user['dietary_prefs'] ?? '[]', true) ?: [];
            $this->view('profile/edit', ['user' => $user, 'dietPrefs' => $currentDietPrefs, 'errors' => $errors]);
            return;
        }

        $this->users->update($userId, [
            'bio'              => $bio,
            'dietary_prefs'    => json_encode($dietPrefs),
            'profile_pic_path' => $avatarPath,
        ]);

        if ($changePassword && empty($errors)) {
            $this->users->updatePassword($userId, $newPass);
        }

        $this->redirect('/users/' . $userId);
    }
}