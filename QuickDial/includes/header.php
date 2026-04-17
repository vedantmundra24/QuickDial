<?php

// header

if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdmin = isset($_SESSION['admin_id']);
$isUser  = isset($_SESSION['user_id']);

// check admin folder

$prefix = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle).' – QuickDial' : 'QuickDial – Find Local Businesses' ?></title>
  <meta name="description" content="<?= isset($pageDesc) ? htmlspecialchars($pageDesc) : 'QuickDial helps you discover the best local businesses near you – restaurants, hospitals, salons, and more.' ?>">
  <link rel="stylesheet" href="<?= $prefix ?>css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- navbar -->

<nav class="navbar">
  <div class="container">
    <a href="<?= $prefix ?>index.php" class="navbar-brand">
      <i class="fa-solid fa-bolt"></i>
      Quick<span>Dial</span>
    </a>

    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>

    <div class="nav-links" id="navLinks">
      <a href="<?= $prefix ?>index.php" class="<?= $currentPage==='index.php'?'active':'' ?>">Home</a>
      <a href="<?= $prefix ?>search.php" class="<?= $currentPage==='search.php'?'active':'' ?>">Browse</a>
      <?php if ($isUser): ?>
        <a href="<?= $prefix ?>add_business.php" class="<?= $currentPage==='add_business.php'?'active':'' ?>">List Business</a>
        <a href="<?= $prefix ?>logout.php">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
      <?php else: ?>
        <a href="<?= $prefix ?>add_business.php">List Business</a>
        <a href="<?= $prefix ?>login.php" class="<?= $currentPage==='login.php'?'active':'' ?>">Login</a>
        <a href="<?= $prefix ?>register.php" class="btn-primary">Sign Up</a>
      <?php endif; ?>
      <a href="<?= $prefix ?>contact.php" class="<?= $currentPage==='contact.php'?'active':'' ?>">Contact</a>
    </div>
  </div>
</nav>
