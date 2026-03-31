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

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
  $subtotal += ($item['effective_price'] * $item['quantity']);
}

$discount_amount = 0;
if (isset($_SESSION['coupon'])) {
    if ($_SESSION['coupon']['type'] === 'percent') {
        $discount_amount = $subtotal * ($_SESSION['coupon']['value'] / 100);
    } else {
        $discount_amount = $_SESSION['coupon']['value'];
    }
    if ($discount_amount > $subtotal) $discount_amount = $subtotal;
}

$shipping = $subtotal > 0 ? 5.00 : 0.00;
$tax = ($subtotal - $discount_amount) * 0.10;
$total = ($subtotal - $discount_amount) + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Vegora</title>
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

  <main class="container py-5 my-5">
    <div class="row g-5 justify-content-center">
      
      <!-- Checkout Form -->
      <div class="col-lg-7">
        <?php if (isset($_SESSION['checkout_error'])): ?>
          <div class="alert alert-danger shadow-sm border-0 fw-semibold mb-4 rounded-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $_SESSION['checkout_error']; unset($_SESSION['checkout_error']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['checkout_success'])): ?>
          <div class="alert alert-success shadow-sm border-0 fw-semibold mb-4 rounded-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?php echo $_SESSION['checkout_success']; unset($_SESSION['checkout_success']); ?>
          </div>
        <?php endif; ?>

        <div class="bg-white p-5 rounded-4 shadow-sm border border-light">
          <h2 class="fw-bold mb-4">Secure Checkout</h2>
          
          <form id="checkoutForm" action="controllers/orderController.php?action=checkout" method="POST">
            
            <h5 class="fw-bold mb-3 mt-4 text-dark"><i class="fa-regular fa-map me-2 text-success"></i> Shipping Details</h5>
            <div id="addressStorageNotice" class="alert alert-info border-0 py-2 px-3 small d-none" role="alert"></div>
            <div class="row g-3">
              <div class="col-12">
                <label for="shipping_address" class="form-label text-muted fw-semibold">Delivery Address</label>
                <input type="text" id="shipping_address" name="shipping_address" class="form-control" placeholder="123 Vegora Street" autocomplete="street-address" required>
              </div>
              <div class="col-md-6">
                <label for="shipping_city" class="form-label text-muted fw-semibold">City</label>
                <input type="text" id="shipping_city" name="shipping_city" class="form-control" placeholder="New York" autocomplete="address-level2" required>
              </div>
              <div class="col-md-6">
                <label for="shipping_zip" class="form-label text-muted fw-semibold">Zip Code</label>
                <input type="text" id="shipping_zip" name="shipping_zip" class="form-control" placeholder="10001" autocomplete="postal-code" required>
              </div>
              <div class="col-md-6">
                <label for="shipping_phone" class="form-label text-muted fw-semibold">Phone Number</label>
                <input type="tel" id="shipping_phone" name="shipping_phone" class="form-control" placeholder="+1 555 123 4567" autocomplete="tel" required>
              </div>
              <div class="col-md-6">
                <label for="shipping_notes" class="form-label text-muted fw-semibold">Address Note (Optional)</label>
                <input type="text" id="shipping_notes" name="shipping_notes" class="form-control" placeholder="Apartment 12, near gate B">
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <button type="button" id="saveAddressBtn" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Address</button>
              <button type="button" id="clearAddressBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold"><i class="fa-solid fa-trash me-1"></i> Clear Saved Address</button>
              <span class="text-muted small align-self-center">Saved on this browser only. You can edit anytime.</span>
            </div>

            <h5 class="fw-bold mb-3 mt-5 text-dark"><i class="fa-regular fa-credit-card me-2 text-success"></i> Payment Method</h5>
            <div class="p-3 border rounded-3 bg-light mb-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment" id="cod" value="cod" checked>
                <label class="form-check-label fw-bold" for="cod">
                  Cash on Delivery (COD)
                </label>
              </div>
              <p class="text-muted small ms-4 mt-1 mb-0">Pay with cash when your vegetables are delivered.</p>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm fs-5 mt-4">Place Order - $<?php echo number_format($total, 2); ?></button>
          </form>
        </div>
      </div>

      <!-- Order Summary Sidebar -->
      <aside class="col-lg-5">
        
        <!-- Coupon Form -->
        <div class="card bg-white border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-ticket text-warning me-2"></i> Apply Coupon</h5>
          <?php if (isset($_SESSION['coupon'])): ?>
            <div class="d-flex justify-content-between align-items-center bg-success bg-opacity-10 p-3 rounded-3 border border-success">
              <div class="text-success fw-bold">
                <i class="fa-solid fa-certificate me-1"></i> <?php echo htmlspecialchars($_SESSION['coupon']['code']); ?> Applied!
              </div>
              <form action="controllers/orderController.php?action=remove_coupon" method="POST" class="m-0">
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">Remove</button>
              </form>
            </div>
          <?php else: ?>
            <form action="controllers/orderController.php?action=apply_coupon" method="POST" class="d-flex gap-2">
              <input type="text" name="coupon_code" class="form-control rounded-pill bg-light border-0 px-4" placeholder="Enter promo code" required>
              <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Apply</button>
            </form>
          <?php endif; ?>
        </div>

        <div class="cart-total-box shadow-sm border border-light bg-white p-4 rounded-4">
          <h4 class="filter-title border-0 pb-0 mb-4">Order Summary</h4>
          
          <div class="d-flex flex-column gap-3 mb-4">
            <?php foreach ($cartItems as $item): ?>
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                  <span class="badge bg-light text-dark border p-2"><?php echo $item['quantity']; ?>x</span>
                  <span class="fw-semibold text-dark"><?php echo htmlspecialchars($item['name']); ?></span>
                </div>
                <span class="text-muted fw-semibold text-end">
                  <?php if (!empty($item['discounted_price']) && $item['discounted_price'] > 0 && $item['discounted_price'] < $item['price']): ?>
                    <div class="small text-decoration-line-through text-secondary">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                  <?php endif; ?>
                  $<?php echo number_format($item['effective_price'] * $item['quantity'], 2); ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>

          <hr class="text-muted opacity-25">
          
          <div class="cart-total-row mt-4">
            <span>Subtotal</span>
            <span class="fw-bold text-dark">$<?php echo number_format($subtotal, 2); ?></span>
          </div>
          <?php if ($discount_amount > 0): ?>
          <div class="cart-total-row text-success">
            <span>Discount (<?php echo htmlspecialchars($_SESSION['coupon']['code']); ?>)</span>
            <span class="fw-bold">-$<?php echo number_format($discount_amount, 2); ?></span>
          </div>
          <?php endif; ?>
          <div class="cart-total-row">
            <span>Shipping</span>
            <span class="fw-bold text-dark">$<?php echo number_format($shipping, 2); ?></span>
          </div>
          <div class="cart-total-row border-bottom pb-3 mb-3">
            <span>Taxes</span>
            <span class="fw-bold text-dark">$<?php echo number_format($tax, 2); ?></span>
          </div>
          
          <div class="d-flex justify-content-between align-items-center fs-4 fw-bold text-dark">
            <span>Total</span>
            <span class="text-success">$<?php echo number_format($total, 2); ?></span>
          </div>
          
        </div>
      </aside>

    </div>
  </main>

  <script>
    (function () {
      const STORAGE_KEY = 'vegora.checkout.address.v1';

      const form = document.getElementById('checkoutForm');
      if (!form) return;

      const fields = {
        shipping_address: document.getElementById('shipping_address'),
        shipping_city: document.getElementById('shipping_city'),
        shipping_zip: document.getElementById('shipping_zip'),
        shipping_phone: document.getElementById('shipping_phone'),
        shipping_notes: document.getElementById('shipping_notes')
      };

      const notice = document.getElementById('addressStorageNotice');
      const saveBtn = document.getElementById('saveAddressBtn');
      const clearBtn = document.getElementById('clearAddressBtn');

      function showNotice(message, type) {
        if (!notice) return;
        notice.className = 'alert border-0 py-2 px-3 small';
        notice.classList.add(type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info'));
        notice.textContent = message;
      }

      function collectAddressData() {
        return {
          shipping_address: fields.shipping_address ? fields.shipping_address.value.trim() : '',
          shipping_city: fields.shipping_city ? fields.shipping_city.value.trim() : '',
          shipping_zip: fields.shipping_zip ? fields.shipping_zip.value.trim() : '',
          shipping_phone: fields.shipping_phone ? fields.shipping_phone.value.trim() : '',
          shipping_notes: fields.shipping_notes ? fields.shipping_notes.value.trim() : ''
        };
      }

      function applyAddressData(data) {
        if (!data) return;
        Object.keys(fields).forEach(function (key) {
          if (fields[key] && typeof data[key] === 'string') {
            fields[key].value = data[key];
          }
        });
      }

      function saveAddress() {
        const data = collectAddressData();
        if (!data.shipping_address || !data.shipping_city || !data.shipping_zip || !data.shipping_phone) {
          showNotice('Fill in address, city, zip, and phone before saving locally.', 'error');
          return false;
        }

        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        showNotice('Address saved in this browser. You can still edit it anytime.', 'success');
        return true;
      }

      function clearSavedAddress() {
        localStorage.removeItem(STORAGE_KEY);
        showNotice('Saved local address removed.', 'info');
      }

      const savedRaw = localStorage.getItem(STORAGE_KEY);
      if (savedRaw) {
        try {
          const savedData = JSON.parse(savedRaw);
          applyAddressData(savedData);
          showNotice('Loaded your saved local address. Edit if needed.', 'info');
        } catch (e) {
          localStorage.removeItem(STORAGE_KEY);
        }
      }

      if (saveBtn) {
        saveBtn.addEventListener('click', saveAddress);
      }

      if (clearBtn) {
        clearBtn.addEventListener('click', clearSavedAddress);
      }

      form.addEventListener('submit', function () {
        saveAddress();
      });
    })();
  </script>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
</body>
</html>
