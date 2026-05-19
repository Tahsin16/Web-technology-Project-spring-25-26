<?php $pageTitle = 'Edit Profile — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<main class="container" style="max-width:640px">
  <h1>Edit Profile</h1>

  <form method="POST" action="<?= url('/profile/edit') ?>" enctype="multipart/form-data" novalidate>

    <div class="form-group">
      <label>Profile Picture <small>(JPEG/PNG, max 1 MB)</small></label>
      <?php if (!empty($user['profile_pic_path'])): ?>
        <img src="<?= (defined('APP_BASE') ? APP_BASE : '') . '/' . htmlspecialchars($user['profile_pic_path']) ?>" class="avatar-preview" alt="Current avatar">
      <?php endif; ?>
      <input type="file" name="profile_pic" accept="image/jpeg,image/png">
      <?php if (!empty($errors['profile_pic'])): ?><span class="error-msg"><?= $errors['profile_pic'] ?></span><?php endif; ?>
    </div>

    <div class="form-group">
      <label>Bio</label>
      <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label>Dietary Preferences</label>
      <div class="checkbox-group">
        <?php
        $allDiets = ['Vegetarian','Vegan','Gluten-Free','Dairy-Free','Keto','Halal'];
        foreach ($allDiets as $d):
          $checked = in_array($d, $dietPrefs) ? 'checked' : '';
        ?>
          <label class="checkbox-label">
            <input type="checkbox" name="dietary_prefs[]" value="<?= $d ?>" <?= $checked ?>>
            <?= $d ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <hr>
    <h3>Change Password <small>(leave blank to keep current)</small></h3>

    <div class="form-group <?= !empty($errors['current_password']) ? 'has-error' : '' ?>">
      <label>Current Password</label>
      <input type="password" name="current_password">
      <?php if (!empty($errors['current_password'])): ?><span class="error-msg"><?= $errors['current_password'] ?></span><?php endif; ?>
    </div>

    <div class="form-group <?= !empty($errors['new_password']) ? 'has-error' : '' ?>">
      <label>New Password</label>
      <input type="password" name="new_password" minlength="8">
      <?php if (!empty($errors['new_password'])): ?><span class="error-msg"><?= $errors['new_password'] ?></span><?php endif; ?>
    </div>

    <div class="form-group <?= !empty($errors['new_password2']) ? 'has-error' : '' ?>">
      <label>Confirm New Password</label>
      <input type="password" name="new_password2">
      <?php if (!empty($errors['new_password2'])): ?><span class="error-msg"><?= $errors['new_password2'] ?></span><?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="<?= url('/users/' . $_SESSION['user_id']) ?>" class="btn btn-outline">Cancel</a>
  </form>
</main>

<?php require ROOT . '/app/views/partials/footer.php'; ?>