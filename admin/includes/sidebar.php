<?php
// admin/includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar p-0 d-flex flex-column pt-3">
  <div class="d-flex align-items-center gap-2 mb-5 mt-2 px-2">
    <i class="fa-solid fa-leaf fs-3" style="color: var(--vegi-green);"></i>
    <h3 class="fw-bold mb-0 text-dark">Vegora Admin</h3>
  </div>
  
  <ul class="nav flex-column mb-auto">
    <li class="px-3 pt-1 pb-1 text-uppercase small fw-bold text-muted" style="letter-spacing: .08em;">Overview</li>
    <li class="nav-item">
      <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">
        <i class="fa-solid fa-gauge-high me-2 text-center" style="width: 20px;"></i> Dashboard
      </a>
    </li>

    <li class="px-3 pt-3 pb-1 text-uppercase small fw-bold text-muted" style="letter-spacing: .08em;">Catalog</li>
    <li class="nav-item">
      <a class="nav-link <?php echo ($currentPage === 'products.php' || $currentPage === 'add_product.php' || $currentPage === 'edit_product.php') ? 'active' : ''; ?>" href="products.php">
        <i class="fa-solid fa-carrot me-2 text-center" style="width: 20px;"></i> Products
      </a>
    </li>
    <li class="nav-item mb-2">
      <a class="nav-link <?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>" href="categories.php">
        <i class="fa-solid fa-layer-group me-2 text-center" style="width: 20px;"></i> Categories
      </a>
    </li>

    <li class="px-3 pt-2 pb-1 text-uppercase small fw-bold text-muted" style="letter-spacing: .08em;">Sales</li>
    <li class="nav-item">
      <a class="nav-link <?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>" href="orders.php">
        <i class="fa-solid fa-basket-shopping me-2 text-center" style="width: 20px;"></i> Orders
      </a>
    </li>

    <li class="px-3 pt-2 pb-1 text-uppercase small fw-bold text-muted" style="letter-spacing: .08em;">Customers</li>
    <li class="nav-item">
      <a class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>" href="users.php">
        <i class="fa-solid fa-users me-2 text-center" style="width: 20px;"></i> Users
      </a>
    </li>

    <li class="px-3 pt-2 pb-1 text-uppercase small fw-bold text-muted" style="letter-spacing: .08em;">Promotions</li>
    <li class="nav-item mb-2">
      <a class="nav-link <?php echo $currentPage === 'coupons.php' ? 'active' : ''; ?>" href="coupons.php">
        <i class="fa-solid fa-ticket me-2 text-center" style="width: 20px;"></i> Coupons
      </a>
    </li>
  </ul>

  <!-- Logout pushed to bottom utilizing mb-auto on UL -->
  <div class="mt-4">
    <a href="../controllers/authController.php?action=logout" class="nav-link text-danger w-100">
      <i class="fa-solid fa-arrow-right-from-bracket me-2 text-center" style="width: 20px;"></i> Logout
    </a>
  </div>
  <p class="p-0 text-center text-muted"><a href="https://github.com/ushanrashmika23" target="_blank" class="text-decoration-none text-muted">@ushanrashmika23</a></p>
</aside>
