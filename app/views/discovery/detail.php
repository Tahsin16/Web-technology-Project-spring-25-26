<?php $pageTitle = htmlspecialchars($recipe['title']) . ' — RecipeHub'; ?>
<?php require ROOT . '/app/views/partials/head.php'; ?>
<?php require ROOT . '/app/views/partials/navbar.php'; ?>

<?php
$avg   = round((float)($avgData['avg_rating'] ?? 0), 1);
$count = (int)($avgData['count'] ?? 0);
function renderStars(float $avg, string $size = ''): string {
    $s = '';
    for ($i = 1; $i <= 5; $i++) $s .= $i <= $avg ? '★' : '☆';
    return "<span class=\"stars $size\">$s</span>";
}
?>

<main class="container detail-page">

  <!-- Hero -->
  <div class="detail-hero">
    <?php if (!empty($recipe['featured_image_path'])): ?>
      <img src="<?= (defined('APP_BASE') ? APP_BASE : '') . '/uploads/recipes/' . basename(htmlspecialchars($recipe['featured_image_path'])) ?>" class="detail-hero-img" alt="<?= htmlspecialchars($recipe['title']) ?>">
    <?php endif; ?>
    <div class="detail-hero-body">
      <span class="badge badge-<?= $recipe['difficulty'] ?>"><?= ucfirst($recipe['difficulty']) ?></span>
      <h1><?= htmlspecialchars($recipe['title']) ?></h1>
      <p class="detail-meta">
        By <a href="<?= url('/users/' . $recipe['author_id']) ?>"><?= htmlspecialchars($recipe['author_name']) ?></a> ·
        <?= htmlspecialchars($recipe['category_name']) ?> ·
        <?= htmlspecialchars($recipe['diet_type']) ?>
      </p>
      <p class="detail-meta">
        ⏱ Prep: <?= (int)$recipe['prep_time_mins'] ?> min ·
        Cook: <?= (int)$recipe['cook_time_mins'] ?> min ·
        🍽 <?= (int)$recipe['servings'] ?> servings
      </p>

      <!-- Rating display widget -->
      <div class="rating-widget" id="rating-widget">
        <?= renderStars($avg, 'stars-lg') ?>
        <span class="avg-display" id="avg-display"><?= $avg ?></span>
        <span class="review-count-display" id="review-count-display">(<?= $count ?> reviews)</span>
      </div>

      <!-- Bookmark button -->
      <?php if ($userId): ?>
        <button id="bookmark-btn"
                class="btn btn-bookmark <?= $isBookmarked ? 'bookmarked' : '' ?>"
                data-recipe="<?= $recipe['id'] ?>">
          <?= $isBookmarked ? '🔖 Saved' : '🔖 Save Recipe' ?>
        </button>
      <?php endif; ?>

      <?php if (!empty($recipe['description'])): ?>
        <p class="detail-description"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="detail-body">
    <!-- Ingredients -->
    <aside class="ingredients-panel">
      <h2>Ingredients</h2>
      <ul class="ingredient-list">
        <?php foreach ($ingredients as $ing): ?>
          <li>
            <span class="ing-qty"><?= htmlspecialchars($ing['quantity']) ?> <?= htmlspecialchars($ing['unit']) ?></span>
            <?= htmlspecialchars($ing['name']) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <!-- Steps -->
    <section class="steps-section">
      <h2>Instructions</h2>
      <ol class="step-list">
        <?php foreach ($steps as $step): ?>
          <li><?= nl2br(htmlspecialchars($step['instruction'])) ?></li>
        <?php endforeach; ?>
      </ol>
    </section>
  </div>

  <!-- ── Reviews ──────────────────────────────────── -->
  <section class="reviews-section" id="reviews-section">
    <h2>Reviews</h2>

    <!-- Submit review form (logged-in, not author, not already reviewed) -->
    <?php if ($userId && !$isAuthor && !$userReviewed): ?>
    <div class="review-form-wrap" id="review-form-wrap">
      <h3>Leave a Review</h3>
      <div class="star-picker" id="star-picker">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <label>
            <input type="radio" name="star-rating" value="<?= $i ?>" style="display:none">
            <span class="star-btn" data-val="<?= $i ?>">☆</span>
          </label>
        <?php endfor; ?>
      </div>
      <span class="error-msg" id="review-error" style="display:none"></span>
      <textarea id="review-text" rows="3" placeholder="Share your experience…"></textarea>
      <button id="submit-review" class="btn btn-primary" data-recipe="<?= $recipe['id'] ?>">Submit Review</button>
    </div>
    <?php elseif ($isAuthor): ?>
      <p class="muted-note">You are the author of this recipe and cannot leave a review.</p>
    <?php elseif ($userReviewed): ?>
      <p class="muted-note">You have already reviewed this recipe.</p>
    <?php else: ?>
      <p class="muted-note"><a href="<?= url('/login') ?>">Log in</a> to leave a review.</p>
    <?php endif; ?>

    <!-- Existing reviews -->
    <div id="review-list">
      <?php foreach ($reviews as $rv): ?>
        <?php
        $rvStars = '';
        for ($i = 1; $i <= 5; $i++) $rvStars .= $i <= $rv['rating'] ? '★' : '☆';
        ?>
        <div class="review-item" id="review-<?= $rv['id'] ?>">
          <div class="reviewer-info">
            <?php if (!empty($rv['reviewer_avatar'])): ?>
              <img src="<?= (defined('APP_BASE') ? APP_BASE : '') . '/' . htmlspecialchars($rv['reviewer_avatar']) ?>" class="avatar-sm" alt="">
            <?php else: ?>
              <div class="avatar-sm avatar-init"><?= mb_strtoupper(mb_substr($rv['reviewer_name'],0,1)) ?></div>
            <?php endif; ?>
            <strong><?= htmlspecialchars($rv['reviewer_name']) ?></strong>
            <span class="stars"><?= $rvStars ?></span>
            <small class="muted"><?= $rv['created_at'] ?></small>
          </div>
          <p><?= nl2br(htmlspecialchars($rv['review_text'])) ?></p>

          <?php if (!empty($rv['reply_text'])): ?>
            <div class="author-reply">
              <strong>Author replied:</strong>
              <p><?= nl2br(htmlspecialchars($rv['reply_text'])) ?></p>
            </div>
          <?php endif; ?>

          <!-- Reply button — visible to recipe author only -->
          <?php if ($isAuthor && empty($rv['reply_text'])): ?>
            <button class="btn btn-sm btn-outline reply-btn" data-review="<?= $rv['id'] ?>">Reply</button>
            <div class="reply-form" id="reply-form-<?= $rv['id'] ?>" style="display:none">
              <textarea class="reply-text" rows="2" placeholder="Your reply…"></textarea>
              <button class="btn btn-sm btn-primary submit-reply" data-review="<?= $rv['id'] ?>">Post Reply</button>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<script>
