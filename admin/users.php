<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User($pdo);
$users = $userModel->getAllUsers();
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
            <tr>
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
                  <span class="badge bg-primary bg-opacity-10 text-primary border rounded-pill px-3 py-2"><i class="fa-solid fa-shield-halved me-1"></i> Admin</span>
                <?php else: ?>
                  <span class="badge bg-light text-dark border rounded-pill px-3 py-2">Customer</span>
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
</body>
</html>
