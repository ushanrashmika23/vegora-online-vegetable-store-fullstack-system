<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Order.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderModel = new Order($pdo);
$orders = $orderModel->getUserOrders($_SESSION['user_id']);

// Pre-fetch items for each customer order for quick summary modals
$orderItemsInfo = [];
foreach ($orders as $orderRow) {
  $orderItemsInfo[$orderRow['id']] = $orderModel->getOrderDetails($orderRow['id']);
}

/**
 * Helper function to determine timeline progress UI
 */
function getProgressWidth($status) {
    if ($status === 'Placed') return '25%';
    if ($status === 'Packed') return '50%';
    if ($status === 'Shipped') return '75%';
    if ($status === 'Delivered') return '100%';
    return '0%'; // Cancelled
}

function getProgressColor($status) {
    if ($status === 'Cancelled') return 'bg-danger';
    if ($status === 'Delivered') return 'bg-success';
    return 'bg-primary';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - Vegora</title>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    .timeline-bg {
        background: #f1f5f9;
        height: 6px;
        border-radius: 10px;
        position: relative;
        margin: 20px 0;
    }
    .timeline-progress {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease-in-out;
    }
    .timeline-step {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #fff;
        border: 4px solid #f1f5f9;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #94a3b8;
      font-size: 0.62rem;
    }
    .step-1 { left: 0%; }
    .step-2 { left: 33.33%; }
    .step-3 { left: 66.66%; }
    .step-4 { left: 100%; transform: translate(-100%, -50%); }
    
    .timeline-step.active { border-color: var(--vegi-green); background: var(--vegi-green); box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2); }
    .timeline-step.active i { color: #ffffff; }
  </style>
</head>
<body class="bg-light">

  <?php require_once 'includes/navbar.php'; ?>

  <div class="bg-white py-5 mb-5 border-bottom">
    <div class="container text-center">
      <h1 class="display-4 fw-bold text-dark mb-2">My Orders</h1>
      <p class="text-muted">Track your fresh veggies from our farm to your door.</p>
    </div>
  </div>

  <main class="container mb-5 pb-5" style="max-width: 900px;">
    
    <?php if (isset($_SESSION['orders_success'])): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i> <?php echo $_SESSION['orders_success']; unset($_SESSION['orders_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['orders_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $_SESSION['orders_error']; unset($_SESSION['orders_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="bg-white p-5 rounded-4 shadow-sm text-center border border-light">
          <i class="fa-solid fa-box-open fs-1 text-muted opacity-25 mb-4 d-block"></i>
          <h3 class="fw-bold mb-3 text-dark">You have no orders yet!</h3>
          <p class="text-muted mb-4">Start shopping for fresh, organic vegetables today.</p>
          <a href="shop.php" class="btn btn-primary rounded-pill px-5 btn-lg">Browse Shop</a>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-4">
        <?php foreach ($orders as $o): ?>
          <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <span class="fs-5 fw-bold text-dark d-block">Order #<?php echo $o['id']; ?></span>
                    <span class="text-muted small fw-semibold"><i class="fa-regular fa-calendar me-1"></i> <?php echo date('F d, Y \a\t h:i A', strtotime($o['created_at'])); ?></span>
                </div>
                <div class="text-end">
                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Amount</span>
                    <span class="fs-4 fw-bold text-success">$<?php echo number_format($o['total'], 2); ?></span>
                </div>
            </div>

            <!-- Visual Tracker -->
            <div class="px-2 px-md-4 mb-4 mt-2">
                <?php if ($o['status'] === 'Cancelled'): ?>
                    <div class="alert alert-danger border-0 d-flex align-items-center mb-0">
                        <i class="fa-solid fa-circle-xmark fs-4 me-3"></i> 
                        <div>
                            <h6 class="mb-0 fw-bold">Order Cancelled</h6>
                            <span class="small">This order will not be fulfilled. Reach out to support if you have questions.</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between mb-2 text-muted fw-bold small text-uppercase" style="font-size: 0.75rem;">
                        <span>Placed</span>
                        <span class="ps-4">Packed</span>
                        <span class="pe-3">Shipped</span>
                        <span>Delivered</span>
                    </div>
                    
                    <div class="timeline-bg">
                        <div class="timeline-progress <?php echo getProgressColor($o['status']); ?>" style="width: <?php echo getProgressWidth($o['status']); ?>"></div>
                      <div class="timeline-step step-1 <?php echo in_array($o['status'], ['Placed', 'Packed', 'Shipped', 'Delivered']) ? 'active' : ''; ?>"><i class="fa-solid fa-cart-shopping"></i></div>
                      <div class="timeline-step step-2 <?php echo in_array($o['status'], ['Packed', 'Shipped', 'Delivered']) ? 'active' : ''; ?>"><i class="fa-solid fa-box-open"></i></div>
                      <div class="timeline-step step-3 <?php echo in_array($o['status'], ['Shipped', 'Delivered']) ? 'active' : ''; ?>"><i class="fa-solid fa-truck-fast"></i></div>
                      <div class="timeline-step step-4 <?php echo in_array($o['status'], ['Delivered']) ? 'active' : ''; ?>"><i class="fa-solid fa-check"></i></div>
                    </div>
                    
                    <div class="text-center mt-3 text-muted small fw-semibold">
                        <?php if ($o['status'] === 'Placed'): ?>
                            <i class="fa-solid fa-boxes-packing text-primary me-1"></i> We've received your order and are gathering your items.
                        <?php elseif ($o['status'] === 'Packed'): ?>
                            <i class="fa-solid fa-box text-warning me-1"></i> Your items are packed and waiting for the driver!
                        <?php elseif ($o['status'] === 'Shipped'): ?>
                            <i class="fa-solid fa-truck-fast text-info me-1"></i> Your order is on the way!
                        <?php elseif ($o['status'] === 'Delivered'): ?>
                            <i class="fa-solid fa-check-circle text-success me-1"></i> Enjoy your fresh veggies!
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Interaction Buttons -->
            <div class="text-end border-top pt-3 mt-2">
              <a href="download_invoice.php?order_id=<?php echo $o['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-4 fw-bold me-2">
                <i class="fa-solid fa-file-pdf me-1"></i> Download Invoice
              </a>
                <button type="button" class="btn btn-light btn-sm rounded-pill px-4 fw-bold me-2" data-bs-toggle="modal" data-bs-target="#orderSummaryModal<?php echo $o['id']; ?>">
                    <i class="fa-solid fa-list-ul me-1"></i> View Summary
                </button>
                <?php if ($o['status'] === 'Placed'): ?>
                    <form action="controllers/orderController.php?action=cancel_user" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold me-2"><i class="fa-solid fa-ban me-1"></i> Cancel Order</button>
                    </form>
                <?php endif; ?>
                <a href="shop.php" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold"><i class="fa-solid fa-rotate-left me-1"></i> Order Again</a>
            </div>
          </div>

          <!-- Order Summary Modal -->
          <div class="modal fade" id="orderSummaryModal<?php echo $o['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 bg-light rounded-top-4 p-4">
                  <div>
                    <h4 class="modal-title fw-bold text-dark mb-1">Order #<?php echo $o['id']; ?> Summary</h4>
                    <span class="text-muted fw-semibold small"><?php echo date('M d, Y h:i A', strtotime($o['created_at'])); ?></span>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                  <h6 class="fw-bold text-uppercase text-muted small mb-3">Purchased Items</h6>
                  <ul class="list-group list-group-flush mb-4 border rounded-3 overflow-hidden">
                    <?php
                    $items = $orderItemsInfo[$o['id']] ?? [];
                    foreach ($items as $i):
                        $imgSrc = str_starts_with($i['image'], 'http') ? $i['image'] : $i['image'];
                    ?>
                      <li class="list-group-item p-3 d-flex justify-content-between align-items-center bg-white border-light">
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($i['product_name']); ?>" class="rounded-3 border" style="width: 52px; height: 52px; object-fit: cover;">
                          <div>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($i['product_name']); ?></div>
                            <div class="small text-muted">$<?php echo number_format($i['price'], 2); ?> each</div>
                          </div>
                        </div>
                        <div class="fw-bold text-dark px-3 bg-light rounded-pill border">x<?php echo $i['quantity']; ?></div>
                        <div class="fw-bold text-success" style="width: 85px; text-align: right;">$<?php echo number_format($i['price'] * $i['quantity'], 2); ?></div>
                      </li>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                      <li class="list-group-item p-4 text-center text-muted">No items found for this order.</li>
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
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>

  <?php require_once 'includes/footer.php'; ?>
  <script src="assets/js/cart.js"></script>
</body>
</html>
