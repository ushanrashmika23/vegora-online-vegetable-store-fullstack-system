<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Category.php';

$categoryModel = new Category($pdo);
$categories = $categoryModel->getAll();

$pageTitle = 'Manage Categories - Vegora Admin';
$pageHeader = 'Product Categories';
require_once __DIR__ . '/includes/header.php';
?>

  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
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

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card bg-white border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3">Add Category</h5>
          <form action="../controllers/categoryController.php?action=add" method="POST" class="d-flex flex-column gap-3">
            <div>
              <label for="new_name" class="form-label fw-bold">Category Name</label>
              <input type="text" id="new_name" name="name" class="form-control bg-light border-0" placeholder="e.g. Leafy Greens" required>
            </div>
            <button type="submit" class="btn btn-success rounded-pill"><i class="fa-solid fa-plus me-1"></i> Add Category</button>
          </form>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card bg-white border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3">Existing Categories</h5>

          <?php if (empty($categories)): ?>
            <p class="text-muted mb-0">No categories found yet.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="text-uppercase text-muted small">Name</th>
                    <th class="text-uppercase text-muted small">Created</th>
                    <th class="text-uppercase text-muted small text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($categories as $cat): ?>
                    <tr>
                      <td>
                        <form action="../controllers/categoryController.php?action=edit" method="POST" class="d-flex gap-2 align-items-center">
                          <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
                          <input type="text" name="name" class="form-control form-control-sm bg-light border-0" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                          <button type="submit" class="btn btn-sm btn-light text-primary rounded-circle" style="width:32px;height:32px;"><i class="fa-solid fa-check"></i></button>
                        </form>
                      </td>
                      <td class="text-muted small"><?php echo date('M d, Y', strtotime($cat['created_at'])); ?></td>
                      <td class="text-end">
                        <a href="../controllers/categoryController.php?action=delete&id=<?php echo (int)$cat['id']; ?>" class="btn btn-sm btn-light text-danger rounded-circle" style="width:32px;height:32px;" onclick="return confirm('Delete this category?');">
                          <i class="fa-solid fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
