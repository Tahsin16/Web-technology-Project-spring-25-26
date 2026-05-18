<?php
$isEdit    = !empty($recipe);
$pageTitle = ($isEdit ? 'Edit Recipe' : 'New Recipe') . ' — RecipeHub';
?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container recipe-form-page">
  <h1><?= $isEdit ? 'Edit Recipe' : 'Create a Recipe' ?></h1>

  <form method="POST"
        action="<?= $isEdit ? url('/recipes/' . $recipe['id'] . '/edit') : url('/recipes/create') ?>"
        enctype="multipart/form-data"
        novalidate>

    <!-- ── Part A: Core Details ──────────────────── -->
    <section class="form-section">
      <h2>Part A — Core Details</h2>

      <div class="form-group <?= !empty($errors['title']) ? 'has-error' : '' ?>">
        <label>Title *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($recipe['title'] ?? '') ?>" required>
        <?php if (!empty($errors['title'])): ?><span class="error-msg"><?= $errors['title'] ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3"><?= htmlspecialchars($recipe['description'] ?? '') ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group <?= !empty($errors['category_id']) ? 'has-error' : '' ?>">
          <label>Cuisine *</label>
          <select name="category_id" required>
            <option value="">Select cuisine…</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"
                <?= (int)($recipe['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['category_id'])): ?><span class="error-msg"><?= $errors['category_id'] ?></span><?php endif; ?>
        </div>

        <div class="form-group">
          <label>Diet Type</label>
          <select name="diet_type">
            <?php foreach (['Any','Vegetarian','Vegan','Gluten-Free','Keto'] as $dt): ?>
              <option value="<?= $dt ?>" <?= ($recipe['diet_type'] ?? 'Any') === $dt ? 'selected' : '' ?>>
                <?= $dt ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Difficulty</label>
        <div class="radio-group">
          <?php foreach (['easy','medium','hard'] as $d): ?>
            <label class="radio-label">
              <input type="radio" name="difficulty" value="<?= $d ?>"
                <?= ($recipe['difficulty'] ?? 'easy') === $d ? 'checked' : '' ?>>
              <?= ucfirst($d) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Prep Time (mins)</label>
          <input type="number" name="prep_time_mins" min="0"
                 value="<?= (int)($recipe['prep_time_mins'] ?? 0) ?>">
        </div>
        <div class="form-group">
          <label>Cook Time (mins)</label>
          <input type="number" name="cook_time_mins" min="0"
                 value="<?= (int)($recipe['cook_time_mins'] ?? 0) ?>">
        </div>
        <div class="form-group">
          <label>Servings</label>
          <input type="number" name="servings" min="1"
                 value="<?= (int)($recipe['servings'] ?? 1) ?>">
        </div>
      </div>

      <div class="form-group <?= !empty($errors['featured_image']) ? 'has-error' : '' ?>">
        <label>Featured Image <small>(JPEG/PNG, max 3 MB)</small></label>
        <?php if (!empty($recipe['featured_image_path'])): ?>
          <img src="/<?= htmlspecialchars($recipe['featured_image_path']) ?>"
               class="img-preview" alt="Current image">
        <?php endif; ?>
        <input type="file" name="featured_image" accept="image/jpeg,image/png">
        <?php if (!empty($errors['featured_image'])): ?><span class="error-msg"><?= $errors['featured_image'] ?></span><?php endif; ?>
      </div>
    </section>

    <!-- ── Part B: Dynamic Ingredient Rows ──────── -->
    <section class="form-section">
      <h2>Part B — Ingredients</h2>
      <div id="ingredients-list">
        <?php foreach ($ingredients as $i => $ing): ?>
        <div class="ingredient-row" data-index="<?= $i ?>">
          <input type="text"   name="ingredients[<?= $i ?>][name]"
                 placeholder="Ingredient name"
                 value="<?= htmlspecialchars($ing['name'] ?? '') ?>">
          <input type="text"   name="ingredients[<?= $i ?>][quantity]"
                 placeholder="Qty"
                 value="<?= htmlspecialchars($ing['quantity'] ?? '') ?>">
          <input type="text"   name="ingredients[<?= $i ?>][unit]"
                 placeholder="Unit"
                 value="<?= htmlspecialchars($ing['unit'] ?? '') ?>">
          <button type="button" class="btn btn-sm btn-danger remove-ingredient" title="Remove">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" id="add-ingredient" class="btn btn-outline btn-sm">+ Add Ingredient</button>
    </section>

    <!-- ── Part C: Fixed Step Fields + Publish ─── -->
    <section class="form-section">
      <h2>Part C — Steps</h2>
      <div id="steps-list">
        <?php for ($s = 0; $s < 10; $s++): ?>
        <div class="step-row <?= $s >= 2 ? 'step-hidden' : '' ?>" id="step-row-<?= $s ?>">
          <label>Step <?= $s + 1 ?></label>
          <textarea name="steps[<?= $s ?>]"
                    rows="2"
                    placeholder="Describe step <?= $s + 1 ?>…"><?= htmlspecialchars($steps[$s] ?? '') ?></textarea>
        </div>
        <?php endfor; ?>
      </div>
      <button type="button" id="add-step" class="btn btn-outline btn-sm">+ Add Step</button>
    </section>

    <!-- Publish toggle -->
    <section class="form-section">
      <div class="radio-group">
        <label class="radio-label">
          <input type="radio" name="status" value="draft"
            <?= ($recipe['status'] ?? 'published') === 'draft' ? 'checked' : '' ?>>
          Save as Draft
        </label>
        <label class="radio-label">
          <input type="radio" name="status" value="published"
            <?= ($recipe['status'] ?? 'published') === 'published' ? 'checked' : '' ?>>
          Publish
        </label>
      </div>
    </section>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">
        <?= $isEdit ? 'Update Recipe' : 'Save Recipe' ?>
      </button>
      <a href="<?= $isEdit ? '/recipes/'.$recipe['id'] : '/my-recipes' ?>" class="btn btn-outline">Cancel</a>
    </div>
  </form>
</main>

<script>
// ── Dynamic Ingredient Rows ───────────────────────────
(function () {
  const list   = document.getElementById('ingredients-list');
  const addBtn = document.getElementById('add-ingredient');

  function nextIndex() {
    const rows = list.querySelectorAll('.ingredient-row');
    return rows.length;
  }

  addBtn.addEventListener('click', () => {
    const i   = nextIndex();
    const row = document.createElement('div');
    row.className = 'ingredient-row';
    row.dataset.index = i;
    row.innerHTML = `
      <input type="text"  name="ingredients[${i}][name]"     placeholder="Ingredient name">
      <input type="text"  name="ingredients[${i}][quantity]" placeholder="Qty">
      <input type="text"  name="ingredients[${i}][unit]"     placeholder="Unit">
      <button type="button" class="btn btn-sm btn-danger remove-ingredient" title="Remove">✕</button>
    `;
    list.appendChild(row);
  });

  list.addEventListener('click', e => {
    if (e.target.classList.contains('remove-ingredient')) {
      e.target.closest('.ingredient-row').remove();
      // Re-index remaining rows
      list.querySelectorAll('.ingredient-row').forEach((row, idx) => {
        row.dataset.index = idx;
        row.querySelectorAll('input').forEach(input => {
          input.name = input.name.replace(/\[\d+\]/, `[${idx}]`);
        });
      });
    }
  });
})();

// ── Show/hide Step textareas ───────────────────────────
(function () {
  const addBtn    = document.getElementById('add-step');
  const stepsList = document.getElementById('steps-list');
  let   shown     = <?= max(2, count(array_filter($steps ?? [], fn($v) => trim($v) !== ''))) ?>;

  addBtn.addEventListener('click', () => {
    if (shown >= 10) { addBtn.disabled = true; addBtn.textContent = 'Max 10 steps'; return; }
    const next = document.getElementById('step-row-' + shown);
    if (next) {
      next.classList.remove('step-hidden');
      shown++;
    }
    if (shown >= 10) { addBtn.disabled = true; addBtn.textContent = 'Max 10 steps'; }
  });
})();
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>