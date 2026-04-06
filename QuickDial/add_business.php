<?php
/**
 * QuickDial – Add / List a Business
 * File: add_business.php
 */
$pageTitle = 'List Your Business';
$pageDesc  = 'Submit your business to QuickDial and get discovered by thousands.';
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error   = '';
$success = '';

/* ── Fetch categories for the dropdown ── */
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitise
    $userId      = $_SESSION['user_id'] ?? null;
    $catId       = (int)($_POST['category_id'] ?? 0);
    $name        = trim($_POST['name']        ?? '');
    $desc        = trim($_POST['description'] ?? '');
    $address     = trim($_POST['address']     ?? '');
    $city        = trim($_POST['city']        ?? '');
    $state       = trim($_POST['state']       ?? '');
    $pincode     = trim($_POST['pincode']     ?? '');
    $phone       = trim($_POST['phone']       ?? '');
    $email       = trim($_POST['email']       ?? '');
    $website     = trim($_POST['website']     ?? '');
    $openTime    = $_POST['opening_time']    ?? null;
    $closeTime   = $_POST['closing_time']    ?? null;

    // Validation
    if (empty($name) || empty($phone) || empty($address) || empty($city) || !$catId) {
        $error = 'Name, category, phone, address and city are required fields.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Phone must be a 10-digit number.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO businesses
                (user_id, category_id, name, description, address, city, state, pincode,
                 phone, email, website, opening_time, closing_time, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')
        ");
        $stmt->execute([
            $userId, $catId, $name, $desc, $address, $city, $state, $pincode,
            $phone, $email, $website,
            ($openTime  ?: null),
            ($closeTime ?: null)
        ]);
        $success = 'Your business has been submitted successfully! It will appear after admin approval.';
    }
}
require_once 'includes/header.php';
?>

<section class="form-page" style="align-items:flex-start;padding-top:2.5rem">
  <div class="form-card form-card-wide" style="max-width:860px;margin:0 auto">
    <div class="form-card-header">
      <i class="fa-solid fa-store"></i>
      <h2>List Your Business</h2>
      <p>Fill in the details below – listing is completely free</p>
    </div>
    <div class="form-body">
      <?php if ($error): ?>
        <div class="alert alert-danger" data-dismiss="6000"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="alert alert-info"><i class="fa-solid fa-circle-info"></i>
          <strong>Tip:</strong> <a href="login.php?redirect=add_business.php" style="color:var(--primary)">Login</a> or
          <a href="register.php" style="color:var(--primary)">create a free account</a> to manage your listings.
        </div>
      <?php endif; ?>

      <form id="bizForm" method="POST" action="add_business.php" novalidate>
        <h4 style="margin-bottom:1rem;font-size:.95rem;color:var(--primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
          <i class="fa-solid fa-circle-info"></i> Basic Information
        </h4>
        <div class="form-row">
          <div class="form-group">
            <label for="biz_name">Business Name *</label>
            <input type="text" id="biz_name" name="name" class="form-control"
                   placeholder="e.g. Spice Garden Restaurant"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label for="biz_category">Category *</label>
            <select id="biz_category" name="category_id" class="form-control" required>
              <option value="">— Select Category —</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (($_POST['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="biz_desc">Description</label>
          <textarea id="biz_desc" name="description" class="form-control"
                    placeholder="Briefly describe your business, services, and specialties…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <h4 style="margin:1.2rem 0 1rem;font-size:.95rem;color:var(--primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
          <i class="fa-solid fa-location-dot"></i> Location
        </h4>
        <div class="form-group">
          <label for="biz_address">Full Address *</label>
          <input type="text" id="biz_address" name="address" class="form-control"
                 placeholder="Street / Area / Landmark"
                 value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="biz_city">City *</label>
            <input type="text" id="biz_city" name="city" class="form-control"
                   placeholder="Mumbai" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label for="biz_state">State</label>
            <input type="text" id="biz_state" name="state" class="form-control"
                   placeholder="Maharashtra" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="biz_pincode">Pincode</label>
            <input type="text" id="biz_pincode" name="pincode" class="form-control"
                   placeholder="400001" value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>">
          </div>
        </div>

        <h4 style="margin:1.2rem 0 1rem;font-size:.95rem;color:var(--primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
          <i class="fa-solid fa-address-card"></i> Contact Details
        </h4>
        <div class="form-row">
          <div class="form-group">
            <label for="biz_phone">Phone Number *</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-phone"></i>
              <input type="tel" id="biz_phone" name="phone" class="form-control"
                     placeholder="10-digit mobile" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label for="biz_email">Email Address</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-envelope"></i>
              <input type="email" id="biz_email" name="email" class="form-control"
                     placeholder="business@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="biz_website">Website</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-globe"></i>
              <input type="url" id="biz_website" name="website" class="form-control"
                     placeholder="https://www.yourbusiness.com" value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
            </div>
          </div>
        </div>

        <h4 style="margin:1.2rem 0 1rem;font-size:.95rem;color:var(--primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
          <i class="fa-solid fa-clock"></i> Business Hours
        </h4>
        <div class="form-row">
          <div class="form-group">
            <label for="opening_time">Opening Time</label>
            <input type="time" id="opening_time" name="opening_time" class="form-control"
                   value="<?= htmlspecialchars($_POST['opening_time'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="closing_time">Closing Time</label>
            <input type="time" id="closing_time" name="closing_time" class="form-control"
                   value="<?= htmlspecialchars($_POST['closing_time'] ?? '') ?>">
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg" style="margin-top:.5rem">
          <i class="fa-solid fa-paper-plane"></i> Submit Business Listing
        </button>
        <p style="text-align:center;margin-top:.8rem;font-size:.82rem;color:var(--text-muted)">
          Your listing will be reviewed by our team before going live.
        </p>
      </form>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
