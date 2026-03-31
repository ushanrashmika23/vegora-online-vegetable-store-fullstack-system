<?php
// admin/coupons.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// Handle POST specific to this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'] === 'fixed' ? 'fixed' : 'percent';
    $value = (float)($_POST['value'] ?? 0);

    if (empty($code) || $value <= 0) {
        $_SESSION['admin_error'] = "Valid code and discount value are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value) VALUES (?, ?, ?)");
            if ($stmt->execute([$code, $type, $value])) {
                $_SESSION['admin_success'] = "Coupon '$code' created successfully!";
            }
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Could not create coupon. The code may already exist.";
        }
    }
    header('Location: coupons.php');
    exit;
}

// Handle toggling active state
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE coupons SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['admin_success'] = "Coupon status updated.";
    header('Location: coupons.php');
    exit;
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['admin_success'] = "Coupon completely deleted.";
    header('Location: coupons.php');
    exit;
}

// Fetch all coupons and how many times they were used
$stmt = $pdo->query("
    SELECT c.*, (SELECT COUNT(id) FROM user_coupons WHERE coupon_id = c.id) as usages 
    FROM coupons c 
    ORDER BY c.created_at DESC
");
$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Coupons - Vegora Admin';
$pageHeader = 'Coupons & Discounts';
require_once __DIR__ . '/includes/header.php';
?>
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
  <main class="main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <?php if (isset($_SESSION['admin_error'])): ?>
      <div class="alert alert-danger shadow-sm border-0 mb-4 rounded-4"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['admin_success'])): ?>
      <div class="alert alert-success shadow-sm border-0 mb-4 rounded-4"><i class="fa-solid fa-circle-check me-2"></i> <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-5">
        <!-- New Coupon Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-plus text-success me-2"></i> Create Coupon</h5>
                <form action="coupons.php" method="POST">
                    <input type="hidden" name="add_coupon" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold small text-uppercase">Coupon Code</label>
                        <input type="text" name="code" class="form-control bg-light border-0 py-2 text-uppercase fw-bold text-dark" placeholder="e.g. SUMMER20" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold small text-uppercase">Discount Type</label>
                        <select name="type" class="form-select bg-light border-0 py-2 fw-semibold">
                            <option value="percent">Percentage (%)</option>
                            <option value="fixed">Fixed Amount ($)</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small text-uppercase">Value</label>
                        <input type="number" step="0.01" name="value" class="form-control bg-light border-0 py-2 fw-bold text-success" placeholder="e.g. 20" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2"><i class="fa-solid fa-magic me-2"></i> Generate Coupon</button>
                </form>
            </div>
        </div>

        <!-- Coupons Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom border-light pt-4 pb-3 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-tags text-primary me-2"></i> Active & History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Code</th>
                                <th class="py-3 border-0">Discount</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="py-3 border-0 text-center">Usages</th>
                                <th class="pe-4 py-3 border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php if (empty($coupons)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No coupons created yet! Make your first discount code on the left.</td></tr>
                            <?php else: ?>
                                <?php foreach ($coupons as $c): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-dark fs-5">
                                        <i class="fa-solid fa-ticket me-2 text-warning opacity-50"></i><?php echo htmlspecialchars($c['code']); ?>
                                    </td>
                                    <td class="py-3 fw-bold <?php echo $c['discount_type'] === 'fixed' ? 'text-success' : 'text-primary'; ?>">
                                        <?php echo $c['discount_type'] === 'fixed' ? '-$' . rtrim(rtrim($c['discount_value'], '0'), '.') : '-' . rtrim(rtrim($c['discount_value'], '0'), '.') . '%'; ?>
                                    </td>
                                    <td class="py-3 text-center">
                                        <?php if ($c['is_active']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold"><i class="fa-solid fa-ban me-1"></i> Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-center fw-semibold text-muted">
                                        <span class="badge bg-light text-dark border px-2 py-1"><?php echo $c['usages']; ?></span>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <a href="coupons.php?action=toggle&id=<?php echo $c['id']; ?>" class="btn btn-sm <?php echo $c['is_active'] ? 'btn-outline-warning' : 'btn-outline-success'; ?> rounded-pill px-3 fw-bold me-2">
                                            <?php echo $c['is_active'] ? 'Disable' : 'Enable'; ?>
                                        </a>
                                        <a href="coupons.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px;" onclick="return confirm('Delete this coupon permanently?');">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
