<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User($pdo);
$users = $userModel->getAllUsers();

$roleFilter = trim((string)($_GET['role'] ?? 'all'));
$search = trim((string)($_GET['q'] ?? ''));

$users = array_values(array_filter($users, function ($u) use ($roleFilter, $search) {
  $matchesRole = $roleFilter === 'all' || strcasecmp((string)$u['role'], $roleFilter) === 0;
  $haystack = strtolower((string)(($u['name'] ?? '') . ' ' . ($u['email'] ?? '')));
  $matchesSearch = $search === '' || strpos($haystack, strtolower($search)) !== false;
  return $matchesRole && $matchesSearch;
}));

$adminCount = 0;
$customerCount = 0;
foreach ($users as $u) {
  if (($u['role'] ?? '') === 'admin') {
    $adminCount++;
  } else {
    $customerCount++;
  }
}
$pageTitle = 'Manage Users - Vegora Admin';
$pageHeader = 'Registered Users';
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

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
      <form method="GET" class="row g-3 align-items-end admin-filter-grid">
        <div class="col-lg-5 col-md-6">
          <label class="form-label fw-bold">Search User</label>
          <input type="text" id="usersFilterSearch" name="q" class="form-control admin-filter-control bg-light border-0" value="<?php echo htmlspecialchars($search); ?>" placeholder="Type name or email">
        </div>
        <div class="col-lg-3 col-md-6">
          <label class="form-label fw-bold">Role</label>
          <select id="usersFilterRole" name="role" class="form-select admin-filter-control bg-light border-0">
            <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All</option>
            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
            <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customer</option>
          </select>
        </div>
        <div class="col-lg-4 col-md-12 admin-filter-actions">
          <button type="button" id="usersFilterApply" class="btn btn-primary admin-filter-btn rounded-pill px-4">Apply</button>
          <a href="users.php" class="btn btn-light border admin-filter-btn rounded-pill px-4">Reset</a>
        </div>
      </form>
      <!-- <div class="small text-muted mt-3">Filters update table live while typing or changing role.</div> -->
      <div class="d-flex flex-wrap gap-2 mt-3">
        <span class="badge badge-chip badge-chip-neutral" id="usersVisibleCount">Showing: <?php echo count($users); ?></span>
        <span class="badge badge-chip badge-chip-primary" id="usersAdminCount">Admins: <?php echo $adminCount; ?></span>
        <span class="badge badge-chip badge-chip-success" id="usersCustomerCount">Customers: <?php echo $customerCount; ?></span>
      </div>
    </div>

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
              <th scope="col" class="text-uppercase text-muted fw-bold small">ID</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Name</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Email</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Role</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Joined Date</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr data-name="<?php echo htmlspecialchars(strtolower((string)($u['name'] ?? ''))); ?>" data-email="<?php echo htmlspecialchars(strtolower((string)($u['email'] ?? ''))); ?>" data-role="<?php echo htmlspecialchars(strtolower((string)($u['role'] ?? 'customer'))); ?>">
              <td><span class="fw-bold text-muted">#<?php echo $u['id']; ?></span></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px;">
                      <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                    </div>
                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($u['name']); ?></span>
                </div>
              </td>
              <td><span class="text-muted fw-semibold"><?php echo htmlspecialchars($u['email']); ?></span></td>
              <td>
                <?php if ($u['role'] === 'admin'): ?>
                  <span class="badge badge-chip badge-chip-primary"><i class="fa-solid fa-shield-halved me-1"></i> Admin</span>
                <?php else: ?>
                  <span class="badge badge-chip badge-chip-success">Customer</span>
                <?php endif; ?>
              </td>
              <td><span class="text-muted"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></span></td>
              <td class="text-end">
                <?php if ($u['role'] === 'admin'): ?>
                   <button class="btn btn-sm btn-light text-muted rounded-pill shadow-sm px-3 fw-bold disabled">Delete</button>
                <?php else: ?>
                   <a href="../controllers/userController.php?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-light text-danger rounded-pill shadow-sm px-3 fw-bold" onclick="return confirm('Delete this user? This will also wipe their orders and cart permanently!');">
                      <i class="fa-solid fa-trash-can me-1"></i> Delete
                   </a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">No users found? Something went wrong.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      const searchInput = document.getElementById('usersFilterSearch');
      const roleSelect = document.getElementById('usersFilterRole');
      const rows = Array.from(document.querySelectorAll('tbody tr[data-name]'));
      const visibleCount = document.getElementById('usersVisibleCount');
      const adminCount = document.getElementById('usersAdminCount');
      const customerCount = document.getElementById('usersCustomerCount');

      function applyUserFilters() {
        const query = (searchInput?.value || '').toLowerCase().trim();
        const role = (roleSelect?.value || 'all').toLowerCase();
        let shown = 0;
        let admins = 0;
        let customers = 0;

        rows.forEach((row) => {
          const name = row.dataset.name || '';
          const email = row.dataset.email || '';
          const rowRole = row.dataset.role || 'customer';

          const matchesSearch = query === '' || name.includes(query) || email.includes(query);
          const matchesRole = role === 'all' || role === rowRole;
          const matches = matchesSearch && matchesRole;

          row.style.display = matches ? '' : 'none';
          if (matches) {
            shown++;
            if (rowRole === 'admin') {
              admins++;
            } else {
              customers++;
            }
          }
        });

        if (visibleCount) visibleCount.textContent = 'Showing: ' + shown;
        if (adminCount) adminCount.textContent = 'Admins: ' + admins;
        if (customerCount) customerCount.textContent = 'Customers: ' + customers;
      }

      if (searchInput) searchInput.addEventListener('input', applyUserFilters);
      if (roleSelect) roleSelect.addEventListener('change', applyUserFilters);

      const applyBtn = document.getElementById('usersFilterApply');
      if (applyBtn) applyBtn.addEventListener('click', applyUserFilters);

      applyUserFilters();
    })();
  </script>
</body>
</html>
