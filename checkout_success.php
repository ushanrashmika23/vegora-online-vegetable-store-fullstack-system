<?php
session_start();

if (!isset($_SESSION['checkout_success'])) {
    header('Location: index.php');
    exit;
}

$message = $_SESSION['checkout_success'];
$orderId = $_GET['order_id'] ?? 'UNKNOWN';

// Clear success message so page can't be refreshed continuously
unset($_SESSION['checkout_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Successful - Vegora</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

  <!-- Injected Navbar -->
  <?php require_once 'includes/navbar.php'; ?>

  <main class="container py-5 my-5 text-center">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="bg-white p-5 rounded-4 shadow-sm border border-light">
          
          <div class="bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 100px; height: 100px;">
            <i class="fa-solid fa-check fs-1"></i>
          </div>
          
          <h1 class="fw-bold text-dark mb-3">Order Completed!</h1>
          <p class="text-muted fs-5 mb-4"><?php echo htmlspecialchars($message); ?></p>
          
          <div class="bg-light p-4 rounded-3 mb-5 d-inline-block">
            <span class="text-muted d-block mb-1 text-uppercase fw-bold" style="letter-spacing: 1px;">Order Reference #</span>
            <span class="fs-4 fw-bold text-dark"><?php echo htmlspecialchars($orderId); ?></span>
          </div>

          <div>
             <a href="shop.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">Continue Shopping <i class="fa-solid fa-arrow-right ms-2"></i></a>
          </div>

        </div>
      </div>
    </div>
  </main>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
</body>
</html>
