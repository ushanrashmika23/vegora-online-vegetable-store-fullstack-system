<?php
// includes/footer.php
?>
<footer class="mt-32">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-4">
        <a href="index.php" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-3">
          <i class="fa-solid fa-leaf text-success fs-3"></i>
          <span class="fs-4 fw-bold text-dark">Vegora</span>
        </a>
        <p class="mb-4 pe-lg-4">Farm-fresh vegetables delivered with speed, transparency, and quality you can trust.</p>
        <div class="d-flex flex-column gap-2 small">
          <div><i class="fa-solid fa-location-dot text-success me-2"></i> 18 Green Market Road, Colombo</div>
          <div><i class="fa-solid fa-phone text-success me-2"></i> +94 11 234 5678</div>
          <div><i class="fa-solid fa-envelope text-success me-2"></i> support@vegora.com</div>
        </div>
      </div>

      <div class="col-6 col-lg-2">
        <h5>Shop</h5>
        <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
          <li><a href="index.php">Home</a></li>
          <li><a href="shop.php">All Products</a></li>
          <li><a href="cart.php">Cart</a></li>
          <li><a href="checkout.php">Checkout</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h5>Account</h5>
        <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
          <li><a href="login.php">Login</a></li>
          <li><a href="register.php">Register</a></li>
          <li><a href="orders.php">My Orders</a></li>
          <li><a href="admin/index.php">Admin</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h5>Weekly Fresh Deals</h5>
        <p class="mb-3">Get seasonal offers, restock alerts, and healthy recipe picks every week.</p>
        <form class="d-flex gap-2 mb-4" onsubmit="return false;">
          <input type="email" class="form-control rounded-pill" placeholder="Enter your email" aria-label="Email for newsletter">
          <button type="submit" class="btn btn-primary rounded-pill px-4">Subscribe</button>
        </form>
        <div class="d-flex align-items-center gap-3 fs-5">
          <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
          <a href="#" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
    </div>

    <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
      <p class="mb-0">&copy; <?php echo date('Y'); ?> Vegora Store. All rights reserved.</p>
      <div class="d-flex flex-wrap gap-3 small">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Refund Policy</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/cart.js"></script>
