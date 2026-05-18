<?php $pageTitle = 'Trending Recipes — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container trending-page">
  <h1>🔥 Trending Recipes</h1>
  <p class="muted-note">Ranked by: AVG(rating) × LOG(reviews + 1)</p>

  <div id="trending-list" class="trending-list">
    <p class="loading-text">Loading trending recipes…</p>
  </div>
</main>

<script>
const BASE = '<?= defined("APP_BASE") ? APP_BASE : "" ?>';
(async function loadTrending() {
  const container = document.getElementById('trending-list');
  try {
    const res  = await fetch(BASE + '/api/recipes/trending');
    const data = await res.json();
    const recipes = data.recipes || [];

    if (!recipes.length) {
      container.innerHTML = '<p class="empty-state">No trending recipes yet.</p>';
      return;
    }

    function stars(avg) {
      let s = '';
      for (let i=1;i<=5;i++) s += i<=Math.round(avg) ? '★' : '☆';
      return s;
    }

    container.innerHTML = recipes.map((r, idx) => `
      <div class="trending-item">
        <div class="trending-rank">#${idx+1}</div>
        <div class="trending-img-wrap">
          ${r.image
            ? `<img src="${r.image}" alt="${r.title}">`
            : '<div class="card-img-placeholder small">🍽️</div>'}
        </div>
        <div class="trending-info">
          <a href="${r.url}" class="trending-title">${r.title}</a>
          <p class="card-meta">${r.category_name} · ⏱ ${r.cook_time_mins} min</p>
          <p class="card-rating">
            <span class="stars">${stars(r.avg_rating)}</span>
            <span class="rating-num">${r.avg_rating}</span>
            <span class="review-count">(${r.review_count} reviews)</span>
          </p>
        </div>
      </div>
    `).join('');
  } catch (e) {
    container.innerHTML = '<p class="empty-state">Error loading trending recipes.</p>';
  }
})();
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>