<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../controllers/productController.php';

$products = getAllProducts();

$categoryOptions = [];
foreach ($products as $product) {
  $categoryName = trim((string)($product['category'] ?? 'Uncategorized'));
  if ($categoryName === '') {
    $categoryName = 'Uncategorized';
  }
  $categoryOptions[$categoryName] = $categoryName;
}
ksort($categoryOptions, SORT_NATURAL | SORT_FLAG_CASE);

function categoryBadgeClass(string $category): string {
  $styles = [
    'badge-chip-primary',
    'badge-chip-success',
    'badge-chip-warning',
    'badge-chip-info',
    'badge-chip-neutral'
  ];
  $index = abs(crc32(strtolower($category))) % count($styles);
  return $styles[$index];
}

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
      <div class="row g-3 mb-4 admin-filter-grid">
        <div class="col-lg-5 col-md-4">
          <label for="productFilterSearch" class="form-label small text-uppercase text-muted fw-bold mb-2">Search Product</label>
          <div class="input-group">
            <span class="input-group-text admin-filter-control bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" id="productFilterSearch" class="form-control admin-filter-control border-start-0" placeholder="Type name or category...">
          </div>
        </div>
        <div class="col-lg-3 col-md-3">
          <label for="productFilterCategory" class="form-label small text-uppercase text-muted fw-bold mb-2">Category</label>
          <select id="productFilterCategory" class="form-select admin-filter-control">
            <option value="">All Categories</option>
            <?php foreach ($categoryOptions as $categoryName): ?>
              <option value="<?php echo htmlspecialchars(strtolower($categoryName)); ?>"><?php echo htmlspecialchars($categoryName); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-lg-2 col-md-3">
          <label for="productFilterStock" class="form-label small text-uppercase text-muted fw-bold mb-2">Stock Status</label>
          <select id="productFilterStock" class="form-select admin-filter-control">
            <option value="">All</option>
            <option value="out">Out of Stock</option>
            <option value="low">Low Stock</option>
            <option value="ok">In Stock</option>
          </select>
        </div>
        <div class="col-lg-2 col-md-2 admin-filter-actions">
          <button type="button" id="productFilterReset" class="btn btn-light border admin-filter-btn px-4">
            <i class="fa-solid fa-rotate-left me-2"></i>Reset
          </button>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- <div class="small text-muted">Live results update while you type</div> -->
        <div class="badge badge-chip badge-chip-neutral" id="productsVisibleCount"><?php echo count($products); ?> shown</div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="productsTable">
          <thead class="table-light">
            <tr>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Product</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Category</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Price</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Stock</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="productsTableBody">
            <?php foreach ($products as $p): ?>
            <?php
              $productName = (string)($p['name'] ?? '');
              $categoryName = trim((string)($p['category'] ?? 'Uncategorized'));
              if ($categoryName === '') {
                  $categoryName = 'Uncategorized';
              }
              $stockValue = (int)($p['stock'] ?? 0);
              $stockLimitValue = (int)($p['stock_limit'] ?? 20);
              if ($stockLimitValue < 0) {
                  $stockLimitValue = 0;
              }
              $stockStatus = 'ok';
              if ($stockValue <= 0) {
                  $stockStatus = 'out';
              } elseif ($stockValue <= $stockLimitValue) {
                  $stockStatus = 'low';
              }
            ?>
            <tr data-name="<?php echo htmlspecialchars(strtolower($productName)); ?>" data-category="<?php echo htmlspecialchars(strtolower($categoryName)); ?>" data-stock-status="<?php echo $stockStatus; ?>">
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
              <td>
                <span class="badge badge-chip <?php echo categoryBadgeClass($categoryName); ?>"><?php echo htmlspecialchars($categoryName); ?></span>
              </td>
              <td class="fw-semibold text-muted">
                <?php if (!empty($p['discounted_price']) && $p['discounted_price'] > 0 && $p['discounted_price'] < $p['price']): ?>
                  <span class="text-decoration-line-through text-secondary me-2">$<?php echo number_format($p['price'], 2); ?></span>
                  <span class="text-success fw-bold">$<?php echo number_format($p['discounted_price'], 2); ?></span>
                <?php else: ?>
                  $<?php echo number_format($p['price'], 2); ?>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($stockStatus === 'out'): ?>
                  <span class="badge badge-chip badge-chip-danger">Out: <?php echo $stockValue; ?></span>
                  <span class="small text-muted ms-2">limit: <?php echo $stockLimitValue; ?></span>
                <?php elseif ($stockStatus === 'low'): ?>
                  <span class="badge badge-chip badge-chip-danger">Low: <?php echo $stockValue; ?></span>
                  <span class="small text-muted ms-2">limit: <?php echo $stockLimitValue; ?></span>
                <?php else: ?>
                  <span class="badge badge-chip badge-chip-success">In Stock: <?php echo $stockValue; ?></span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <form method="POST" action="../controllers/productController.php?action=restock" class="d-inline-flex align-items-center gap-2 me-2">
                  <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                  <input
                    type="number"
                    name="amount"
                    value="0"
                    min="1"
                    step="1"
                    class="form-control form-control-sm"
                    style="width: 72px;"
                    aria-label="Stock quantity"
                    title="Stock quantity"
                  >
                  <button type="submit" class="btn btn-sm btn-success" title="Add stock">
                    Add Stock
                  </button>
                </form>
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
  <script>
    (function () {
      const searchInput = document.getElementById('productFilterSearch');
      const categorySelect = document.getElementById('productFilterCategory');
      const stockSelect = document.getElementById('productFilterStock');
      const resetButton = document.getElementById('productFilterReset');
      const visibleCount = document.getElementById('productsVisibleCount');
      const rows = Array.from(document.querySelectorAll('#productsTableBody tr[data-name]'));

      function applyFilters() {
        const query = (searchInput.value || '').toLowerCase().trim();
        const category = (categorySelect.value || '').toLowerCase();
        const stock = (stockSelect.value || '').toLowerCase();
        let shown = 0;

        rows.forEach((row) => {
          const name = row.dataset.name || '';
          const rowCategory = row.dataset.category || '';
          const rowStock = row.dataset.stockStatus || '';

          const matchesSearch = query === '' || name.includes(query) || rowCategory.includes(query);
          const matchesCategory = category === '' || rowCategory === category;
          const matchesStock = stock === '' || rowStock === stock;

          const matches = matchesSearch && matchesCategory && matchesStock;
          row.style.display = matches ? '' : 'none';
          if (matches) shown++;
        });

        if (visibleCount) {
          visibleCount.textContent = shown + ' shown';
        }
      }

      [searchInput, categorySelect, stockSelect].forEach((el) => {
        if (!el) return;
        el.addEventListener('input', applyFilters);
        el.addEventListener('change', applyFilters);
      });

      if (resetButton) {
        resetButton.addEventListener('click', function () {
          searchInput.value = '';
          categorySelect.value = '';
          stockSelect.value = '';
          applyFilters();
          searchInput.focus();
        });
      }

      applyFilters();
    })();
  </script>
</body>
</html>
