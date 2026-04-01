<?php
// includes/navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
      <i class="fa-solid fa-leaf text-success"></i> Vegora
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'shop.php' ? 'active' : ''; ?>" href="shop.php">Shop</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="about.php">About Us</a>
        </li>
      </ul>
      
      <div class="d-flex align-items-center gap-4">
        <!-- Search Bar -->
        <form class="position-relative d-none d-lg-block" method="GET" action="shop.php">
          <input class="form-control rounded-pill pe-5" type="search" name="q" value="<?php echo htmlspecialchars((string)($_GET['q'] ?? '')); ?>" placeholder="Search veggies..." style="width: 250px; background-color: #f3f4f6; border: none; padding-left: 20px;">
          <button type="submit" class="btn position-absolute end-0 top-50 translate-middle-y text-muted pb-2">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </form>

        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="dropdown">
            <a href="#" class="text-dark text-decoration-none fw-semibold d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown">
              <i class="fa-solid fa-circle-user text-success fs-5"></i> 
              <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
              <li><a class="dropdown-item py-2" href="orders.php"><i class="fa-solid fa-clipboard-list me-2 text-muted"></i> Orders</a></li>
              
              <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li><a class="dropdown-item py-2" href="admin/index.php"><i class="fa-solid fa-shield-halved me-2 text-muted"></i> Admin Panel</a></li>
              <?php endif; ?>
              
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item py-2 text-danger" href="controllers/authController.php?action=logout"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="login.php" class="text-dark text-decoration-none fw-semibold d-flex align-items-center gap-2">
            <i class="fa-regular fa-user"></i> <span class="d-none d-md-inline">Log In</span>
          </a>
        <?php endif; ?>
        
        <a href="cart.php" class="cart-icon-wrapper text-decoration-none">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="cart-badge">0</span> <!-- Populated by AJAX -->
        </a>
      </div>
    </div>
  </div>
</nav>
