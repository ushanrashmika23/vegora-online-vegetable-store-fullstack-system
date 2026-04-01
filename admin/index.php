<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

$orderModel = new Order($pdo);
$orders = $orderModel->getAllOrders();

$userModel = new User($pdo);
$users = $userModel->getAllUsers();

// --- 1. KEY PERFORMANCE INDICATORS ---
$totalRevenue = 0;
$activeOrders = 0;
$statusCounts = [
    'Placed' => 0,
    'Packed' => 0,
    'Shipped' => 0,
    'Delivered' => 0,
    'Cancelled' => 0
];

foreach ($orders as $o) {
    if (isset($statusCounts[$o['status']])) {
        $statusCounts[$o['status']]++;
    }
    if ($o['status'] === 'Delivered') {
        $totalRevenue += $o['total'];
    }
    if (in_array($o['status'], ['Placed', 'Packed', 'Shipped'])) {
        $activeOrders++;
    }
}
$totalUsers = count($users);

$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
$todayOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$monthlyRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'Delivered' AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn();

$stmtRecent = $pdo->query("SELECT o.id, o.total, o.status, o.created_at, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 6");
$recentOrders = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

$stmtTopCustomers = $pdo->query("SELECT u.id, u.name, u.email, COUNT(o.id) AS order_count, COALESCE(SUM(o.total), 0) AS lifetime_value FROM users u JOIN orders o ON o.user_id = u.id WHERE o.status != 'Cancelled' GROUP BY u.id ORDER BY lifetime_value DESC LIMIT 5");
$topCustomers = $stmtTopCustomers->fetchAll(PDO::FETCH_ASSOC);

// --- 2. TRAILING 7-DAY REVENUE ANALYTICS ---
$last7Days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $last7Days[$date] = 0;
}

