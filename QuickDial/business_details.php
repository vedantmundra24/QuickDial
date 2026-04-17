<?php

// business details

require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: search.php'); exit; }

// business

$stmt = $pdo->prepare("
    SELECT b.*, c.name AS category_name, c.icon AS category_icon
    FROM businesses b
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.id = ? AND b.status = 'approved'
");
$stmt->execute([$id]);
$biz = $stmt->fetch();
if (!$biz) { header('Location: search.php'); exit; }

// reviews

$revStmt = $pdo->prepare("SELECT * FROM reviews WHERE business_id = ? ORDER BY created_at DESC");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();
$avgRating = count($reviews) ? round(array_sum(array_column($reviews,'rating')) / count($reviews), 1) : 0;

// review submit

$reviewError = ''; $reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rName    = trim($_POST['reviewer_name']  ?? '');
    $rEmail   = trim($_POST['reviewer_email'] ?? '');
    $rating   = (int)($_POST['rating']        ?? 0);
    $comment  = trim($_POST['comment']        ?? '');
    $userId   = $_SESSION['user_id'] ?? null;

    if (!$rName || !$rating) {
        $reviewError = 'Name and rating are required.';
    } elseif ($rating < 1 || $rating > 5) {
        $reviewError = 'Rating must be between 1 and 5.';
    } else {
        $ins = $pdo->prepare("
            INSERT INTO reviews (business_id, user_id, reviewer_name, reviewer_email, rating, comment)
            VALUES (?,?,?,?,?,?)
        ");
        $ins->execute([$id, $userId, $rName, $rEmail, $rating, $comment]);
        $reviewSuccess = 'Your review has been posted!';
        
        // reloading

        header("Location: business_details.php?id=$id#reviews"); exit;
    }
}

$pageTitle = $biz['name'];
$pageDesc  = substr(strip_tags($biz['description'] ?? ''), 0, 155) ?: 'View details, contact info, and reviews for '.$biz['name'].' on QuickDial.';

function starHtml(float $r, $size='1rem'): string {
    $h='';
    for($i=1;$i<=5;$i++) $h .= $i<=$r ? '<i class="fa-solid fa-star" style="color:var(--warning)"></i>' : '<i class="fa-regular fa-star" style="color:#d1d5db"></i>';
    return "<span style='font-size:$size'>$h</span>";
}

require_once 'includes/header.php';
?>
<!-- hero -->

<div class="detail-hero">
  <div class="container">
    <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:.5rem">
      <span class="badge badge-primary" style="font-size:.85rem;padding:.3rem .9rem">
        <i class="fa-solid <?= htmlspecialchars($biz['category_icon']) ?>"></i>
        <?= htmlspecialchars($biz['category_name']) ?>
      </span>
      <?php if ($biz['featured']): ?>
        <span class="featured-badge">⭐ Featured</span>
      <?php endif; ?>
    </div>
    <h1><?= htmlspecialchars($biz['name']) ?></h1>
    <div class="detail-meta">
      <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($biz['address']) ?>, <?= htmlspecialchars($biz['city']) ?></span>
      <span><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($biz['phone']) ?></span>
      <span><?= starHtml($avgRating,'1rem') ?> <?= $avgRating ?> (<?= count($reviews) ?> reviews)</span>
      <?php if ($biz['opening_time'] && $biz['closing_time']): ?>
        <span><i class="fa-solid fa-clock"></i>
          <?= date('g:i A', strtotime($biz['opening_time'])) ?> – <?= date('g:i A', strtotime($biz['closing_time'])) ?>
        </span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- body -->

<section class="detail-body">
  <div class="container">
    <div class="detail-grid">
   
    <!-- left -->

      <div>
        <?php if ($biz['description']): ?>
        <div class="detail-section">
          <h3><i class="fa-solid fa-circle-info"></i> About</h3>
          <p style="color:var(--text-muted);line-height:1.8"><?= nl2br(htmlspecialchars($biz['description'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- reviews -->

        <div class="detail-section" id="reviews">
          <h3><i class="fa-solid fa-star"></i> Reviews
            <span style="font-size:.85rem;font-weight:400;color:var(--text-muted);margin-left:.5rem">(<?= count($reviews) ?>)</span>
          </h3>

          <?php if ($reviewSuccess): ?>
            <div class="alert alert-success" data-dismiss="5000"><i class="fa-solid fa-circle-check"></i> <?= $reviewSuccess ?></div>
          <?php endif; ?>

          <?php if (empty($reviews)): ?>
            <p style="color:var(--text-muted);font-size:.9rem">No reviews yet. Be the first to review!</p>
          <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
              <div class="review-card">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.4rem">
                  <div>
                    <span class="reviewer-name"><?= htmlspecialchars($rev['reviewer_name']) ?></span>
                    <span class="review-date" style="margin-left:.5rem"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                  </div>
                  <?= starHtml($rev['rating']) ?>
                </div>
                <?php if ($rev['comment']): ?>
                  <p class="review-comment"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- review form -->

          <div class="review-form-section" style="margin-top:1.5rem">
            <h4 style="font-size:.95rem;font-weight:700;margin-bottom:1rem;color:var(--dark)">Write a Review</h4>
            <?php if ($reviewError): ?>
              <div class="alert alert-danger" style="margin-bottom:1rem"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($reviewError) ?></div>
            <?php endif; ?>
            <form method="POST" action="business_details.php?id=<?= $id ?>#reviews">
              <div class="form-row">
                <div class="form-group">
                  <label>Your Name *</label>
                  <input type="text" name="reviewer_name" class="form-control" required
                         value="<?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                </div>
                <div class="form-group">
                  <label>Email (optional)</label>
                  <input type="email" name="reviewer_email" class="form-control"
                         value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                </div>
              </div>
              <div class="form-group">
                <label>Rating *</label>
                <div class="star-rating">
                  <?php for ($s = 5; $s >= 1; $s--): ?>
                    <input type="radio" id="star<?= $s ?>" name="rating" value="<?= $s ?>">
                    <label for="star<?= $s ?>" title="<?= $s ?> star<?= $s>1?'s':'' ?>">&#9733;</label>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="form-group">
                <label>Comment</label>
                <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience…"></textarea>
              </div>
              <button type="submit" name="submit_review" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Post Review
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- sidebar -->

      <div>
        <div class="contact-sidebar">
          <h3><i class="fa-solid fa-address-book" style="color:var(--primary)"></i> Contact Info</h3>
          <div class="sidebar-phone">
            <i class="fa-solid fa-phone"></i>
            <?= htmlspecialchars($biz['phone']) ?>
          </div>
          <div style="margin-top:1rem" class="info-list">
            <ul>
              <li><i class="fa-solid fa-location-dot"></i>
                <?= htmlspecialchars($biz['address']) ?>
                <?php if ($biz['city']): ?>, <?= htmlspecialchars($biz['city']) ?><?php endif; ?>
                <?php if ($biz['pincode']): ?> – <?= htmlspecialchars($biz['pincode']) ?><?php endif; ?>
              </li>
              <?php if ($biz['email']): ?>
                <li><i class="fa-solid fa-envelope"></i> <a href="mailto:<?= htmlspecialchars($biz['email']) ?>"><?= htmlspecialchars($biz['email']) ?></a></li>
              <?php endif; ?>
              <?php if ($biz['website']): ?>
                <li><i class="fa-solid fa-globe"></i> <a href="<?= htmlspecialchars($biz['website']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($biz['website']) ?></a></li>
              <?php endif; ?>
              <?php if ($biz['opening_time'] && $biz['closing_time']): ?>
                <li><i class="fa-solid fa-clock"></i>
                  <?= date('g:i A', strtotime($biz['opening_time'])) ?> – <?= date('g:i A', strtotime($biz['closing_time'])) ?>
                </li>
              <?php endif; ?>
            </ul>
          </div>
          <a href="tel:<?= htmlspecialchars($biz['phone']) ?>" class="btn btn-primary w-100 btn-lg" style="margin-top:1.2rem">
            <i class="fa-solid fa-phone"></i> Call Now
          </a>
          <div class="map-placeholder">
            <div><i class="fa-solid fa-map-location-dot" style="font-size:2rem;margin-bottom:.5rem;display:block"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