const BASE = '<?= defined("APP_BASE") ? APP_BASE : "" ?>';
const RECIPE_ID = <?= (int)$recipe['id'] ?>;

// ── Bookmark toggle ───────────────────────────────────
const bookmarkBtn = document.getElementById('bookmark-btn');
if (bookmarkBtn) {
  bookmarkBtn.addEventListener('click', async () => {
    bookmarkBtn.disabled = true;
    try {
      const res  = await fetch(BASE + '/api/bookmarks/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ recipe_id: RECIPE_ID }),
      });
      const data = await res.json();
      if (typeof data.bookmarked !== 'undefined') {
        bookmarkBtn.textContent = data.bookmarked ? '🔖 Saved' : '🔖 Save Recipe';
        bookmarkBtn.classList.toggle('bookmarked', data.bookmarked);
      }
    } catch (e) { alert('Bookmark error.'); }
    bookmarkBtn.disabled = false;
  });
}

// ── Star picker ───────────────────────────────────────
const starBtns = document.querySelectorAll('.star-btn');
function paintStars(val) {
  starBtns.forEach(s => {
    s.textContent = +s.dataset.val <= val ? '★' : '☆';
    s.classList.toggle('selected', +s.dataset.val <= val);
  });
}
starBtns.forEach(s => {
  s.addEventListener('mouseover', () => paintStars(+s.dataset.val));
  s.addEventListener('click', () => {
    const radio = s.previousElementSibling;
    radio.checked = true;
    paintStars(+s.dataset.val);
  });
});
document.querySelector('.star-picker')?.addEventListener('mouseleave', () => {
  const checked = document.querySelector('input[name="star-rating"]:checked');
  paintStars(checked ? +checked.value : 0);
});

