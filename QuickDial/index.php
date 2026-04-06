<?php
/**
 * QuickDial – Homepage
 * File: index.php
 */
$pageTitle = 'Find Local Businesses Near You';
$pageDesc  = 'QuickDial – Discover restaurants, hospitals, hotels, salons and more in your city.';
require_once 'config/db_connect.php';
require_once 'includes/header.php';

// Fetch all categories
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Fetch featured businesses with avg rating
$featuredStmt = $pdo->query("
    SELECT b.id, b.name, b.phone, b.address, b.city, b.featured,
           c.name AS category_name, c.icon AS category_icon,
           COALESCE(ROUND(AVG(r.rating),1),0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM businesses b
    LEFT JOIN categories c ON b.category_id = c.id
    LEFT JOIN reviews r ON b.id = r.business_id
    WHERE b.status = 'approved' AND b.featured = 1
    GROUP BY b.id
    LIMIT 6
");
$featured = $featuredStmt->fetchAll();

// Stats
$totalBiz   = $pdo->query("SELECT COUNT(*) FROM businesses WHERE status='approved'")->fetchColumn();
$totalCats  = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCities = $pdo->query("SELECT COUNT(DISTINCT city) FROM businesses WHERE status='approved'")->fetchColumn();

// Helper: render stars
function starHtml(float $r): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $r ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star star-empty"></i>';
    }
    return $html;
}
?>

<!-- ── HERO ── -->
<section class="hero">
  <div class="container hero-content">
    <h1>Find the Best <span>Local Businesses</span><br>Near You</h1>
    <p>Restaurants · Hospitals · Hotels · Salons · and 500+ categories</p>

    <form class="search-box" action="search.php" method="GET" id="heroSearch">
      <i class="fa-solid fa-magnifying-glass" style="color:var(--text-muted);flex-shrink:0"></i>
      <input type="text" name="q" id="searchQ" placeholder="Search businesses, services…" autocomplete="off">
      <div class="search-divider"></div>
      <select name="category" id="searchCat">
        <option value="">All Categories</option>
        <?php foreach ($cats as $cat): ?>
          <option value="<?= htmlspecialchars($cat['name']) ?>">
            <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="search-divider"></div>
      <input type="text" name="city" id="searchCity" placeholder="City" style="flex:.8">
      <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>

    <div class="popular-cats">
      <p>Popular Categories</p>
      <div class="cat-pills">
        <?php foreach (array_slice($cats, 0, 10) as $cat): ?>
          <span class="cat-pill" data-cat="<?= htmlspecialchars($cat['name']) ?>">
            <i class="fa-solid <?= htmlspecialchars($cat['icon']) ?>"></i>
            <?= htmlspecialchars($cat['name']) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── STATS ── -->
<div class="stats-bar">
  <div class="container">
    <div class="stat-item"><div class="num"><?= number_format($totalBiz) ?>+</div><div class="lbl">Businesses</div></div>
    <div class="stat-item"><div class="num"><?= $totalCats ?>+</div><div class="lbl">Categories</div></div>
    <div class="stat-item"><div class="num"><?= $totalCities ?>+</div><div class="lbl">Cities</div></div>
    <div class="stat-item"><div class="num"><?= number_format($totalUsers) ?>+</div><div class="lbl">Users</div></div>
  </div>
</div>

<!-- ── CATEGORIES ── -->
<section class="categories-section">
  <div class="container">
    <div class="section-header">
      <h2>Browse by Category</h2>
      <p>Explore businesses across all popular service categories</p>
      <div class="underline"></div>
    </div>
    <div class="cat-grid">
      <?php foreach ($cats as $cat): ?>
        <div class="cat-card" data-cat="<?= htmlspecialchars($cat['name']) ?>">
          <i class="fa-solid <?= htmlspecialchars($cat['icon']) ?>"></i>
          <p><?= htmlspecialchars($cat['name']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── FEATURED BUSINESSES ── -->
<?php if (!empty($featured)): ?>
<section class="featured-section">
  <div class="container">
    <div class="section-header">
      <h2>Featured Businesses</h2>
      <p>Top-rated and most popular businesses near you</p>
      <div class="underline"></div>
    </div>
    <div class="business-grid">
      <?php foreach ($featured as $b): ?>
        <div class="business-card">
          <div class="card-header-strip"></div>
          <div class="card-body">
            <div class="card-meta">
              <span class="card-category">
                <i class="fa-solid <?= htmlspecialchars($b['category_icon']) ?>"></i>
                <?= htmlspecialchars($b['category_name']) ?>
              </span>
              <?php if ($b['featured']): ?>
                <span class="featured-badge">⭐ Featured</span>
              <?php endif; ?>
            </div>
            <h3 class="card-title"><?= htmlspecialchars($b['name']) ?></h3>
            <p class="card-address">
              <i class="fa-solid fa-location-dot"></i>
              <?= htmlspecialchars($b['address']) ?>, <?= htmlspecialchars($b['city']) ?>
            </p>
            <p class="card-phone">
              <i class="fa-solid fa-phone"></i>
              <?= htmlspecialchars($b['phone']) ?>
            </p>
          </div>
          <div class="card-footer-strip">
            <div class="rating-row">
              <span class="stars"><?= starHtml((float)$b['avg_rating']) ?></span>
              <span class="rating-num"><?= $b['avg_rating'] ?></span>
              <span>(<?= $b['review_count'] ?> reviews)</span>
            </div>
            <a href="business_details.php?id=<?= $b['id'] ?>" class="btn btn-primary btn-sm">View</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
      <a href="search.php" class="btn btn-outline btn-lg">View All Businesses</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── HOW IT WORKS ── -->
<section class="how-section">
  <div class="container">
    <div class="section-header">
      <h2>How QuickDial Works</h2>
      <p>Find the right business in just three simple steps</p>
      <div class="underline"></div>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h3>Search</h3>
        <p>Enter a business name, category, or city to find what you need.</p>
      </div>
      <div class="step-card">
        <div class="step-icon"><i class="fa-solid fa-list-check"></i></div>
        <h3>Compare</h3>
        <p>Browse listings, read reviews, and compare ratings.</p>
      </div>
      <div class="step-card">
        <div class="step-icon"><i class="fa-solid fa-phone"></i></div>
        <h3>Connect</h3>
        <p>Call or get directions to the business directly from the listing.</p>
      </div>
      <div class="step-card">
        <div class="step-icon"><i class="fa-solid fa-star"></i></div>
        <h3>Review</h3>
        <p>Share your experience and help others make better choices.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section style="background:var(--primary);padding:4rem 0;text-align:center">
  <div class="container">
    <h2 style="color:#fff;font-size:2rem;font-weight:800;margin-bottom:.5rem">Own a Business?</h2>
    <p style="color:rgba(255,255,255,.8);margin-bottom:1.5rem;font-size:1.05rem">List it for free on QuickDial and reach thousands of customers nearby.</p>
    <a href="add_business.php" class="btn btn-lg" style="background:#fff;color:var(--primary);font-weight:700">
      <i class="fa-solid fa-plus"></i> Add Your Business Free
    </a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
