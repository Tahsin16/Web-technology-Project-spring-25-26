<nav class="navbar">
  <a class="navbar-brand" href="<?= url('/browse') ?>">🍳 RecipeHub</a>
  <div class="navbar-links">
    <a href="<?= url('/browse') ?>">Browse</a>
    <a href="<?= url('/trending') ?>">Trending</a>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <a href="<?= url('/recipes/create') ?>">+ New Recipe</a>
      <a href="<?= url('/my-recipes') ?>">My Recipes</a>
      <a href="<?= url('/saved') ?>">Saved</a>
      <a href="<?= url('/users/' . ($_SESSION['user_id'] ?? '')) ?>">Profile</a>
      <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="<?= url('/admin') ?>" class="badge-admin">Admin</a>
      <?php endif; ?>
      <a href="<?= url('/logout') ?>" class="btn-logout">Logout</a>
    <?php else: ?>
      <a href="<?= url('/login') ?>">Login</a>
      <a href="<?= url('/register') ?>" class="btn-register">Register</a>
    <?php endif; ?>
  </div>
</nav>