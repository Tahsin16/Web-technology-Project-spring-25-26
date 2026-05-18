<?php
/**
 * Recipe Card partial
 * Expects: $recipe (array with id, title, category_name, difficulty,
 *          cook_time_mins, avg_rating, review_count, featured_image_path)
 */
$avg = round((float)($recipe['avg_rating'] ?? 0), 1);
$stars = '';
for ($i = 1; $i <= 5; $i++) {
    $stars .= $i <= $avg ? '★' : '☆';
}
?>
<article class="recipe-card">
  <a href="<?= url('/recipes/' . $recipe['id']) ?>">
    <div class="card-img-wrap">
      <?php if (!empty($recipe['featured_image_path'])): ?>
        <img src="<?= (defined('APP_BASE') ? APP_BASE : '') . '/uploads/recipes/' . basename(htmlspecialchars($recipe['featured_image_path'])) ?>" alt="<?= htmlspecialchars($recipe['title']) ?>">
      <?php else: ?>
        <div class="card-img-placeholder">🍽️</div>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <span class="badge badge-<?= htmlspecialchars($recipe['difficulty']) ?>"><?= ucfirst($recipe['difficulty']) ?></span>
      <h3><?= htmlspecialchars($recipe['title']) ?></h3>
      <p class="card-meta">
        <?= htmlspecialchars($recipe['category_name'] ?? '') ?> ·
        ⏱ <?= (int)$recipe['cook_time_mins'] ?> min
      </p>
      <p class="card-rating">
        <span class="stars"><?= $stars ?></span>
        <span class="rating-num"><?= $avg ?></span>
        <span class="review-count">(<?= (int)($recipe['review_count'] ?? 0) ?>)</span>
      </p>
    </div>
  </a>
</article>