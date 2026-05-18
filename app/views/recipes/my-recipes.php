<?php $pageTitle = 'My Recipes — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container">
  <div class="page-header">
    <h1>My Recipes</h1>
    <a href="<?= url('/recipes/create') ?>" class="btn btn-primary">+ New Recipe</a>
  </div>

  <?php if (empty($recipes)): ?>
    <div class="empty-state">
      <p>You haven't created any recipes yet.</p>
      <a href="<?= url('/recipes/create') ?>" class="btn btn-primary">Create your first recipe</a>
    </div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Cuisine</th>
        <th>Difficulty</th>
        <th>Rating</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recipes as $r): ?>
      <tr id="row-<?= $r['id'] ?>">
        <td><a href="<?= url('/recipes/' . $r['id']) ?>"><?= htmlspecialchars($r['title']) ?></a></td>
        <td><?= htmlspecialchars($r['category_name']) ?></td>
        <td><span class="badge badge-<?= $r['difficulty'] ?>"><?= ucfirst($r['difficulty']) ?></span></td>
        <td>★ <?= round((float)$r['avg_rating'], 1) ?> (<?= (int)$r['review_count'] ?>)</td>
        <td>
          <span class="badge badge-<?= $r['status'] ?>" id="status-badge-<?= $r['id'] ?>">
            <?= ucfirst($r['status']) ?>
          </span>
        </td>
        <td class="table-actions">
          <a href="<?= url('/recipes/' . $r['id'] . '/edit') ?>" class="btn btn-sm btn-outline">Edit</a>
          <button class="btn btn-sm toggle-publish"
                  data-id="<?= $r['id'] ?>"
                  data-status="<?= $r['status'] ?>">
            <?= $r['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
          </button>
          <form method="POST" action="<?= url('/recipes/' . $r['id'] . '/delete') ?>" style="display:inline"
                onsubmit="return confirm('Delete this recipe?')">
            <button class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</main>

<script>
const BASE = '<?= defined("APP_BASE") ? APP_BASE : "" ?>';
document.querySelectorAll('.toggle-publish').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    btn.disabled = true;
    try {
      const res  = await fetch(BASE + `/api/recipes/${id}/toggle-publish`, { method: 'POST' });
      const data = await res.json();
      if (data.status) {
        btn.dataset.status = data.status;
        btn.textContent    = data.status === 'published' ? 'Unpublish' : 'Publish';
        const badge = document.getElementById('status-badge-' + id);
        badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
        badge.className   = 'badge badge-' + data.status;
      }
    } catch (e) {
      alert('Error toggling status.');
    }
    btn.disabled = false;
  });
});
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>