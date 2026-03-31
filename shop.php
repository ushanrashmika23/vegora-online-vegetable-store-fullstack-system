<?php
session_start();
require_once __DIR__ . '/controllers/productController.php';

// Fetch products from database
$products = getAllProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Vegora</title>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

  <!-- Injected Navbar -->
  <?php require_once 'includes/navbar.php'; ?>

  <!-- Page Header -->
  <div class="bg-white py-5 mb-5 border-bottom">
    <div class="container text-center">
      <h1 class="display-4 fw-bold text-dark">Shop Fresh Veggies</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center mb-0 mt-3">
          <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Shop</li>
        </ol>
      </nav>
    </div>
  </div>

  <main class="container mb-5 pb-5">
    <div class="row g-5">
      
      <!-- Sidebar Filters -->
      <aside class="col-lg-3">
        <div class="filter-card">
          <h4 class="filter-title">Categories</h4>
          <div class="list-group list-group-flush mb-4">
            <a href="#" class="list-group-item list-group-item-action active d-flex justify-content-between align-items-center">
              All Fresh
              <span class="badge bg-success rounded-pill"><?php echo count($products); ?></span>
            </a>
            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Organic Greens
              <span class="badge bg-light text-dark rounded-pill">0</span>
            </a>
            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Root Vegetables
              <span class="badge bg-light text-dark rounded-pill">0</span>
            </a>
            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              Peppers & Tomatoes
              <span class="badge bg-light text-dark rounded-pill">0</span>
            </a>
          </div>

          <h4 class="filter-title mt-2">Filter by Price</h4>
          <div class="px-2">
            <input type="range" class="form-range" min="0" max="50" step="1" id="priceRange" style="accent-color: var(--vegi-green);">
            <div class="d-flex justify-content-between text-muted fw-bold mt-2">
              <span>$0</span>
              <span>$50</span>
            </div>
            <button class="btn btn-outline-primary w-100 mt-4 rounded-pill">Apply Filter</button>
          </div>
        </div>
      </aside>

      <!-- Main Product Grid -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
          <p class="mb-0 text-muted">Showing <strong><?php echo count($products); ?></strong> results</p>
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted fw-semibold flex-shrink-0">Sort by:</label>
            <select class="form-select border-0 shadow-sm rounded-pill fw-semibold bg-white" style="cursor: pointer;">
              <option>Default Sorting</option>
              <option>Price: Low to High</option>
              <option>Price: High to Low</option>
              <option>Latest</option>
            </select>
          </div>
        </div>

        <div class="row g-4">
          <?php if (empty($products)): ?>
            <div class="col-12 py-5 text-center">
              <i class="fa-solid fa-basket-shopping text-muted fs-1 mb-3 opacity-50"></i>
              <h4 class="text-muted">No products available at the moment.</h4>
            </div>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
              <div class="col-xl-4 col-md-6">
                <div class="card product-card h-100">
                  <!-- Wrapped content inside anchor -->
                  <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                    <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-3 z-1 rounded-circle shadow-sm" style="width: 35px; height: 35px;"><i class="fa-regular fa-heart text-muted"></i></button>
                    
                    <div class="img-wrapper">
                      <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="card-body">
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
                      <button class="btn btn-primary rounded-circle p-0" style="width: 45px; height: 45px;" onclick="addToCart(<?php echo $product['id']; ?>)">
                        <i class="fa-solid fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (count($products) > 0): ?>
        <nav class="mt-5 d-flex justify-content-center">
          <ul class="pagination">
            <li class="page-item disabled">
              <span class="page-link border-0 rounded-circle me-2 text-muted fw-bold p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fa-solid fa-chevron-left"></i></span>
            </li>
            <li class="page-item active">
              <span class="page-link border-0 rounded-circle me-2 bg-success text-white fw-bold p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">1</span>
            </li>
            <li class="page-item">
              <a class="page-link border-0 rounded-circle me-2 text-dark bg-white fw-bold p-3 shadow-sm d-flex align-items-center justify-content-center" href="#" style="width: 50px; height: 50px;"><i class="fa-solid fa-chevron-right"></i></a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
      </div>

    </div>
  </main>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
  <script src="assets/js/cart.js"></script>
</body>
</html>
