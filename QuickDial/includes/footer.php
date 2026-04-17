<?php

// footer

$prefix = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../' : '';
?>

<!-- footer  -->

<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="brand-logo">
          <i class="fa-solid fa-bolt" style="color:var(--primary)"></i>
          Quick<span>Dial</span>
        </div>
        <p>India's trusted platform to discover and connect with local businesses. Find the best nearby services in seconds.</p>
        <div class="social-links">
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div>
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= $prefix ?>index.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Home</a></li>
          <li><a href="<?= $prefix ?>search.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Browse Businesses</a></li>
          <li><a href="<?= $prefix ?>add_business.php"><i class="fa-solid fa-chevron-right fa-xs"></i> List Your Business</a></li>
          <li><a href="<?= $prefix ?>contact.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Contact Us</a></li>
        </ul>
      </div>
      <div>
        <h4>Categories</h4>
        <ul>
          <li><a href="<?= $prefix ?>search.php?category=Restaurants">Restaurants</a></li>
          <li><a href="<?= $prefix ?>search.php?category=Hospitals">Hospitals</a></li>
          <li><a href="<?= $prefix ?>search.php?category=Hotels">Hotels</a></li>
          <li><a href="<?= $prefix ?>search.php?category=Salons">Salons</a></li>
          <li><a href="<?= $prefix ?>search.php?category=Education">Education</a></li>
        </ul>
      </div>
      <div>
        <h4>Contact</h4>
        <ul>
          <li><a href="#"><i class="fa-solid fa-location-dot fa-xs"></i> Nagpur, India</a></li>
          <li><a href="tel:+911234567890"><i class="fa-solid fa-phone fa-xs"></i> +91 12345 67890</a></li>
          <li><a href="mailto:support@quickdial.in"><i class="fa-solid fa-envelope fa-xs"></i> support@quickdial.in</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> QuickDial. All rights reserved. | Built with ❤️ in India</p>
  </div>
</footer>

<script src="<?= $prefix ?>js/script.js"></script>
</body>
</html>
