<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Product.php';

$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    header('Location: products.php');
    exit;
}

$productModel = new Product($pdo);
$product = $productModel->findById($id);

if (!$product) {
    $_SESSION['admin_error'] = "Product not found.";
    header('Location: products.php');
    exit;
}
$pageTitle = 'Edit Product - Vegora Admin';
$showBackButton = true;
$backLink = 'products.php';
$pageHeader = 'Edit Product';
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
      <form action="../controllers/productController.php?action=edit" method="POST" enctype="multipart/form-data">
        
        <!-- Hidden required states for POST parsing -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product['image']); ?>">

        <div class="mb-4">
          <label for="name" class="form-label fw-bold">Product Name</label>
          <input type="text" name="name" id="name" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <label for="price" class="form-label fw-bold">Price ($)</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($product['price']); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="discounted_price" class="form-label fw-bold">Discounted Price ($)</label>
            <input type="number" step="0.01" name="discounted_price" id="discounted_price" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($product['discounted_price'] ?? ''); ?>" placeholder="Optional">
            <div class="form-text text-muted">Leave empty to remove discount.</div>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <label for="stock" class="form-label fw-bold">Stock Quantity</label>
            <input type="number" name="stock" id="stock" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="stock_limit" class="form-label fw-bold">Low Stock Limit</label>
            <input type="number" name="stock_limit" id="stock_limit" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($product['stock_limit'] ?? 20); ?>" min="0" required>
            <div class="form-text text-muted">Dashboard alerts when stock is less than or equal to this limit.</div>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Description</label>
          <textarea name="description" class="form-control rounded-3 border-light bg-light" rows="4" placeholder="Enter product details..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
        </div>

        <div class="mb-4">
          <label for="category" class="form-label fw-bold">Category</label>
          <select name="category" id="category" class="form-select bg-light border-0 py-2" required>
            <option value="Organic" <?php echo $product['category'] === 'Organic' ? 'selected' : ''; ?>>Organic</option>
            <option value="Root" <?php echo $product['category'] === 'Root' ? 'selected' : ''; ?>>Root Vegetables</option>
            <option value="Greens" <?php echo $product['category'] === 'Greens' ? 'selected' : ''; ?>>Greens</option>
            <option value="Onions & Garlic" <?php echo $product['category'] === 'Onions & Garlic' ? 'selected' : ''; ?>>Onions & Garlic</option>
          </select>
        </div>

        <div class="mb-5">
          <label for="image" class="form-label fw-bold">Update Image</label>
          <div class="d-flex align-items-center gap-3">
             <?php $imgSrc = str_starts_with($product['image'], 'http') ? $product['image'] : '../' . $product['image']; ?>
             <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Current" class="img-preview shadow-sm border border-light">
             <input class="form-control bg-light border-0 py-2" type="file" id="image" name="image" accept="image/*">
          </div>
          <div class="form-text text-muted mt-2">Leave input blank to keep existing image.</div>
        </div>

        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm rounded-pill"><i class="fa-solid fa-save me-2"></i> Save Changes</button>
      </form>
    </div>
  </main>
</body>
</html>
