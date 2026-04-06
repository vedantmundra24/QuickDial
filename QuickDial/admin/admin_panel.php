<?php
/**
 * QuickDial – Admin Panel
 * File: admin/admin_panel.php
 * Handles: login, dashboard, businesses, messages
 */
session_start();
require_once '../config/db_connect.php';

/* ── Auth check ── */
$isLoggedIn = isset($_SESSION['admin_id']);

/* ── Logout ── */
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = []; session_destroy();
    header('Location: admin_panel.php'); exit;
}

/* ── Handle POST actions ── */
$msg = '';
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bizId  = (int)($_POST['biz_id'] ?? 0);

    if ($action === 'approve' && $bizId) {
        $pdo->prepare("UPDATE businesses SET status='approved' WHERE id=?")->execute([$bizId]);
        $msg = 'Business approved successfully.';
    } elseif ($action === 'reject' && $bizId) {
        $pdo->prepare("UPDATE businesses SET status='rejected' WHERE id=?")->execute([$bizId]);
        $msg = 'Business rejected.';
    } elseif ($action === 'delete_biz' && $bizId) {
        $pdo->prepare("DELETE FROM businesses WHERE id=?")->execute([$bizId]);
        $msg = 'Business deleted.';
    } elseif ($action === 'delete_review') {
        $rid = (int)($_POST['rev_id'] ?? 0);
        if ($rid) { $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$rid]); $msg = 'Review deleted.'; }
    } elseif ($action === 'mark_read') {
        $mid = (int)($_POST['msg_id'] ?? 0);
        if ($mid) { $pdo->prepare("UPDATE contact_messages SET status='read' WHERE id=?")->execute([$mid]); $msg = 'Marked as read.'; }
    } elseif ($action === 'delete_msg') {
        $mid = (int)($_POST['msg_id'] ?? 0);
        if ($mid) { $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$mid]); $msg = 'Message deleted.'; }
    } elseif ($action === 'toggle_featured' && $bizId) {
        $cur = $pdo->prepare("SELECT featured FROM businesses WHERE id=?"); $cur->execute([$bizId]);
        $f = (int)$cur->fetchColumn();
        $pdo->prepare("UPDATE businesses SET featured=? WHERE id=?")->execute([$f ? 0 : 1, $bizId]);
        $msg = 'Featured status toggled.';
    }
    header('Location: admin_panel.php?tab=' . ($_GET['tab'] ?? 'dashboard') . '&msg=' . urlencode($msg)); exit;
}

