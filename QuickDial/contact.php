<?php
/**
 * QuickDial – Contact Page
 * File: contact.php
 */
$pageTitle = 'Contact Us';
$pageDesc  = 'Get in touch with the QuickDial team for support, feedback or partnership enquiries.';
require_once 'config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        $error = 'Name, email, and message are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, email, phone, subject, message)
            VALUES (?,?,?,?,?)
        ");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        $success = 'Thank you! Your message has been sent. We will get back to you within 24 hours.';
    }
}
require_once 'includes/header.php';
?>

<div class="contact-hero">
  <div class="container">
    <h1>Get in Touch</h1>
    <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
  </div>
</div>

<section class="contact-body">
  <div class="container">
    <div class="contact-grid">
      <!-- Info -->
      <div>
        <div class="contact-info-card">
          <h3 style="font-size:1.15rem;font-weight:700;margin-bottom:1.5rem;color:var(--dark)">Contact Information</h3>
          <div class="contact-info-item">
            <div class="c-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div class="c-text">
              <h4>Office Address</h4>
              <p>42, Business Park, Andheri East, Mumbai – 400069, Maharashtra, India</p>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="c-icon"><i class="fa-solid fa-phone"></i></div>
            <div class="c-text">
              <h4>Phone & WhatsApp</h4>
              <p>+91 12345 67890<br>Mon–Sat, 9 AM – 7 PM</p>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="c-icon"><i class="fa-solid fa-envelope"></i></div>
            <div class="c-text">
              <h4>Email Us</h4>
              <p>support@quickdial.in<br>business@quickdial.in</p>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="c-icon"><i class="fa-solid fa-clock"></i></div>
            <div class="c-text">
              <h4>Working Hours</h4>
              <p>Monday – Saturday<br>9:00 AM to 7:00 PM IST</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="contact-form-card">
        <h3>Send Us a Message</h3>
        <?php if ($success): ?>
          <div class="alert alert-success" data-dismiss="6000"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger" data-dismiss="5000"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="contactForm" method="POST" action="contact.php" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="con_name">Your Name *</label>
              <input type="text" id="con_name" name="name" class="form-control"
                     placeholder="Full name"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label for="con_email">Email Address *</label>
              <input type="email" id="con_email" name="email" class="form-control"
                     placeholder="you@example.com"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="con_phone">Phone Number</label>
              <input type="tel" id="con_phone" name="phone" class="form-control"
                     placeholder="Optional"
                     value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="con_subject">Subject</label>
              <input type="text" id="con_subject" name="subject" class="form-control"
                     placeholder="How can we help?"
                     value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="con_message">Message *</label>
            <textarea id="con_message" name="message" class="form-control" rows="6"
                      placeholder="Tell us how we can help you…" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="fa-solid fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
