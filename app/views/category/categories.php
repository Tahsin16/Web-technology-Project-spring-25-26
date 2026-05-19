<?php $pageTitle = 'Cuisine Categories — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container" style="max-width:700px">
  <h1>Cuisine Categories</h1>

  <?php if (!empty($errors['delete'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($errors['delete']) ?></div>
  <?php endif; ?>

  <!-- Add new category -->
  <form method="POST" action="<?= url('/categories') ?>" class="inline-form">
    <div class="form-group <?= !empty($errors['name']) ? 'has-error' : '' ?>" style="display:flex;gap:.5rem">
      <input type="text" name="name" placeholder="New cuisine name" value="<?= htmlspecialchars($old ?? '') ?>" required style="flex:1">
      <button type="submit" class="btn btn-primary">Add</button>
    </div>
    <?php if (!empty($errors['name'])): ?><span class="error-msg"><?= $errors['name'] ?></span><?php endif; ?>
  </form>

  <table class="data-table" style="margin-top:1.5rem">
    <thead>
      <tr><th>#</th><th>Name</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $cat): ?>
      <tr>
        <td><?= $cat['id'] ?></td>
        <td>
          <!-- Inline edit form -->
          <form method="POST" action="<?= url('/categories/' . $cat['id'] . '/edit') ?>" style="display:flex;gap:.5rem;align-items:center">
            <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required style="flex:1">
            <button type="submit" class="btn btn-sm btn-outline">Save</button>
          </form>
        </td>
        <td>
          <form method="POST" action="<?= url('/categories/' . $cat['id'] . '/delete') ?>"
                onsubmit="return confirm('Delete this category?')">
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>