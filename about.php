<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Vegora</title>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

  <?php require_once 'includes/navbar.php'; ?>

  <header class="bg-white py-5 border-bottom">
    <div class="container text-center">
      <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 mb-3 fw-bold">Who We Are</span>
      <h1 class="display-4 fw-bold text-dark mb-2">About Vegora</h1>
      <p class="text-muted mb-0">Fresh produce, local partnerships, and reliable delivery in one place.</p>
    </div>
  </header>

  <main class="container py-5">
    <div class="row g-4 mb-5">
      <div class="col-lg-7">
        <div class="bg-white rounded-4 shadow-sm border border-light p-4 p-md-5 h-100">
          <h2 class="fw-bold mb-3">Our Story</h2>
          <p class="text-muted fs-5" style="line-height: 1.8;">
            Vegora started with one simple mission: make truly fresh vegetables easy to access for every family.
            We work directly with trusted farms, source produce daily, and deliver with care so what reaches your
            kitchen is crisp, healthy, and full of flavor.
          </p>
          <p class="text-muted mb-0" style="line-height: 1.8;">
            From seasonal greens to everyday staples, our platform combines transparent pricing,
            quality checks, and quick logistics to make healthy eating practical and affordable.
          </p>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="bg-white rounded-4 shadow-sm border border-light p-4 p-md-5 h-100">
          <h2 class="fw-bold mb-4">What We Promise</h2>
          <div class="d-flex flex-column gap-3 text-muted fw-semibold">
            <div><i class="fa-solid fa-leaf text-success me-2"></i> Fresh and responsibly sourced produce</div>
            <div><i class="fa-solid fa-shield-halved text-success me-2"></i> Strict quality control before dispatch</div>
            <div><i class="fa-solid fa-truck-fast text-success me-2"></i> Reliable and fast delivery windows</div>
            <div><i class="fa-solid fa-hand-holding-dollar text-success me-2"></i> Fair pricing with regular discounts</div>
          </div>
        </div>
      </div>
    </div>

    <section class="bg-white rounded-4 shadow-sm border border-light p-4 p-md-5 mb-5">
      <h2 class="fw-bold mb-4">By The Numbers</h2>
      <div class="row g-3 text-center">
        <div class="col-md-3 col-6">
          <div class="p-3 rounded-3 bg-light">
            <div class="fs-3 fw-bold text-success">50+</div>
            <div class="text-muted small fw-semibold">Farm Partners</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="p-3 rounded-3 bg-light">
            <div class="fs-3 fw-bold text-success">10k+</div>
            <div class="text-muted small fw-semibold">Orders Delivered</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="p-3 rounded-3 bg-light">
            <div class="fs-3 fw-bold text-success">98%</div>
            <div class="text-muted small fw-semibold">Satisfaction Rate</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="p-3 rounded-3 bg-light">
            <div class="fs-3 fw-bold text-success">24/7</div>
            <div class="text-muted small fw-semibold">Order Support</div>
          </div>
        </div>
      </div>
    </section>

    <section class="text-center bg-white rounded-4 shadow-sm border border-light p-4 p-md-5">
      <h3 class="fw-bold mb-2">Ready to shop fresh?</h3>
      <p class="text-muted mb-4">Browse our latest produce and get it delivered to your doorstep.</p>
      <a href="shop.php" class="btn btn-primary btn-lg rounded-pill px-5">Visit Shop</a>
    </section>
  </main>

  <?php require_once 'includes/footer.php'; ?>
</body>
</html>
