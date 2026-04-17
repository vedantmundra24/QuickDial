<?php

// register page 

$pageTitle = 'Create Account';
$pageDesc  = 'Register on QuickDial to list your business or leave reviews.';
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// redirecting if already logged

if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $city     = trim($_POST['city']     ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // validation

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        
        // duplicate email

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists. <a href="login.php">Login instead?</a>';
        } else {
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare("INSERT INTO users (name, email, password, phone, city) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$name, $email, $hash, $phone, $city]);
            
            // Auto-login

            $_SESSION['user_id']   = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email']= $email;
            header('Location: index.php'); exit;
        }
    }
}
require_once 'includes/header.php';
?>

<section class="form-page">
  <div class="form-card form-card-wide">
    <div class="form-card-header">
      <i class="fa-solid fa-user-plus"></i>
      <h2>Create Your Account</h2>
      <p>Join QuickDial – it's free forever</p>
    </div>
    <div class="form-body">
      <?php if ($error): ?>
        <div class="alert alert-danger" data-dismiss="6000"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
      <?php endif; ?>

      <form id="registerForm" method="POST" action="register.php" novalidate>
        <div class="form-row">
          <div class="form-group">
            <label for="name">Full Name *</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-user"></i>
              <input type="text" id="name" name="name" class="form-control"
                     placeholder="Rahul Sharma"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label for="email">Email Address *</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-envelope"></i>
              <input type="email" id="email" name="email" class="form-control"
                     placeholder="you@example.com"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-phone"></i>
              <input type="tel" id="phone" name="phone" class="form-control"
                     placeholder="10-digit number"
                     value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="city">City</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-city"></i>
              <input type="text" id="city" name="city" class="form-control"
                     placeholder="Mumbai"
                     value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="password">Password *</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-lock"></i>
              <input type="password" id="password" name="password" class="form-control"
                     placeholder="Min 6 characters" required>
            </div>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm Password *</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-lock"></i>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                     placeholder="Repeat password" required>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">
          <i class="fa-solid fa-user-plus"></i> Create Account
        </button>
      </form>
    </div>
    <div class="form-footer">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
