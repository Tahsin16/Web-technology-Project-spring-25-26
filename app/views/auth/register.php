<?php $pageTitle = 'Register — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="auth-page">
  <div class="auth-card">
    <h1>Create Account</h1>
    <?php if (!empty($errors['form'])): ?>
      <div class="alert alert-error"><?= htmlspecialchars($errors['form']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('/register') ?>" novalidate>
      <div class="form-group <?= !empty($errors['name']) ? 'has-error' : '' ?>">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
        <?php if (!empty($errors['name'])): ?><span class="error-msg"><?= $errors['name'] ?></span><?php endif; ?>
      </div>

      <div class="form-group <?= !empty($errors['email']) ? 'has-error' : '' ?>">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
        <?php if (!empty($errors['email'])): ?><span class="error-msg"><?= $errors['email'] ?></span><?php endif; ?>
      </div>

      <div class="form-group <?= !empty($errors['password']) ? 'has-error' : '' ?>">
        <label>Password <small>(min. 8 characters)</small></label>
        <input type="password" name="password" required minlength="8">
        <?php if (!empty($errors['password'])): ?><span class="error-msg"><?= $errors['password'] ?></span><?php endif; ?>
      </div>

      <div class="form-group <?= !empty($errors['password2']) ? 'has-error' : '' ?>">
        <label>Confirm Password</label>
        <input type="password" name="password2" required>
        <?php if (!empty($errors['password2'])): ?><span class="error-msg"><?= $errors['password2'] ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label>Dietary Preferences</label>
        <div class="checkbox-group">
          <?php
          $allDiets  = ['Vegetarian','Vegan','Gluten-Free','Dairy-Free','Keto','Halal'];
          $oldDiets  = $_POST['dietary_prefs'] ?? [];
          foreach ($allDiets as $d):
            $checked = in_array($d, $oldDiets) ? 'checked' : '';
          ?>
            <label class="checkbox-label">
              <input type="checkbox" name="dietary_prefs[]" value="<?= $d ?>" <?= $checked ?>>
              <?= $d ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Create Account</button>
    </form>

    <p class="auth-alt">Already have an account? <a href="<?= url('/login') ?>">Log in</a></p>
  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>