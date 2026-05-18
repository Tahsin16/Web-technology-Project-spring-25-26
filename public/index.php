<?php

// Front Controller
define('ROOT', dirname(__DIR__));
require ROOT . '/app/config/database.php';

session_start();

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';

// Strip base path if running in a subfolder
if (defined('APP_BASE') && APP_BASE !== '' && str_starts_with($uri, APP_BASE)) {
    $uri = substr($uri, strlen(APP_BASE)) ?: '/';
}
$method = $_SERVER['REQUEST_METHOD'];

// 404 Helper
function notFound(): void {
    http_response_code(404);
    echo "<h1>404 — Page Not Found</h1>";
    exit;
}

// Routes
$routes = [

    // Home / Auth (Task 1)
    'GET /'                                     => ['AuthController',       'home'],
    'GET /register'                             => ['AuthController',       'showRegister'],
    'POST /register'                            => ['AuthController',       'register'],
    'GET /login'                                => ['AuthController',       'showLogin'],
    'POST /login'                               => ['AuthController',       'login'],
    'GET /logout'                               => ['AuthController',       'logout'],

    // Profile (Task 1)
    'GET /users/{id}'                           => ['ProfileController',    'show'],
    'GET /profile/edit'                         => ['ProfileController',    'edit'],
    'POST /profile/edit'                        => ['ProfileController',    'update'],

    // Admin (Task 1)
    'GET /admin'                                => ['AdminController',      'index'],
    'POST /admin/recipes/{id}/moderate'         => ['AdminController',      'moderate'],

    // Categories (Task 2)
    'GET /categories'                           => ['CategoryController',   'index'],
    'POST /categories'                          => ['CategoryController',   'store'],
    'POST /categories/{id}/edit'                => ['CategoryController',   'update'],
    'POST /categories/{id}/delete'              => ['CategoryController',   'destroy'],

    // Recipes (Task 2) — static/specific routes MUST come before {id} wildcard routes
    'GET /recipes/create'                       => ['RecipeController',     'create'],
    'POST /recipes/create'                      => ['RecipeController',     'store'],
    'GET /my-recipes'                           => ['RecipeController',     'myRecipes'],

    // Discovery & Bookmarks (Task 3) — static routes before wildcards
    'GET /browse'                               => ['DiscoveryController',  'browse'],
    'GET /saved'                                => ['DiscoveryController',  'saved'],

    // Wildcard recipe routes — these must come AFTER /recipes/create and /browse
    'GET /recipes/{id}/edit'                    => ['RecipeController',     'edit'],
    'POST /recipes/{id}/edit'                   => ['RecipeController',     'update'],
    'POST /recipes/{id}/delete'                 => ['RecipeController',     'destroy'],
    'GET /recipes/{id}'                         => ['DiscoveryController',  'detail'],

    // Reviews / Trending (Task 4)
    'GET /trending'                             => ['ReviewController',     'trending'],

    // ── AJAX API endpoints ────────────────────────────────────────
    'GET /api/recipes/search'                   => ['ApiRecipeController',  'search'],
    'GET /api/recipes/trending'                 => ['ApiRecipeController',  'trending'],
    'GET /api/recipes'                          => ['ApiRecipeController',  'filter'],
    'POST /api/recipes/{id}/toggle-publish'     => ['ApiRecipeController',  'togglePublish'],
    'POST /api/bookmarks/toggle'                => ['ApiBookmarkController','toggle'],
    'POST /api/reviews'                         => ['ApiReviewController',  'store'],
    'POST /api/reviews/{id}/reply'              => ['ApiReviewController',  'reply'],
];

// Route Matching
$matched = false;
$params  = [];

foreach ($routes as $pattern => $handler) {

    [$routeMethod, $routePath] = explode(' ', $pattern, 2);

    if ($routeMethod !== $method) continue;

    $regex = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[^/]+)', $routePath);
    $regex = '@^' . $regex . '$@';

    if (preg_match($regex, $uri, $matches)) {
        foreach ($matches as $key => $value) {
            if (!is_int($key)) $params[$key] = $value;
        }
        $matched = $handler;
        break;
    }
}

if (!$matched) { notFound(); }

[$controllerName, $action] = $matched;

// API controllers live in a subdirectory
$apiControllers = ['ApiRecipeController','ApiBookmarkController','ApiReviewController'];
if (in_array($controllerName, $apiControllers, true)) {
    $controllerFile = ROOT . '/app/controllers/api/' . $controllerName . '.php';
} else {
    $controllerFile = ROOT . '/app/controllers/' . $controllerName . '.php';
}

if (!file_exists($controllerFile)) {
    die("Controller file not found: " . $controllerFile);
}

require_once $controllerFile;

$controller = new $controllerName();
$controller->$action($params);
