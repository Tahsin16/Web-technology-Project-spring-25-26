<?php $pageTitle = 'Admin Panel — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container">
  <h1>Admin Moderation Panel</h1>

  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Title</th>
        <th>Author</th>
        <th>Cuisine</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recipes as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><a href="<?= url('/recipes/' . $r['id']) ?>"><?= htmlspecialchars($r['title']) ?></a></td>
        <td><?= htmlspecialchars($r['author_name']) ?></td>
        <td><?= htmlspecialchars($r['category_name']) ?></td>
        <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
        <td><?= $r['created_at'] ?></td>
        <td>
          <form method="POST" action="<?= url('/admin/recipes/' . $r['id'] . '/moderate') ?>" style="display:inline">
            <?php if ($r['status'] === 'published'): ?>
              <input type="hidden" name="status" value="draft">
              <button class="btn btn-sm btn-danger">Unpublish</button>
            <?php else: ?>
              <input type="hidden" name="status" value="published">
              <button class="btn btn-sm btn-success">Publish</button>
            <?php endif; ?>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>