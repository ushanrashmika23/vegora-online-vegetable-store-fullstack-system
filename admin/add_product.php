<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Category.php';

$categoryModel = new Category($pdo);
$categories = $categoryModel->getAll();

$pageTitle = 'Add Product - Vegora Admin';
$showBackButton = true;
$backLink = 'products.php';
$pageHeader = 'Add New Product';
$hideProfile = true;
require_once __DIR__ . '/includes/header.php';
?>

  <!-- Injected Sidebar -->
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content">
    
    <!-- Injected Topbar -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <?php if (isset($_SESSION['admin_error'])): ?>
      <div class="alert alert-danger shadow-sm border-0 mb-4">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
      </div>
    <?php endif; ?>

    <div class="bg-white p-5 rounded-4 shadow-sm border border-light col-lg-8">
      <form action="../controllers/productController.php?action=add" method="POST" enctype="multipart/form-data">
        
        <div class="mb-4">
          <label for="name" class="form-label fw-bold">Product Name</label>
          <input type="text" name="name" id="name" class="form-control bg-light border-0 py-2" required>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <label for="price" class="form-label fw-bold">Price ($)</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control bg-light border-0 py-2" required>
          </div>
          <div class="col-md-6">
            <label for="discounted_price" class="form-label fw-bold">Discounted Price ($)</label>
            <input type="number" step="0.01" name="discounted_price" id="discounted_price" class="form-control bg-light border-0 py-2" placeholder="Optional">
            <div class="form-text text-muted">Leave empty for no product discount.</div>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <label for="stock" class="form-label fw-bold">Stock Quantity</label>
            <input type="number" name="stock" id="stock" class="form-control bg-light border-0 py-2" required>
          </div>
          <div class="col-md-6">
            <label for="stock_limit" class="form-label fw-bold">Low Stock Limit</label>
            <input type="number" name="stock_limit" id="stock_limit" class="form-control bg-light border-0 py-2" value="20" min="0" required>
            <div class="form-text text-muted">Dashboard shows low stock when quantity reaches this value.</div>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Description</label>
          <textarea name="description" class="form-control bg-light border-0 py-2" rows="4" placeholder="Enter product details..."></textarea>
        </div>

        <div class="mb-4">
          <label for="category" class="form-label fw-bold">Category</label>
          <select name="category_id" id="category" class="form-select bg-light border-0 py-2" required>
            <option value="">Select Category...</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text text-muted">Manage category list from <a href="categories.php" class="text-decoration-none">Categories</a>.</div>
        </div>

        <div class="mb-5">
          <label for="image" class="form-label fw-bold">Product Image</label>
          <input class="form-control bg-light border-0 py-2" type="file" id="image" name="image" accept="image/*">
          <div class="form-text text-muted">Upload a high quality image of the product.</div>
        </div>

        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm rounded-pill"><i class="fa-solid fa-plus me-2"></i> Create Product</button>
      </form>
    </div>
  </main>
</body>
</html>
