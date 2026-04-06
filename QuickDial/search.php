<?php
/**
 * QuickDial – Search Results
 * File: search.php
 */
$pageTitle = 'Search Businesses';
$pageDesc  = 'Search and find verified local businesses on QuickDial.';
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ── Inputs ──
$q        = trim($_GET['q']        ?? '');
$category = trim($_GET['category'] ?? '');
$city     = trim($_GET['city']     ?? '');
$sort     = $_GET['sort'] ?? 'rating';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 8;
$offset   = ($page - 1) * $perPage;

// ── Build query ──
$where  = ["b.status = 'approved'"];
$params = [];

if ($q !== '') {
    $where[]  = "(b.name LIKE ? OR b.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($category !== '') {
    $where[]  = "c.name = ?";
    $params[] = $category;
}
if ($city !== '') {
    $where[]  = "b.city LIKE ?";
    $params[] = "%$city%";
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// Count total
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM businesses b
    LEFT JOIN categories c ON b.category_id = c.id
    $whereSQL
");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Sort
$orderSQL = match($sort) {
    'name'   => 'b.name ASC',
    'newest' => 'b.created_at DESC',
    default  => 'avg_rating DESC',
};

// Fetch results
$sql = "
    SELECT b.id, b.name, b.phone, b.address, b.city, b.featured,
           c.name AS category_name, c.icon AS category_icon,
           COALESCE(ROUND(AVG(r.rating),1), 0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM businesses b
    LEFT JOIN categories c ON b.category_id = c.id
    LEFT JOIN reviews r ON b.id = r.business_id
    $whereSQL
    GROUP BY b.id, b.name, b.phone, b.address, b.city, b.featured, c.name, c.icon
    ORDER BY $orderSQL
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Categories for filter
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Helper
function starHtml(float $r): string {
    $h = '';
    for ($i=1;$i<=5;$i++) $h .= $i<=$r ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star star-empty"></i>';
    return $h;
}

require_once 'includes/header.php';
?>

<!-- ── Inline search bar ── -->
<div class="search-bar-inline">
  <div class="container">
    <form method="GET" action="search.php" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center">
      <input type="text" name="q" class="form-control" placeholder="Business or keyword…"
             value="<?= htmlspecialchars($q) ?>" style="flex:2;min-width:160px">
      <select name="category" class="form-control" style="flex:1;min-width:150px">
        <option value="">All Categories</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= htmlspecialchars($c['name']) ?>" <?= $category===$c['name']?'selected':'' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="city" class="form-control" placeholder="City"
             value="<?= htmlspecialchars($city) ?>" style="flex:.8;min-width:110px">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
  </div>
</div>

<section class="search-results-page">
  <div class="container">
    <div class="results-header">
      <p class="results-count">
        Showing <strong><?= $total ?></strong> result<?= $total!==1?'s':'' ?>
        <?php if ($q): ?> for "<strong><?= htmlspecialchars($q) ?></strong>"<?php endif; ?>
        <?php if ($category): ?> in <strong><?= htmlspecialchars($category) ?></strong><?php endif; ?>
        <?php if ($city): ?> near <strong><?= htmlspecialchars($city) ?></strong><?php endif; ?>
      </p>
      <div style="display:flex;gap:.6rem;align-items:center">
        <label for="sortSelect" style="font-size:.85rem;color:var(--text-muted)">Sort by:</label>
        <select id="sortSelect" class="sort-select">
          <option value="rating"  <?= $sort==='rating'?'selected':'' ?>>Top Rated</option>
          <option value="name"    <?= $sort==='name'?'selected':'' ?>>Name A–Z</option>
          <option value="newest"  <?= $sort==='newest'?'selected':'' ?>>Newest</option>
        </select>
      </div>
    </div>

    <?php if (empty($results)): ?>
      <div class="no-results">
        <i class="fa-solid fa-store-slash"></i>
        <h3>No businesses found</h3>
        <p style="color:var(--text-muted);margin:.5rem 0 1.5rem">Try a different keyword, category, or city.</p>
        <a href="search.php" class="btn btn-outline">Browse All</a>
      </div>
    <?php else: ?>
      <div class="business-list">
        <?php foreach ($results as $b): ?>
          <div class="biz-list-card">
            <div class="biz-icon">
              <i class="fa-solid <?= htmlspecialchars($b['category_icon']) ?>"></i>
            </div>
            <div class="biz-info">
              <div class="biz-name">
                <a href="business_details.php?id=<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></a>
                <?php if ($b['featured']): ?>
                  <span class="featured-badge" style="font-size:.68rem;margin-left:.4rem">Featured</span>
                <?php endif; ?>
              </div>
              <span class="badge badge-primary" style="margin-bottom:.4rem"><?= htmlspecialchars($b['category_name']) ?></span>
              <div class="biz-meta">
                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($b['address']) ?>, <?= htmlspecialchars($b['city']) ?></span>
                <span><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($b['phone']) ?></span>
                <span>
                  <span class="stars"><?= starHtml((float)$b['avg_rating']) ?></span>
                  <?= $b['avg_rating'] ?> (<?= $b['review_count'] ?> reviews)
                </span>
              </div>
            </div>
            <div class="biz-actions">
              <a href="business_details.php?id=<?= $b['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
              <a href="tel:<?= htmlspecialchars($b['phone']) ?>" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-phone"></i> Call
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <?php
        $baseUrl = 'search.php?' . http_build_query(['q'=>$q,'category'=>$category,'city'=>$city,'sort'=>$sort]);
        ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="<?= $baseUrl ?>&page=<?= $page-1 ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
          <?php endif; ?>
          <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
            <a href="<?= $baseUrl ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <a href="<?= $baseUrl ?>&page=<?= $page+1 ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