// ── Submit review ─────────────────────────────────────
const submitBtn = document.getElementById('submit-review');
if (submitBtn) {
  submitBtn.addEventListener('click', async () => {
    const ratingInput = document.querySelector('input[name="star-rating"]:checked');
    const text        = document.getElementById('review-text').value.trim();
    const errEl       = document.getElementById('review-error');

    if (!ratingInput) { errEl.textContent = 'Please select a star rating.'; errEl.style.display=''; return; }
    errEl.style.display = 'none';
    submitBtn.disabled  = true;

    try {
      const res  = await fetch(BASE + '/api/reviews', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ recipe_id: RECIPE_ID, rating: +ratingInput.value, review_text: text }),
      });
      const data = await res.json();

      if (!res.ok) {
        errEl.textContent = data.error || 'Error submitting review.';
        errEl.style.display = '';
        submitBtn.disabled = false;
        return;
      }

      // Append new review
      const rv       = data.review;
      const rvStars  = '★'.repeat(rv.rating) + '☆'.repeat(5 - rv.rating);
      const avatarHtml = rv.reviewer_avatar
        ? `<img src="${BASE}/${rv.reviewer_avatar}" class="avatar-sm" alt="">`
        : `<div class="avatar-sm avatar-init">${rv.reviewer_name.charAt(0).toUpperCase()}</div>`;
      const html = `
        <div class="review-item" id="review-${rv.id}">
          <div class="reviewer-info">
            ${avatarHtml}
            <strong>${rv.reviewer_name}</strong>
            <span class="stars">${rvStars}</span>
            <small class="muted">${rv.created_at}</small>
          </div>
          <p>${rv.review_text ? rv.review_text.replace(/\n/g,'<br>') : ''}</p>
        </div>`;
      document.getElementById('review-list').insertAdjacentHTML('afterbegin', html);

      // Update avg rating widget
      updateRatingWidget(data.new_avg, data.count);

      // Hide form
      document.getElementById('review-form-wrap').style.display = 'none';

    } catch (e) {
      errEl.textContent = 'Network error.';
      errEl.style.display = '';
      submitBtn.disabled = false;
    }
  });
}

function updateRatingWidget(newAvg, count) {
  const avgDisplay   = document.getElementById('avg-display');
  const countDisplay = document.getElementById('review-count-display');
  if (avgDisplay)   avgDisplay.textContent   = newAvg;
  if (countDisplay) countDisplay.textContent = `(${count} reviews)`;
  // Update stars
  const starsEl = document.querySelector('#rating-widget .stars');
  if (starsEl) {
    starsEl.textContent = '★'.repeat(Math.round(newAvg)) + '☆'.repeat(5 - Math.round(newAvg));
  }
}

// ── Author reply ──────────────────────────────────────
document.querySelectorAll('.reply-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id   = btn.dataset.review;
    const form = document.getElementById('reply-form-' + id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  });
});

document.querySelectorAll('.submit-reply').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id   = btn.dataset.review;
    const text = btn.previousElementSibling.value.trim();
    if (!text) return;
    btn.disabled = true;
    try {
      const res  = await fetch(BASE + `/api/reviews/${id}/reply`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reply_text: text }),
      });
      const data = await res.json();
      if (res.ok) {
        const replyDiv = document.createElement('div');
        replyDiv.className = 'author-reply';
        replyDiv.innerHTML = `<strong>Author replied:</strong><p>${data.reply_text.replace(/\n/g,'<br>')}</p>`;
        document.getElementById('review-' + id).appendChild(replyDiv);
        document.getElementById('reply-form-' + id).style.display = 'none';
        document.querySelector(`.reply-btn[data-review="${id}"]`).style.display = 'none';
      }
    } catch (e) { alert('Error posting reply.'); }
    btn.disabled = false;
  });
});
</script>

<?php require ROOT . '/app/views/partials/footer.php'; ?>