<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Order.php';

$orderModel = new Order($pdo);
$orders = $orderModel->getAllOrders();

// Pre-fetch items for each order so we can display them in modals cleanly
$orderItemsInfo = [];
foreach ($orders as $o) {
    $orderItemsInfo[$o['id']] = $orderModel->getOrderDetails($o['id']);
}

$pageTitle = 'Manage Orders - Vegora Admin';
$pageHeader = 'Manage Orders';
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
              <th scope="col" class="text-uppercase text-muted fw-bold small">Order ID</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Customer</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Date</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Total</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small">Status</th>
              <th scope="col" class="text-uppercase text-muted fw-bold small text-end">Action / Items</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
              <td><span class="fw-bold text-dark">#<?php echo $o['id']; ?></span></td>
              <td>
                <div class="fw-bold text-dark"><?php echo htmlspecialchars($o['user_name']); ?></div>
                <div class="small text-muted"><?php echo htmlspecialchars($o['user_email']); ?></div>
              </td>
              <td><span class="text-muted fw-semibold"><?php echo date('M d, Y h:i A', strtotime($o['created_at'])); ?></span></td>
              <td class="fw-bold text-success">$<?php echo number_format($o['total'], 2); ?></td>
              <td>
                <!-- Inline Status Update Form -->
                <form action="../controllers/orderController.php?action=update_status" method="POST" class="m-0">
                  <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                  <?php $bgColorClass = "custom-status-bg-" . $o['status']; ?>
                  <select name="status" class="form-select form-select-sm rounded-pill border-0 shadow-sm px-3 <?php echo $bgColorClass; ?>" onchange="this.form.submit()">
                    <option value="Placed" <?php echo $o['status'] === 'Placed' ? 'selected' : ''; ?>>Placed</option>
                    <option value="Packed" <?php echo $o['status'] === 'Packed' ? 'selected' : ''; ?>>Packed</option>
                    <option value="Shipped" <?php echo $o['status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="Delivered" <?php echo $o['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="Cancelled" <?php echo $o['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                  </select>
                </form>
              </td>
              <td class="text-end">
                 <a href="../download_invoice.php?order_id=<?php echo $o['id']; ?>" class="btn btn-sm btn-light text-danger rounded-pill shadow-sm px-3 fw-bold me-2">
                   <i class="fa-solid fa-file-pdf me-1"></i> Invoice
                 </a>
                <button class="btn btn-sm btn-light text-primary rounded-pill shadow-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $o['id']; ?>">
                   <i class="fa-solid fa-list me-1"></i> Details
                </button>
              </td>
            </tr>

            <!-- Modal for this specific order -->
            <div class="modal fade" id="orderModal<?php echo $o['id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                  <div class="modal-header border-bottom-0 bg-light rounded-top-4 p-4">
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-1">Order #<?php echo $o['id']; ?></h4>
                        <span class="text-muted fw-semibold small"><?php echo htmlspecialchars($o['user_name']); ?> &bull; <?php echo date('M d, Y', strtotime($o['created_at'])); ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body p-4">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3">Purchased Items</h6>
                    <ul class="list-group list-group-flush mb-4 border rounded-3 overflow-hidden">
                       <?php 
                       $items = $orderItemsInfo[$o['id']] ?? [];
                       foreach($items as $i): 
                          $imgSrc = str_starts_with($i['image'], 'http') ? $i['image'] : '../' . $i['image'];
                       ?>
                         <li class="list-group-item p-3 d-flex justify-content-between align-items-center bg-white border-light">
                            <div class="d-flex align-items-center gap-3">
                               <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="img" class="prod-thumb-sm border">
                               <div>
                                  <div class="fw-bold text-dark"><?php echo htmlspecialchars($i['product_name']); ?></div>
                                  <div class="small text-muted">$<?php echo number_format($i['price'], 2); ?> each</div>
                               </div>
                            </div>
                            <div class="fw-bold text-dark px-3 bg-light rounded-pill border">x<?php echo $i['quantity']; ?></div>
                            <div class="fw-bold text-success" style="width: 80px; text-align: right;">$<?php echo number_format($i['price'] * $i['quantity'], 2); ?></div>
                         </li>
                       <?php endforeach; ?>
                       <?php if(empty($items)): ?>
                         <li class="list-group-item p-4 text-center text-muted">No items found.</li>
                       <?php endif; ?>
                    </ul>
                    
                    <div class="d-flex justify-content-end align-items-center border-top pt-3">
                       <span class="text-muted fw-bold me-3">Grand Total:</span>
                       <span class="fs-4 fw-bold text-success">$<?php echo number_format($o['total'], 2); ?></span>
                    </div>
                  </div>
                  <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Modal -->
            
            <?php endforeach; ?>
            
            <?php if (empty($orders)): ?>
            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa-solid fa-basket-shopping fs-2 mb-2 opacity-50"></i><br>No orders received yet!</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
