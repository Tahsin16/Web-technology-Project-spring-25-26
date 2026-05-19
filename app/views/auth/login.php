<?php $pageTitle = 'Login — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="auth-page">
  <div class="auth-card">
    <h1>Welcome Back</h1>

    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success">Account created! Please log in.</div>
    <?php endif; ?>

    <?php if (!empty($errors['form'])): ?>
      <div class="alert alert-error"><?= htmlspecialchars($errors['form']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('/login') ?>" novalidate>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required autofocus>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Log In</button>
    </form>

    <p class="auth-alt">New here? <a href="<?= url('/register') ?>">Create an account</a></p>
  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>