/* ── Admin Login ── */
$loginError = '';
if (!$isLoggedIn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
        $uname = trim($_POST['username'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $row   = $pdo->prepare("SELECT * FROM admin WHERE username=?");
        $row->execute([$uname]);
        $admin = $row->fetch();
        if ($admin && password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: admin_panel.php'); exit;
        } else {
            $loginError = 'Invalid username or password.';
        }
    }
    // Show login form
    ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login – QuickDial</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background:var(--dark);display:flex;align-items:center;justify-content:center;min-height:100vh">
  <div class="form-card" style="max-width:400px;width:100%">
    <div class="form-card-header">
      <i class="fa-solid fa-shield-halved"></i>
      <h2>Admin Login</h2>
      <p>QuickDial Control Panel</p>
    </div>
    <div class="form-body">
      <?php if ($loginError): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($loginError) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <label>Username</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" class="form-control" placeholder="admin" required>
          </div>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" class="form-control" placeholder="••••••" required>
          </div>
        </div>
        <button type="submit" name="admin_login" class="btn btn-primary w-100 btn-lg">
          <i class="fa-solid fa-right-to-bracket"></i> Login
        </button>
      </form>
      <p style="text-align:center;margin-top:1rem;font-size:.82rem;color:var(--text-muted)">
        Default: <strong>admin</strong> / <strong>admin123</strong>
      </p>
    </div>
  </div>
  <script src="../js/script.js"></script>
</body></html><?php
    exit;
}

/* ── Stats ── */
$tab         = $_GET['tab'] ?? 'dashboard';
$flashMsg    = $_GET['msg'] ?? $msg;
$totalBiz    = $pdo->query("SELECT COUNT(*) FROM businesses")->fetchColumn();
$pendingBiz  = $pdo->query("SELECT COUNT(*) FROM businesses WHERE status='pending'")->fetchColumn();
$approvedBiz = $pdo->query("SELECT COUNT(*) FROM businesses WHERE status='approved'")->fetchColumn();
$totalUsers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRevs   = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$unreadMsgs  = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();

/* ── Tab data ── */
$businesses = $users = $revList = $messages = [];
if ($tab === 'pending') {
    $businesses = $pdo->query("
        SELECT b.*, c.name AS category_name FROM businesses b
        LEFT JOIN categories c ON b.category_id=c.id
        WHERE b.status='pending' ORDER BY b.created_at DESC
    ")->fetchAll();
} elseif ($tab === 'all_biz') {
    $businesses = $pdo->query("
        SELECT b.*, c.name AS category_name FROM businesses b
        LEFT JOIN categories c ON b.category_id=c.id
        ORDER BY b.created_at DESC
    ")->fetchAll();
} elseif ($tab === 'users') {
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} elseif ($tab === 'reviews') {
    $revList = $pdo->query("
        SELECT r.*, b.name AS biz_name FROM reviews r
        LEFT JOIN businesses b ON r.business_id=b.id
        ORDER BY r.created_at DESC
    ")->fetchAll();
} elseif ($tab === 'messages') {
    $messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Panel – QuickDial</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- ── Topbar ── -->
<nav class="navbar">
  <div class="container">
    <a href="../index.php" class="navbar-brand"><i class="fa-solid fa-bolt"></i> Quick<span>Dial</span></a>
    <div class="nav-links" id="navLinks" style="display:flex">
      <span style="color:rgba(255,255,255,.6);font-size:.85rem;padding:.4rem .8rem">
        <i class="fa-solid fa-shield-halved"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?>
      </span>
      <a href="../index.php">View Site</a>
      <a href="?action=logout" style="color:#ff6b6b">Logout</a>
    </div>
  </div>
</nav>

<div class="admin-layout">
  <!-- ── Sidebar ── -->
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <h3>Control Panel</h3>
      <p>QuickDial Admin</p>
    </div>
    <nav class="admin-nav">
      <a href="?tab=dashboard" class="<?= $tab==='dashboard'?'active':'' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="?tab=pending"   class="<?= $tab==='pending'?'active':'' ?>">
        <i class="fa-solid fa-clock"></i> Pending
        <?php if ($pendingBiz > 0): ?>
          <span class="badge badge-danger" style="margin-left:auto"><?= $pendingBiz ?></span>
        <?php endif; ?>
      </a>
      <a href="?tab=all_biz"  class="<?= $tab==='all_biz'?'active':'' ?>"><i class="fa-solid fa-store"></i> All Businesses</a>
      <a href="?tab=users"    class="<?= $tab==='users'?'active':'' ?>"><i class="fa-solid fa-users"></i> Users</a>
      <a href="?tab=reviews"  class="<?= $tab==='reviews'?'active':'' ?>"><i class="fa-solid fa-star"></i> Reviews</a>
      <a href="?tab=messages" class="<?= $tab==='messages'?'active':'' ?>">
        <i class="fa-solid fa-envelope"></i> Messages
        <?php if ($unreadMsgs > 0): ?>
          <span class="badge badge-danger" style="margin-left:auto"><?= $unreadMsgs ?></span>
        <?php endif; ?>
      </a>
    </nav>
  </aside>

  <!-- ── Main content ── -->
  <main class="admin-content">
    <?php if ($flashMsg): ?>
      <div class="alert alert-success" data-dismiss="4000"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flashMsg) ?></div>
    <?php endif; ?>

    <?php if ($tab === 'dashboard'): ?>
      <!-- DASHBOARD -->
      <div class="admin-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>! Here's your overview.</p>
      </div>
      <div class="stat-cards">
        <div class="stat-card" style="border-color:#e8322a">
          <div class="icon" style="background:#fff0ef"><i class="fa-solid fa-store" style="color:#e8322a"></i></div>
          <div><div class="val"><?= $totalBiz ?></div><div class="key">Total Businesses</div></div>
        </div>
        <div class="stat-card" style="border-color:#f59e0b">
          <div class="icon" style="background:#fef3c7"><i class="fa-solid fa-clock" style="color:#f59e0b"></i></div>
          <div><div class="val"><?= $pendingBiz ?></div><div class="key">Pending Approval</div></div>
        </div>
        <div class="stat-card" style="border-color:#10b981">
          <div class="icon" style="background:#d1fae5"><i class="fa-solid fa-circle-check" style="color:#10b981"></i></div>
          <div><div class="val"><?= $approvedBiz ?></div><div class="key">Approved</div></div>
        </div>
        <div class="stat-card" style="border-color:#3b82f6">
          <div class="icon" style="background:#dbeafe"><i class="fa-solid fa-users" style="color:#3b82f6"></i></div>
          <div><div class="val"><?= $totalUsers ?></div><div class="key">Registered Users</div></div>
        </div>
        <div class="stat-card" style="border-color:#8b5cf6">
          <div class="icon" style="background:#ede9fe"><i class="fa-solid fa-star" style="color:#8b5cf6"></i></div>
          <div><div class="val"><?= $totalRevs ?></div><div class="key">Total Reviews</div></div>
        </div>
        <div class="stat-card" style="border-color:#ec4899">
          <div class="icon" style="background:#fce7f3"><i class="fa-solid fa-envelope" style="color:#ec4899"></i></div>
          <div><div class="val"><?= $unreadMsgs ?></div><div class="key">Unread Messages</div></div>
        </div>
      </div>
      <?php if ($pendingBiz > 0): ?>
        <div class="alert alert-warning">
          <i class="fa-solid fa-triangle-exclamation"></i>
          You have <strong><?= $pendingBiz ?></strong> pending business listing(s) waiting for approval.
          <a href="?tab=pending" style="color:var(--primary);font-weight:700;margin-left:.5rem">Review now →</a>
        </div>
      <?php endif; ?>

    <?php elseif ($tab === 'pending' || $tab === 'all_biz'): ?>
      <!-- BUSINESSES TABLE -->
      <div class="admin-header">
        <h1><?= $tab === 'pending' ? 'Pending Listings' : 'All Businesses' ?></h1>
        <p><?= $tab === 'pending' ? 'Review and approve or reject submitted listings.' : 'Manage all business listings.' ?></p>
      </div>
      <div class="data-table">
        <div class="data-table-head">
          <h3><?= count($businesses) ?> listing<?= count($businesses)!==1?'s':'' ?></h3>
        </div>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Business</th>
              <th>Category</th>
              <th>City</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($businesses)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem">No listings found.</td></tr>
            <?php endif; ?>
            <?php foreach ($businesses as $i => $b): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <strong><?= htmlspecialchars($b['name']) ?></strong>
                  <?php if ($b['featured']): ?><span class="badge badge-warning" style="margin-left:.3rem">Featured</span><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($b['category_name']) ?></td>
                <td><?= htmlspecialchars($b['city']) ?></td>
                <td><?= htmlspecialchars($b['phone']) ?></td>
                <td>
                  <?php
                  $badgeClass = ['pending'=>'badge-warning','approved'=>'badge-success','rejected'=>'badge-danger'][$b['status']] ?? 'badge-primary';
                  ?>
                  <span class="badge <?= $badgeClass ?>"><?= ucfirst($b['status']) ?></span>
                </td>
                <td>
                  <div class="action-buttons">
                    <?php if ($b['status'] !== 'approved'): ?>
                      <form method="POST" style="display:inline">
                        <input type="hidden" name="biz_id" value="<?= $b['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button class="btn btn-success btn-sm" title="Approve"><i class="fa-solid fa-check"></i></button>
                      </form>
                    <?php endif; ?>
                    <?php if ($b['status'] !== 'rejected'): ?>
                      <form method="POST" style="display:inline">
                        <input type="hidden" name="biz_id" value="<?= $b['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button class="btn btn-sm" style="background:#f59e0b;color:#fff" title="Reject"><i class="fa-solid fa-ban"></i></button>
                      </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="biz_id" value="<?= $b['id'] ?>">
                      <input type="hidden" name="action" value="toggle_featured">
                      <button class="btn btn-sm" style="background:#8b5cf6;color:#fff" title="Toggle Featured">
                        <i class="fa-solid fa-star"></i>
                      </button>
                    </form>
                    <?php if ($b['status'] === 'approved'): ?>
                      <a href="../business_details.php?id=<?= $b['id'] ?>" class="btn btn-outline btn-sm" target="_blank" title="View">
                        <i class="fa-solid fa-eye"></i>
                      </a>
                    <?php endif; ?>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this business permanently?')">
                      <input type="hidden" name="biz_id" value="<?= $b['id'] ?>">
                      <input type="hidden" name="action" value="delete_biz">
                      <button class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($tab === 'users'): ?>
      <!-- USERS -->
      <div class="admin-header"><h1>Registered Users</h1><p>All user accounts on QuickDial.</p></div>
      <div class="data-table">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Registered</th></tr></thead>
          <tbody>
            <?php if (empty($users)): ?>
              <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem">No users found.</td></tr>
            <?php endif; ?>
            <?php foreach ($users as $i => $u): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td><?= htmlspecialchars($u['city'] ?? '—') ?></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($tab === 'reviews'): ?>
      <!-- REVIEWS -->
      <div class="admin-header"><h1>All Reviews</h1><p>Moderate user reviews.</p></div>
      <div class="data-table">
        <table>
          <thead><tr><th>#</th><th>Business</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Date</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($revList)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem">No reviews found.</td></tr>
            <?php endif; ?>
            <?php foreach ($revList as $i => $rv): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($rv['biz_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($rv['reviewer_name']) ?></td>
                <td><?= str_repeat('★', $rv['rating']) ?></td>
                <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?= htmlspecialchars($rv['comment'] ?? '—') ?>
                </td>
                <td><?= date('d M Y', strtotime($rv['created_at'])) ?></td>
                <td>
                  <form method="POST" onsubmit="return confirm('Delete this review?')">
                    <input type="hidden" name="rev_id" value="<?= $rv['id'] ?>">
                    <input type="hidden" name="action" value="delete_review">
                    <button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($tab === 'messages'): ?>
      <!-- MESSAGES -->
      <div class="admin-header"><h1>Contact Messages</h1><p>Enquiries submitted through the contact form.</p></div>
      <div class="data-table">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($messages)): ?>
              <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem">No messages yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($messages as $i => $m): ?>
              <tr <?= $m['status']==='unread' ? 'style="font-weight:600"' : '' ?>>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td><?= htmlspecialchars($m['subject'] ?? '—') ?></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?= htmlspecialchars($m['message']) ?>
                </td>
                <td>
                  <?php $bc=['unread'=>'badge-danger','read'=>'badge-success','replied'=>'badge-primary'][$m['status']] ?? 'badge-primary'; ?>
                  <span class="badge <?= $bc ?>"><?= ucfirst($m['status']) ?></span>
                </td>
                <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                <td>
                  <div class="action-buttons">
                    <?php if ($m['status']==='unread'): ?>
                      <form method="POST" style="display:inline">
                        <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                        <input type="hidden" name="action" value="mark_read">
                        <button class="btn btn-success btn-sm" title="Mark read"><i class="fa-solid fa-check"></i></button>
                      </form>
                    <?php endif; ?>
                    <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= urlencode($m['subject'] ?? 'Your enquiry') ?>" class="btn btn-outline btn-sm" title="Reply">
                      <i class="fa-solid fa-reply"></i>
                    </a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this message?')">
                      <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                      <input type="hidden" name="action" value="delete_msg">
                      <button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>

<script src="../js/script.js"></script>
</body>
</html>
