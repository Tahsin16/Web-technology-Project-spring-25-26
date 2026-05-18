<?php $pageTitle = 'Saved Recipes — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container">
  <h1>Saved Recipes</h1>

  <?php if (empty($recipes)): ?>
    <div class="empty-state">
      <p>You haven't saved any recipes yet.</p>
      <a href="<?= url('/browse') ?>" class="btn btn-primary">Discover Recipes</a>
    </div>
  <?php else: ?>
  <div class="recipe-grid" id="saved-grid">
    <?php foreach ($recipes as $recipe): ?>
      <div class="saved-card-wrap" id="saved-<?= $recipe['id'] ?>">
        <?php require ROOT . '/app/views/partials/recipe-card.php'; ?>
        <button class="btn btn-sm btn-danger remove-bookmark"
                data-recipe="<?= $recipe['id'] ?>">
          Remove Bookmark
        </button>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<script>
const BASE = '<?= defined("APP_BASE") ? APP_BASE : "" ?>';
document.querySelectorAll('.remove-bookmark').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.recipe;
    btn.disabled = true;
    try {
      const res  = await fetch(BASE + '/api/bookmarks/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ recipe_id: +id }),
      });
      const data = await res.json();
      if (!data.bookmarked) {
        document.getElementById('saved-' + id).remove();
        if (!document.querySelector('.saved-card-wrap')) {
          document.getElementById('saved-grid').innerHTML =
            '<p class="empty-state">No saved recipes.</p>';
        }
      }
    } catch (e) { alert('Error removing bookmark.'); }
    btn.disabled = false;
  });
});
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>