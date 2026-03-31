<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Cart.php';

// Check login status
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$cartModel = new Cart($pdo);
$cartItems = $cartModel->getItems($_SESSION['user_id']);

$subtotal = 0;
foreach ($cartItems as $item) {
  $subtotal += ($item['effective_price'] * $item['quantity']);
}
$shipping = $subtotal > 0 ? 5.00 : 0.00;
$tax = $subtotal * 0.10;
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart - Vegora</title>
  
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

  <!-- Page Header -->
  <div class="bg-white py-5 mb-5 border-bottom">
    <div class="container text-center">
      <h1 class="display-4 fw-bold text-dark">Your Cart</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center mb-0 mt-3">
          <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="shop.php" class="text-success text-decoration-none">Shop</a></li>
          <li class="breadcrumb-item active" aria-current="page">Cart</li>
        </ol>
      </nav>
    </div>
  </div>

  <main class="container mb-5 pb-5">
    <div class="row g-5">
      
      <!-- Cart Items -->
      <div class="col-lg-8">
        <div class="cart-card bg-white p-4">
          <h4 class="filter-title mb-4 border-0">Shopping Cart</h4>
          
          <?php if (empty($cartItems)): ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-cart-shopping fs-1 text-muted opacity-25 mb-3"></i>
                <h4 class="text-muted">Your cart is empty.</h4>
                <a href="shop.php" class="btn btn-primary mt-3 rounded-pill px-4">Start Shopping</a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table cart-table align-middle">
                <thead>
                  <tr>
                    <th scope="col" style="width: 50%;">Product Details</th>
                    <th scope="col" class="text-center">Quantity</th>
                    <th scope="col" class="text-center">Price</th>
                    <th scope="col" class="text-end">Total</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cartItems as $item): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-4">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img border">
                        <div>
                          <h6 class="mb-1 fw-bold text-dark fs-5"><?php echo htmlspecialchars($item['name']); ?></h6>
                          <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-bold"><?php echo htmlspecialchars($item['category']); ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="qty-control mx-auto">
                        <button class="qty-btn" type="button" onclick="updateCartQuantity(<?php echo $item['cart_id']; ?>, this.nextElementSibling, -1)"><i class="fa-solid fa-minus fs-6"></i></button>
                        <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                        <button class="qty-btn" type="button" onclick="updateCartQuantity(<?php echo $item['cart_id']; ?>, this.previousElementSibling, 1)"><i class="fa-solid fa-plus fs-6"></i></button>
                      </div>
                    </td>
                    <td class="text-center fw-semibold text-muted item-price" data-effective-price="<?php echo htmlspecialchars($item['effective_price']); ?>">
                      <?php if (!empty($item['discounted_price']) && $item['discounted_price'] > 0 && $item['discounted_price'] < $item['price']): ?>
                        <div class="small text-decoration-line-through text-secondary">$<?php echo number_format($item['price'], 2); ?></div>
                      <?php endif; ?>
                      $<?php echo number_format($item['effective_price'], 2); ?>
                    </td>
                    <td class="text-end fw-bold text-dark fs-5 item-total">$<?php echo number_format($item['effective_price'] * $item['quantity'], 2); ?></td>
                    <td class="text-end">
                      <button class="btn btn-light rounded-circle text-danger shadow-sm" style="width: 40px; height: 40px;" onclick="removeCartItem(<?php echo $item['cart_id']; ?>, this)"><i class="fa-solid fa-trash-can"></i></button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-5">
              <a href="shop.php" class="btn btn-outline-primary rounded-pill"><i class="fa-solid fa-arrow-left-long ms-1 me-2"></i> Continue Shopping</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Order Summary -->
      <aside class="col-lg-4">
        <div class="cart-total-box shadow-sm border border-light bg-white">
          <h4 class="filter-title border-0 pb-0">Order Summary</h4>
          <hr class="text-muted opacity-25">
          
          <div class="cart-total-row mt-4">
            <span>Subtotal</span>
            <span class="fw-bold text-dark" id="cart-subtotal">$<?php echo number_format($subtotal, 2); ?></span>
          </div>
          <div class="cart-total-row">
            <span>Shipping</span>
            <span class="fw-bold text-dark">$<?php echo number_format($shipping, 2); ?></span>
          </div>
          <div class="cart-total-row">
            <span>Taxes</span>
            <span class="fw-bold text-dark">$<?php echo number_format($tax, 2); ?></span>
          </div>
          
          <div class="cart-total-row final">
            <span>Total</span>
            <span class="text-success" id="cart-final-total">$<?php echo number_format($total, 2); ?></span>
          </div>
          
          <a href="checkout.php" class="btn btn-primary btn-lg w-100 mt-4 rounded-pill shadow-sm fs-5 <?php echo empty($cartItems) ? 'disabled' : ''; ?>">Proceed to Checkout <i class="fa-solid fa-arrow-right-long ms-2"></i></a>
          
          <div class="mt-4 text-center">
            <p class="text-muted small mb-2"><i class="fa-solid fa-lock me-1"></i> Secure Checkout</p>
            <div class="d-flex justify-content-center gap-2 text-muted fs-3">
              <i class="fa-brands fa-cc-visa"></i>
              <i class="fa-brands fa-cc-mastercard"></i>
              <i class="fa-brands fa-cc-paypal"></i>
              <i class="fa-brands fa-cc-apple-pay"></i>
            </div>
          </div>
        </div>
      </aside>

    </div>
  </main>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
</body>
</html>
