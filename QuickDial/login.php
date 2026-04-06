<?php
/**
 * QuickDial – Login Page
 * File: login.php
 */
$pageTitle = 'Login';
$pageDesc  = 'Login to your QuickDial account.';
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect); exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
require_once 'includes/header.php';
?>

<section class="form-page">
  <div class="form-card">
    <div class="form-card-header">
      <i class="fa-solid fa-right-to-bracket"></i>
      <h2>Welcome Back</h2>
      <p>Login to your QuickDial account</p>
    </div>
    <div class="form-body">
      <?php if ($error): ?>
        <div class="alert alert-danger" data-dismiss="5000"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'required'): ?>
        <div class="alert alert-warning" data-dismiss="5000"><i class="fa-solid fa-triangle-exclamation"></i> Please login to continue.</div>
      <?php endif; ?>

      <form id="loginForm" method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>" novalidate>
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-envelope"></i>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="Your password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">
          <i class="fa-solid fa-right-to-bracket"></i> Login
        </button>
      </form>
    </div>
    <div class="form-footer">
      Don't have an account? <a href="register.php">Sign up free</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
