<?php $pageTitle = 'Browse Recipes — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container browse-page">
  <h1>Browse Recipes</h1>

  <!-- ── Search bar ────────────────────────────────── -->
  <div class="search-bar">
    <input type="search" id="search-input" placeholder="Search recipes or ingredients…">
    <span class="search-icon">🔍</span>
  </div>

  <!-- ── Filters ───────────────────────────────────── -->
  <div class="filter-bar" id="filter-bar">
    <div class="filter-group">
      <label>Cuisine</label>
      <select id="f-cuisine">
        <option value="">All</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filter-group">
      <label>Diet</label>
      <select id="f-diet">
        <option value="">All</option>
        <?php foreach (['Any','Vegetarian','Vegan','Gluten-Free','Keto'] as $d): ?>
          <option value="<?= $d ?>"><?= $d ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filter-group">
      <label>Difficulty</label>
      <div class="radio-inline">
        <label><input type="radio" name="f-diff" value=""> All</label>
        <label><input type="radio" name="f-diff" value="easy"> Easy</label>
        <label><input type="radio" name="f-diff" value="medium"> Medium</label>
        <label><input type="radio" name="f-diff" value="hard"> Hard</label>
      </div>
    </div>

    <div class="filter-group">
      <label>Max Cook Time: <span id="cook-val">Any</span></label>
      <input type="range" id="f-cook" min="5" max="240" step="5" value="240">
    </div>
  </div>

  <!-- ── Recipe Grid ───────────────────────────────── -->
  <div id="recipe-grid" class="recipe-grid">
    <?php foreach ($recipes as $recipe): ?>
      <?php require ROOT . '/app/views/partials/recipe-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($recipes)): ?>
      <p class="empty-state" id="empty-msg">No recipes found.</p>
    <?php endif; ?>
  </div>

  <div id="loading-indicator" class="loading-spinner" style="display:none">Loading…</div>
</main>

<script>
const BASE = '<?= defined("APP_BASE") ? APP_BASE : "" ?>';
(function () {
  const grid       = document.getElementById('recipe-grid');
  const searchInput= document.getElementById('search-input');
  const cookSlider = document.getElementById('f-cook');
  const cookVal    = document.getElementById('cook-val');
  const spinner    = document.getElementById('loading-indicator');

  let   searchTimer = null;
  const MAX_COOK    = 240;

  // ── Render card HTML from JSON ────────────────────
  function starStr(avg) {
    let s = '';
    for (let i = 1; i <= 5; i++) s += (i <= Math.round(avg)) ? '★' : '☆';
    return s;
  }

  function renderCards(recipes) {
    if (!recipes.length) {
      grid.innerHTML = '<p class="empty-state">No recipes match your filters.</p>';
      return;
    }
    grid.innerHTML = recipes.map(r => `
      <article class="recipe-card">
        <a href="${r.url}">
          <div class="card-img-wrap">
            ${r.image
              ? `<img src="${r.image}" alt="${r.title}">`
              : '<div class="card-img-placeholder">🍽️</div>'}
          </div>
          <div class="card-body">
            <span class="badge badge-${r.difficulty}">${r.difficulty.charAt(0).toUpperCase()+r.difficulty.slice(1)}</span>
            <h3>${r.title}</h3>
            <p class="card-meta">${r.category_name} · ⏱ ${r.cook_time_mins} min</p>
            <p class="card-rating">
              <span class="stars">${starStr(r.avg_rating)}</span>
              <span class="rating-num">${r.avg_rating}</span>
              <span class="review-count">(${r.review_count})</span>
            </p>
          </div>
        </a>
      </article>
    `).join('');
  }

  // ── Fetch with filters ────────────────────────────
  async function fetchFiltered() {
    const cuisine    = document.getElementById('f-cuisine').value;
    const diet       = document.getElementById('f-diet').value;
    const diffRadio  = document.querySelector('input[name="f-diff"]:checked');
    const difficulty = diffRadio ? diffRadio.value : '';
    const maxCook    = parseInt(cookSlider.value);

    const params = new URLSearchParams();
    if (cuisine)    params.set('cuisine_id', cuisine);
    if (diet)       params.set('diet',       diet);
    if (difficulty) params.set('difficulty', difficulty);
    if (maxCook < MAX_COOK) params.set('max_cook', maxCook);

    spinner.style.display = 'block';
    try {
      const res  = await fetch(BASE + '/api/recipes?' + params.toString());
      const data = await res.json();
      renderCards(data.recipes || []);
    } catch (e) {
      grid.innerHTML = '<p class="empty-state">Error loading recipes.</p>';
    }
    spinner.style.display = 'none';
  }

  // ── Live search ───────────────────────────────────
  async function liveSearch(q) {
    if (q.trim() === '') { fetchFiltered(); return; }
    spinner.style.display = 'block';
    try {
      const res  = await fetch(BASE + '/api/recipes/search?q=' + encodeURIComponent(q));
      const data = await res.json();
      renderCards(data.recipes || []);
    } catch (e) {
      grid.innerHTML = '<p class="empty-state">Error loading results.</p>';
    }
    spinner.style.display = 'none';
  }

  // ── Event listeners ───────────────────────────────
  document.getElementById('f-cuisine').addEventListener('change', fetchFiltered);
  document.getElementById('f-diet').addEventListener('change', fetchFiltered);
  document.querySelectorAll('input[name="f-diff"]').forEach(r =>
    r.addEventListener('change', fetchFiltered)
  );

  cookSlider.addEventListener('input', () => {
    const v = parseInt(cookSlider.value);
    cookVal.textContent = v >= MAX_COOK ? 'Any' : v + ' min';
  });
  cookSlider.addEventListener('change', fetchFiltered);

  searchInput.addEventListener('input', e => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => liveSearch(e.target.value), 350);
  });
})();
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>