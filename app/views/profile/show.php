<?php $pageTitle = htmlspecialchars($user['name']) . ' — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="profile-page container">
  <div class="profile-header">
    <div class="avatar-wrap">
      <?php if (!empty($user['profile_pic_path'])): ?>
        <img src="<?= (defined('APP_BASE') ? APP_BASE : '') . '/' . htmlspecialchars($user['profile_pic_path']) ?>" class="avatar-lg" alt="Avatar">
      <?php else: ?>
        <div class="avatar-placeholder"><?= mb_strtoupper(mb_substr($user['name'],0,1)) ?></div>
      <?php endif; ?>
    </div>
    <div class="profile-info">
      <h1><?= htmlspecialchars($user['name']) ?></h1>
      <?php if (!empty($user['bio'])): ?>
        <p class="bio"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
      <?php endif; ?>
      <div class="diet-tags">
        <?php foreach ($dietPrefs as $pref): ?>
          <span class="diet-tag"><?= htmlspecialchars($pref) ?></span>
        <?php endforeach; ?>
      </div>
      <?php if ($isOwner): ?>
        <div class="profile-stats">
          <div class="stat"><strong><?= $recipeCount ?></strong><span>Recipes</span></div>
          <div class="stat"><strong><?= $savedCount ?></strong><span>Saved</span></div>
        </div>
        <a href="<?= url('/profile/edit') ?>" class="btn btn-outline">Edit Profile</a>
      <?php endif; ?>
    </div>
  </div>

  <h2><?= htmlspecialchars($user['name']) ?>'s Recipes</h2>
  <div class="recipe-grid">
    <?php if (empty($recipes)): ?>
      <p class="empty-state">No published recipes yet.</p>
    <?php else: ?>
      <?php foreach ($recipes as $recipe): ?>
        <?php require ROOT . '/app/views/partials/recipe-card.php'; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>