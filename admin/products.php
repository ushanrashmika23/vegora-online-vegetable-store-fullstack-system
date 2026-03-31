<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../controllers/productController.php';

$products = getAllProducts();
$pageTitle = 'Manage Products - Vegora Admin';
$headerActionHtml = '<a href="add_product.php" class="btn btn-success fw-bold rounded-pill px-4 shadow-sm"><i class="fa-solid fa-plus me-2"></i> Add Product</a>';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- Injected Sidebar -->
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content">
    
    <!-- Injected Topbar -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <?php if (isset($_SESSION['admin_success'])): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i> <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['admin_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="card card-stat bg-white h-100 p-4">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Product</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Category</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Price</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Stock</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?php 
                    // Handle placeholder external images or relative local uploads nicely
                     $imgSrc = str_starts_with($p['image'], 'http') ? $p['image'] : '../' . $p['image']; 
                  ?>
                  <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="prod-thumb">
                  <span class="fw-bold text-dark"><?php echo htmlspecialchars($p['name']); ?></span>
                </div>
              </td>
              <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['category']); ?></span></td>
              <td class="fw-semibold text-muted">
                <?php if (!empty($p['discounted_price']) && $p['discounted_price'] > 0 && $p['discounted_price'] < $p['price']): ?>
                  <span class="text-decoration-line-through text-secondary me-2">$<?php echo number_format($p['price'], 2); ?></span>
                  <span class="text-success fw-bold">$<?php echo number_format($p['discounted_price'], 2); ?></span>
                <?php else: ?>
                  $<?php echo number_format($p['price'], 2); ?>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($p['stock'] <= ($p['stock_limit'] ?? 20)): ?>
                  <span class="badge bg-danger rounded-pill"><?php echo $p['stock']; ?></span>
                  <span class="small text-muted ms-2">limit: <?php echo (int)($p['stock_limit'] ?? 20); ?></span>
                <?php else: ?>
                  <span class="fw-semibold"><?php echo $p['stock']; ?></span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-light text-primary rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;"><i class="fa-solid fa-pen"></i></a>
                <a href="../controllers/productController.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" style="width: 32px; height: 32px;" onclick="return confirm('Delete this product permanently?');"><i class="fa-solid fa-trash"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">No products found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
