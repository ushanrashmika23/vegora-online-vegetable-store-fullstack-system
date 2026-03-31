<?php
// index.php
session_start();
require_once __DIR__ . '/controllers/productController.php';

// Fetch a subset of products for the featured section
$products = getAllProducts();
$featuredProducts = array_slice($products, 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vegora - Fresh Online Vegetables</title>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>

  <!-- Injected Navbar -->
  <?php require_once 'includes/navbar.php'; ?>

  <!-- Main Content -->
  <main class="container">
    
    <!-- Hero Section -->
    <section class="hero-section row align-items-center mx-1">
      <div class="col-lg-6 hero-text z-1">
        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 mb-3 fw-bold">100% Organic & Fresh</span>
        <h1>Fresh Vegetables <br> Delivered <span class="text-highlight">Daily.</span></h1>
        <p>Get farm-fresh, pesticide-free vegetables delivered straight to your doorstep within hours.</p>
        <div class="d-flex gap-3 mt-4">
          <a href="shop.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></a>
          <a href="#" class="btn btn-outline-primary btn-lg rounded-pill px-4" style="background: white;">How it works</a>
        </div>
      </div>
      <div class="col-lg-6 mt-5 mt-lg-0 text-center" style="background: transparent;">
        <!-- Hero Image -->
        <!-- <img src="https://images.unsplash.com/photo-1471193945509-9ad0617afabf?auto=format&fit=crop&q=80&w=1200" alt="Fresh Vegetables" class="img-fluid hero-img shadow-lg" style="width: 100%; height: 420px; object-fit: cover; border-radius: 0;"> -->
        <img src="./assets/2.png" alt="Fresh Vegetables" class="img-fluid hero-img" width="100%" height="420" style="background: transparent; mix-blend-mode: multiply; object-fit: contain;">
      </div>
    </section>

    <!-- Featured Categories Quick Links -->
    <section class="row g-4 mb-5 mx-1">
      <div class="col-md-4">
        <div class="d-flex align-items-center gap-3 p-4 rounded-4" style="background: #f0fdf4;">
          <div class="bg-white p-3 rounded-circle text-success shadow-sm"><i class="fa-solid fa-truck-fast fs-4"></i></div>
          <div><h5 class="mb-1">Free Delivery</h5><p class="mb-0 text-muted small">Orders over $50</p></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-flex align-items-center gap-3 p-4 rounded-4" style="background: #fff8f1;">
          <div class="bg-white p-3 rounded-circle text-warning shadow-sm"><i class="fa-solid fa-leaf fs-4"></i></div>
          <div><h5 class="mb-1">100% Organic</h5><p class="mb-0 text-muted small">Local farms guaranteed</p></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-flex align-items-center gap-3 p-4 rounded-4" style="background: #f0f9ff;">
          <div class="bg-white p-3 rounded-circle text-info shadow-sm"><i class="fa-solid fa-shield-halved fs-4"></i></div>
          <div><h5 class="mb-1">Money Back</h5><p class="mb-0 text-muted small">30 days guarantee</p></div>
        </div>
      </div>
    </section>

    <!-- Featured Products Dynamic Generation -->
    <section class="my-5 pt-4">
      <div class="d-flex justify-content-between align-items-end mb-4 mx-1">
        <div>
          <h2 class="mb-1">Featured Freshness</h2>
          <p class="text-muted mb-0">Our most popular seasonal picks</p>
        </div>
        <a href="shop.php" class="text-success text-decoration-none fw-bold">View All <i class="fa-solid fa-arrow-right-long ms-1"></i></a>
      </div>

      <div class="row g-4">
        <?php foreach ($featuredProducts as $index => $product): ?>
        <div class="col-xl-3 col-lg-4 col-md-6 <?php echo $index == 3 ? 'd-none d-xl-block' : ''; ?>">
          <div class="card product-card h-100">
            <!-- Trending badge replaced by direct link -->
            <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
              <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-3 z-1 rounded-circle shadow-sm" style="width: 35px; height: 35px;"><i class="fa-regular fa-heart text-muted"></i></button>
              <div class="img-wrapper">
                <?php $imgSrc = str_starts_with($product['image'], 'http') ? $product['image'] : $product['image']; ?>
                <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
              </div>
              <div class="card-body d-flex flex-column">
                <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
            </a>
              <div class="d-flex justify-content-between align-items-end mt-auto pt-3">
                <div class="product-price mb-0">
                  <?php if (!empty($product['discounted_price']) && $product['discounted_price'] > 0 && $product['discounted_price'] < $product['price']): ?>
                    <span class="text-decoration-line-through text-secondary me-2">$<?php echo number_format($product['price'], 2); ?></span>
                    <span class="text-success fw-bold">$<?php echo number_format($product['discounted_price'], 2); ?></span>
                  <?php else: ?>
                    $<?php echo number_format($product['price'], 2); ?>
                  <?php endif; ?>
                  <span class="small text-muted fw-normal">/ kg</span>
                </div>
                <button class="btn btn-primary rounded-circle p-0" style="width: 45px; height: 45px;" onclick="addToCart(<?php echo $product['id']; ?>)"><i class="fa-solid fa-plus"></i></button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

  </main>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
</body>
</html>