$stmtRev = $pdo->query("
    SELECT DATE(created_at) as date, SUM(total) as daily_total 
    FROM orders 
    WHERE status = 'Delivered' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY DATE(created_at)
");
while ($row = $stmtRev->fetch(PDO::FETCH_ASSOC)) {
    if (isset($last7Days[$row['date']])) {
        $last7Days[$row['date']] = (float)$row['daily_total'];
    }
}

// Convert dates to friendlier format (e.g., "Mon 24")
$revenueLabels = [];
foreach (array_keys($last7Days) as $rawDate) {
    $revenueLabels[] = date('D d', strtotime($rawDate));
}
$revenueLabelsJson = json_encode($revenueLabels);
$revenueDataJson = json_encode(array_values($last7Days));

// JSON for Status Doughnut
$statusLabelsJson = json_encode(array_keys($statusCounts));
$statusDataJson = json_encode(array_values($statusCounts));

// --- 3. LOW STOCK PRODUCTS (Dynamic threshold per product) ---
$stmtLow = $pdo->query("SELECT id, name, image, stock, stock_limit FROM products WHERE stock <= stock_limit ORDER BY stock ASC, name ASC LIMIT 8");
$lowStockProducts = $stmtLow->fetchAll(PDO::FETCH_ASSOC);
$lowStockCount = count($lowStockProducts);

// --- 4. TOP SELLING PRODUCTS ---
$stmtTop = $pdo->query("
  SELECT p.id, p.name, p.image, p.price, p.discounted_price, p.stock, p.stock_limit,
       CASE WHEN p.discounted_price IS NOT NULL AND p.discounted_price > 0 AND p.discounted_price < p.price THEN p.discounted_price ELSE p.price END AS effective_price,
       SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Cancelled'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$topProducts = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

function orderStatusBadgeClass(string $status): string {
  return match ($status) {
    'Placed' => 'badge-chip-primary',
    'Packed' => 'badge-chip-info',
    'Shipped' => 'badge-chip-warning',
    'Delivered' => 'badge-chip-success',
    'Cancelled' => 'badge-chip-danger',
    default => 'badge-chip-neutral'
  };
}

$pageTitle = 'Dashboard - Vegora Admin';
$pageHeader = 'Analytics Overview';
require_once __DIR__ . '/includes/header.php';
?>

  <!-- Injected Sidebar -->
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content">
    
    <!-- Injected Topbar -->
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <!-- Stat Widgets -->
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card card-stat bg-white h-100 p-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-center position-relative z-1">
            <div>
              <p class="text-muted fw-bold mb-1 text-uppercase small" style="letter-spacing: 1px;">Total Revenue</p>
              <h3 class="fw-bold mb-0 text-dark">$<?php echo number_format($totalRevenue, 2); ?></h3>
              <span class="text-success small fw-bold"><i class="fa-solid fa-arrow-trend-up me-1"></i> Lifetime Delivered</span>
            </div>
            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
              <i class="fa-solid fa-wallet fs-3"></i>
            </div>
          </div>
          <div class="position-absolute z-0 bg-success bg-opacity-10 rounded-circle" style="width: 150px; height: 150px; top: -50px; right: -50px; filter: blur(30px);"></div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card card-stat bg-white h-100 p-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-center position-relative z-1">
            <div>
              <p class="text-muted fw-bold mb-1 text-uppercase small" style="letter-spacing: 1px;">Active Orders</p>
              <h3 class="fw-bold mb-0 text-dark"><?php echo $activeOrders; ?></h3>
              <span class="text-primary small fw-bold"><i class="fa-solid fa-box-open me-1"></i> Awaiting Fulfillment</span>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
              <i class="fa-solid fa-truck-fast fs-3"></i>
            </div>
          </div>
          <div class="position-absolute z-0 bg-primary bg-opacity-10 rounded-circle" style="width: 150px; height: 150px; top: -50px; right: -50px; filter: blur(30px);"></div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card card-stat bg-white h-100 p-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-center position-relative z-1">
            <div>
              <p class="text-muted fw-bold mb-1 text-uppercase small" style="letter-spacing: 1px;">Registered Users</p>
              <h3 class="fw-bold mb-0 text-dark"><?php echo $totalUsers; ?></h3>
              <span class="text-info small fw-bold"><i class="fa-solid fa-user-group me-1"></i> Total Community</span>
            </div>
            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
              <i class="fa-solid fa-users fs-3"></i>
            </div>
          </div>
          <div class="position-absolute z-0 bg-info bg-opacity-10 rounded-circle" style="width: 150px; height: 150px; top: -50px; right: -50px; filter: blur(30px);"></div>
        </div>
      </div>
    </div>

    <!-- Operations Snapshot -->
    <div class="row g-4 mb-5">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="text-uppercase small fw-bold opacity-75">Today Orders</div>
            <span class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-basket-shopping"></i></span>
          </div>
          <h3 class="fw-bold mb-1"><?php echo $todayOrders; ?></h3>
          <small class="opacity-75 d-block mb-3">Orders placed since midnight</small>
          <a href="orders.php" class="text-white text-decoration-none small fw-bold">Open Orders <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="text-uppercase small fw-bold opacity-75">Monthly Revenue</div>
            <span class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-coins"></i></span>
          </div>
          <h3 class="fw-bold mb-1">$<?php echo number_format($monthlyRevenue, 2); ?></h3>
          <small class="opacity-75 d-block mb-3">Delivered orders this month</small>
          <span class="small fw-bold opacity-75"><i class="fa-solid fa-arrow-trend-up me-1"></i> Revenue momentum</span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="text-uppercase small fw-bold opacity-75">Products</div>
            <span class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-carrot"></i></span>
          </div>
          <h3 class="fw-bold mb-1"><?php echo $totalProducts; ?></h3>
          <small class="opacity-75 d-block mb-3">Active products in catalog</small>
          <a href="products.php" class="text-white text-decoration-none small fw-bold">Manage Products <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-white" style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="text-uppercase small fw-bold opacity-75">Categories</div>
            <span class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-layer-group"></i></span>
          </div>
          <h3 class="fw-bold mb-1"><?php echo $totalCategories; ?></h3>
          <small class="opacity-75 d-block mb-3">Structured category groups</small>
          <a href="categories.php" class="text-white text-decoration-none small fw-bold">Manage Categories <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-5">
      <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-bolt text-warning me-2"></i> Quick Actions</h5>
      <div class="d-flex flex-wrap gap-2">
        <a href="add_product.php" class="btn btn-success rounded-pill px-3"><i class="fa-solid fa-plus me-1"></i> Add Product</a>
        <a href="categories.php" class="btn btn-light rounded-pill px-3 border"><i class="fa-solid fa-layer-group me-1"></i> Manage Categories</a>
        <a href="orders.php" class="btn btn-light rounded-pill px-3 border"><i class="fa-solid fa-basket-shopping me-1"></i> Review Orders</a>
        <a href="coupons.php" class="btn btn-light rounded-pill px-3 border"><i class="fa-solid fa-ticket me-1"></i> Create Coupon</a>
        <a href="users.php" class="btn btn-light rounded-pill px-3 border"><i class="fa-solid fa-users me-1"></i> Manage Users</a>
      </div>
    </div>

     <!-- Low Stock Watchlist -->
    <div class="row mt-2 mb-5">
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
          <div class="card-header bg-white border-bottom border-light pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Low Stock Watchlist</h5>
            <span class="badge badge-chip badge-chip-danger"><?php echo $lowStockCount; ?> items</span>
          </div>
          <div class="card-body p-0">
            <?php if (empty($lowStockProducts)): ?>
              <div class="text-center py-5 text-muted">No low stock items right now.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                      <th class="ps-4 py-3 border-0">Product</th>
                      <th class="py-3 border-0">Current Stock</th>
                      <th class="py-3 border-0">Stock Limit</th>
                      <th class="pe-4 py-3 border-0">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lowStockProducts as $lp): ?>
                      <tr>
                        <td class="ps-4 py-3">
                          <div class="d-flex align-items-center gap-3">
                            <?php $lowImg = str_starts_with($lp['image'], 'http') ? $lp['image'] : '../' . $lp['image']; ?>
                            <img src="<?php echo htmlspecialchars($lowImg); ?>" class="rounded-3 object-fit-cover shadow-sm" width="42" height="42" alt="<?php echo htmlspecialchars($lp['name']); ?>">
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($lp['name']); ?></span>
                          </div>
                        </td>
                        <td class="py-3 fw-bold text-dark"><?php echo (int)$lp['stock']; ?></td>
                        <td class="py-3 text-muted"><?php echo (int)$lp['stock_limit']; ?></td>
                        <td class="pe-4 py-3">
                          <?php if ((int)$lp['stock'] === 0): ?>
                            <span class="badge badge-chip badge-chip-dark">Out of Stock</span>
                          <?php else: ?>
                            <span class="badge badge-chip badge-chip-danger">Low Stock</span>
                          <?php endif; ?>
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
    </div>

    <!-- Analytics Charts -->
    <div class="row g-4 mb-5">
      <!-- Revenue Trajectory -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
          <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-area text-success me-2"></i> Revenue Trajectory</h5>
            <p class="text-muted small">Daily revenue from delivered orders over the last 7 days.</p>
          </div>
          <div class="card-body px-4 pb-4">
            <div style="height: 300px; width: 100%;">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Status Distribution -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
          <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-pie text-primary me-2"></i> Order Matrix</h5>
            <p class="text-muted small">Distribution of lifetime order statuses.</p>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center px-4 pb-4">
            <div style="height: 250px; width: 100%; position: relative;">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
          <div class="card-header bg-white border-bottom border-light pt-4 pb-3 px-4">
            <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Recent Orders</h5>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light text-muted small text-uppercase">
                <tr>
                  <th class="ps-4 py-3 border-0">Order</th>
                  <th class="py-3 border-0">Customer</th>
                  <th class="py-3 border-0">Total</th>
                  <th class="pe-4 py-3 border-0">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentOrders as $ro): ?>
                  <tr>
                    <td class="ps-4 py-3 fw-bold">#<?php echo (int)$ro['id']; ?></td>
                    <td class="py-3 text-muted"><?php echo htmlspecialchars($ro['customer_name']); ?></td>
                    <td class="py-3 fw-semibold">$<?php echo number_format((float)$ro['total'], 2); ?></td>
                      <td class="pe-4 py-3"><span class="badge badge-chip <?php echo orderStatusBadgeClass((string)$ro['status']); ?>"><?php echo htmlspecialchars($ro['status']); ?></span></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($recentOrders)): ?>
                  <tr><td colspan="4" class="text-center py-4 text-muted">No orders yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
          <div class="card-header bg-white border-bottom border-light pt-4 pb-3 px-4">
            <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-star text-warning me-2"></i> Top Customers</h5>
          </div>
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              <?php foreach ($topCustomers as $tc): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                  <div>
                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($tc['name']); ?></div>
                    <small class="text-muted"><?php echo (int)$tc['order_count']; ?> orders</small>
                  </div>
                  <div class="fw-bold text-success">$<?php echo number_format((float)$tc['lifetime_value'], 2); ?></div>
                </li>
              <?php endforeach; ?>
              <?php if (empty($topCustomers)): ?>
                <li class="list-group-item text-center text-muted py-4">No customer data yet.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Products Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom border-light pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-crown text-warning me-2"></i> Top Selling Products</h5>
                    <a href="products.php" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">View Inventory <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Product Name</th>
                                <th class="py-3 border-0">Current Price</th>
                                <th class="py-3 border-0">Units Sold</th>
                                <th class="py-3 border-0">Gross Volume</th>
                                <th class="pe-4 py-3 border-0">Remaining Stock</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php if (empty($topProducts)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Not enough order data to calculate top products.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($topProducts as $idx => $p): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="position-relative">
                                        <?php $topImg = str_starts_with($p['image'], 'http') ? $p['image'] : '../' . $p['image']; ?>
                                        <img src="<?php echo htmlspecialchars($topImg); ?>" class="rounded-3 object-fit-cover shadow-sm" width="45" height="45" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                                <?php if($idx === 0): ?>
                                                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-warning p-1 border border-2 border-white"><i class="fa-solid fa-trophy" style="font-size: 0.6rem;"></i></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($p['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-dark fw-semibold">
                                      <?php if (!empty($p['discounted_price']) && $p['discounted_price'] > 0 && $p['discounted_price'] < $p['price']): ?>
                                        <span class="text-decoration-line-through text-secondary me-2">$<?php echo number_format($p['price'], 2); ?></span>
                                        <span class="text-success">$<?php echo number_format($p['effective_price'], 2); ?></span>
                                      <?php else: ?>
                                        $<?php echo number_format($p['price'], 2); ?>
                                      <?php endif; ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge badge-chip badge-chip-primary"><?php echo $p['total_sold']; ?> Kgs</span>
                                    </td>
                                    <td class="py-3 text-success fw-bold">
                                        $<?php echo number_format($p['effective_price'] * $p['total_sold'], 2); ?>
                                    </td>
                                    <td class="pe-4 py-3">
                                      <?php if ($p['stock'] <= $p['stock_limit']): ?>
                                        <span class="badge badge-chip badge-chip-danger"><?php echo $p['stock']; ?> Low</span>
                                        <?php else: ?>
                                            <span class="text-muted fw-semibold"><?php echo $p['stock']; ?> available</span>
                                        <?php endif; ?>
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
  <!-- Chart.js Engine -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
    // Initialize Chart configurations safely
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- 1. Lines / Trailing Revenue Curve ---
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        
        // Gradient styling
        let revGradient = revCtx.createLinearGradient(0, 0, 0, 300);
        revGradient.addColorStop(0, 'rgba(46, 204, 113, 0.5)'); // Vegora Green bright
        revGradient.addColorStop(1, 'rgba(46, 204, 113, 0.0)'); // Vegora Green fade

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: <?php echo $revenueLabelsJson; ?>,
                datasets: [{
                    label: 'Gross Daily Revenue ($)',
                    data: <?php echo $revenueDataJson; ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: revGradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2ecc71',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Smooth bezier curves!
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#2c3e50',
                        padding: 12,
                        titleFont: { size: 14, family: 'Inter' },
                        bodyFont: { size: 14, family: 'Inter', weight: 'bold' },
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: 'Inter', weight: '500' }, color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6', borderDash: [5, 5], drawBorder: false },
                        ticks: { 
                            font: { family: 'Inter', weight: '500' }, color: '#9ca3af',
                            callback: function(value) { return '$' + value; }
                        }
                    }
                }
            }
        });

        // --- 2. Doughnut / Order Distribution Matrix ---
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $statusLabelsJson; ?>,
                datasets: [{
                    data: <?php echo $statusDataJson; ?>,
                    backgroundColor: [
                        '#fef08a', // Placed: Yellow
                        '#fed7aa', // Packed: Orange
                        '#bfdbfe', // Shipped: Blue
                        '#bbf7d0', // Delivered: Green
                        '#fecaca'  // Cancelled: Red
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { family: 'Inter', size: 12, weight: '600' },
                            color: '#6b7280'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#2c3e50',
                        padding: 12,
                        bodyFont: { size: 14, family: 'Inter', weight: 'bold' }
                    }
                }
            }
        });

    });
  </script>
</body>
</html>
