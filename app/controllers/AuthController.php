<?php
require_once ROOT . '/app/controllers/BaseController.php';
require_once ROOT . '/app/models/UserModel.php';
class AuthController extends BaseController {

    private UserModel $users;

    public function __construct() {
        $this->users = new UserModel();
    }

    public function home(array $p): void {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/browse');
        }
        $this->redirect('/login');
    }

    // ── Register ──────────────────────────────────────
    public function showRegister(array $p): void {
        $this->view('auth/register', ['errors' => [], 'old' => []]);
    }

    public function register(array $p): void {
        $errors = [];
        $old    = [
            'name'  => trim($_POST['name']  ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];
        $password  = $_POST['password']  ?? '';
        $password2 = $_POST['password2'] ?? '';
        $dietPrefs = $_POST['dietary_prefs'] ?? [];

        $allowedDiets = ['Vegetarian','Vegan','Gluten-Free','Dairy-Free','Keto','Halal'];
        $dietPrefs    = array_values(array_intersect($dietPrefs, $allowedDiets));

        if ($old['name'] === '')          $errors['name']     = 'Name is required.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required.';
        if (strlen($password) < 8)        $errors['password'] = 'Password must be at least 8 characters.';
        if ($password !== $password2)     $errors['password2']= 'Passwords do not match.';

        if (empty($errors) && $this->users->findByEmail($old['email'])) {
            $errors['email'] = 'Email already registered.';
        }

        if (!empty($errors)) {
            $this->view('auth/register', ['errors' => $errors, 'old' => $old]);
            return;
        }

        $this->users->create([
            'name'          => $old['name'],
            'email'         => $old['email'],
            'password'      => $password,
            'dietary_prefs' => $dietPrefs,
        ]);
        $this->redirect('/login?registered=1');
    }

    // ── Login ─────────────────────────────────────────
    public function showLogin(array $p): void {
        $this->view('auth/login', ['errors' => [], 'old' => []]);
    }

    public function login(array $p): void {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors['form'] = 'Invalid email or password.';
            $this->view('auth/login', ['errors' => $errors, 'old' => ['email' => $email]]);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] === 'admin') {
            $this->redirect('/admin');
        } else {
            $this->redirect('/browse');
        }
    }

    // ── Logout ────────────────────────────────────────
    public function logout(array $p): void {
        session_destroy();
        $this->redirect('/login');
    }
}