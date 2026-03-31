<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Product.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: shop.php');
    exit;
}

$productModel = new Product($pdo);
$product = $productModel->findById($id);

if (!$product) {
    header('Location: shop.php');
    exit;
}

$mostSellingItems = $productModel->getMostSelling(4, $product['id']);
$ratingSummary = $productModel->getRatingSummary($product['id']);
$reviews = $productModel->getReviews($product['id'], 20);
$userReview = null;
if (isset($_SESSION['user_id'])) {
  $userReview = $productModel->getUserReview($product['id'], (int)$_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product['name']); ?> - Vegora</title>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    .product-img-box {
      background-color: #f0fdf4;
      border-radius: var(--vegi-radius);
      padding: 3rem;
      text-align: center;
      height: 100%;
      min-height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .product-img-box img {
      max-height: 400px;
      object-fit: contain;
      filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
    }
    .details-box {
      padding: 2rem 0;
    }
    .rating-stars {
      color: #f59e0b;
      letter-spacing: 2px;
      font-size: 1rem;
    }
    .star-rating-input {
      display: inline-flex;
      flex-direction: row-reverse;
      gap: 0.35rem;
    }
    .star-rating-input input {
      position: absolute;
      opacity: 0;
      pointer-events: none;
    }
    .star-rating-input label {
      cursor: pointer;
      color: #d1d5db;
      font-size: 1.6rem;
      line-height: 1;
      transition: color 0.2s ease;
    }
    .star-rating-input label i {
      pointer-events: none;
    }
    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label,
    .star-rating-input input:checked ~ label {
      color: #f59e0b;
    }
  </style>
</head>
<body class="bg-light">

  <?php require_once 'includes/navbar.php'; ?>

  <main class="container py-5 my-4">
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="shop.php" class="text-success text-decoration-none">Shop</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
      </ol>
    </nav>

    <div class="row g-5 align-items-center bg-white p-4 p-md-5 rounded-4 shadow-sm border border-light">
      
      <!-- Product Image -->
      <div class="col-lg-6">
        <div class="product-img-box">
          <?php $imgSrc = str_starts_with($product['image'], 'http') ? $product['image'] : $product['image']; ?>
          <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
        </div>
      </div>

      <!-- Product Details -->
      <div class="col-lg-6 details-box">
        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold mb-3"><?php echo htmlspecialchars($product['category']); ?></span>
        <h1 class="display-4 fw-bold text-dark mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>

        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="rating-stars">
            <?php
              $avgRatingRounded = (int)round($ratingSummary['average_rating']);
              for ($s = 1; $s <= 5; $s++):
                if ($s <= $avgRatingRounded):
            ?>
                <i class="fa-solid fa-star"></i>
            <?php else: ?>
                <i class="fa-regular fa-star text-secondary"></i>
            <?php
                endif;
              endfor;
            ?>
          </span>
          <span class="text-muted fw-semibold"><?php echo number_format($ratingSummary['average_rating'], 1); ?> (<?php echo (int)$ratingSummary['review_count']; ?> reviews)</span>
        </div>
        
        <div class="fs-2 fw-bold text-success mb-4">
          <?php if (!empty($product['discounted_price']) && $product['discounted_price'] > 0 && $product['discounted_price'] < $product['price']): ?>
            <span class="text-decoration-line-through text-secondary fs-4 me-2">$<?php echo number_format($product['price'], 2); ?></span>
            <span>$<?php echo number_format($product['discounted_price'], 2); ?></span>
          <?php else: ?>
            $<?php echo number_format($product['price'], 2); ?>
          <?php endif; ?>
          <span class="fs-5 text-muted fw-normal">/ kg</span>
        </div>
        
        <p class="text-muted fs-5 mb-5" style="line-height: 1.8;">
          <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available for this delicious, farm-fresh product! Enjoy the highest quality organically sourced vegetables right at your doorstep.')); ?>
        </p>

        <div class="d-flex align-items-center gap-3">
          <div class="qty-control px-2 py-1 bg-light rounded-pill border" style="width: 140px;">
            <button class="qty-btn bg-white shadow-sm rounded-circle d-flex align-items-center justify-content-center border-0 text-dark" style="width:35px; height:35px;" onclick="document.getElementById('buy_qty').stepDown()"><i class="fa-solid fa-minus fs-6"></i></button>
            <input type="number" id="buy_qty" class="qty-input bg-transparent border-0 text-center fw-bold fs-5" style="width: 50px;" value="1" min="1">
            <button class="qty-btn bg-white shadow-sm rounded-circle d-flex align-items-center justify-content-center border-0 text-dark" style="width:35px; height:35px;" onclick="document.getElementById('buy_qty').stepUp()"><i class="fa-solid fa-plus fs-6"></i></button>
          </div>
          
          <button class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm d-flex align-items-center gap-2 flex-grow-1 justify-content-center" onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('buy_qty').value)">
            <i class="fa-solid fa-cart-shopping"></i> Add to Cart
          </button>
        </div>
        
        <hr class="my-5 text-muted opacity-25">
        
        <div class="d-flex flex-column gap-2 text-muted fw-semibold">
          <div><i class="fa-solid fa-truck-fast text-success me-2" style="width: 20px;"></i> Free delivery on orders over $50</div>
          <div><i class="fa-solid fa-leaf text-success me-2" style="width: 20px;"></i> 100% Organic & Local</div>
          <div><i class="fa-solid fa-shield text-success me-2" style="width: 20px;"></i> Satisfaction Guarantee</div>
        </div>

      </div>

    </div>

    <section class="mt-5">
      <div class="row g-4">
        <div class="col-lg-5">
          <div class="bg-white rounded-4 shadow-sm border border-light p-4 h-100">
            <h4 class="fw-bold mb-3">Rate This Product</h4>

            <?php if (isset($_SESSION['review_success'])): ?>
              <div class="alert alert-success border-0 shadow-sm">
                <i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($_SESSION['review_success']); unset($_SESSION['review_success']); ?>
              </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['review_error'])): ?>
              <div class="alert alert-danger border-0 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($_SESSION['review_error']); unset($_SESSION['review_error']); ?>
              </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['user_id'])): ?>
              <p class="text-muted mb-3">Login to submit your rating and review.</p>
              <a href="login.php" class="btn btn-outline-primary rounded-pill px-4">Login to Review</a>
            <?php else: ?>
              <form action="controllers/reviewController.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

                <div class="mb-3">
                  <label class="form-label fw-bold d-block">Your Rating</label>
                  <div class="star-rating-input" role="radiogroup" aria-label="Select rating">
                    <?php $currentRating = (int)($userReview['rating'] ?? 0); ?>
                    <input type="radio" id="rating-5" name="rating" value="5" <?php echo $currentRating === 5 ? 'checked' : ''; ?> required>
                    <label for="rating-5" title="5 stars" aria-label="5 stars"><i class="fa-solid fa-star"></i></label>

                    <input type="radio" id="rating-4" name="rating" value="4" <?php echo $currentRating === 4 ? 'checked' : ''; ?>>
                    <label for="rating-4" title="4 stars" aria-label="4 stars"><i class="fa-solid fa-star"></i></label>

                    <input type="radio" id="rating-3" name="rating" value="3" <?php echo $currentRating === 3 ? 'checked' : ''; ?>>
                    <label for="rating-3" title="3 stars" aria-label="3 stars"><i class="fa-solid fa-star"></i></label>

                    <input type="radio" id="rating-2" name="rating" value="2" <?php echo $currentRating === 2 ? 'checked' : ''; ?>>
                    <label for="rating-2" title="2 stars" aria-label="2 stars"><i class="fa-solid fa-star"></i></label>

                    <input type="radio" id="rating-1" name="rating" value="1" <?php echo $currentRating === 1 ? 'checked' : ''; ?>>
                    <label for="rating-1" title="1 star" aria-label="1 star"><i class="fa-solid fa-star"></i></label>
                  </div>
                  <div class="form-text text-muted">Click a star to rate from 1 to 5.</div>
                </div>

                <div class="mb-3">
                  <label for="review_text" class="form-label fw-bold">Your Review</label>
                  <textarea id="review_text" name="review_text" rows="4" class="form-control" placeholder="Share your experience (optional)"><?php echo htmlspecialchars($userReview['review_text'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4"><?php echo $userReview ? 'Update Review' : 'Submit Review'; ?></button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="bg-white rounded-4 shadow-sm border border-light p-4 h-100">
            <h4 class="fw-bold mb-3">Customer Reviews</h4>

            <?php if (empty($reviews)): ?>
              <p class="text-muted mb-0">No reviews yet. Be the first to rate this product.</p>
            <?php else: ?>
              <div class="d-flex flex-column gap-3" style="max-height: 420px; overflow: auto;">
                <?php foreach ($reviews as $rev): ?>
                  <div class="border rounded-3 p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <div class="fw-bold text-dark"><?php echo htmlspecialchars($rev['user_name']); ?></div>
                      <small class="text-muted"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></small>
                    </div>
                    <div class="rating-stars mb-2">
                      <?php for ($s = 1; $s <= 5; $s++): ?>
                        <?php if ($s <= (int)$rev['rating']): ?>
                          <i class="fa-solid fa-star"></i>
                        <?php else: ?>
                          <i class="fa-regular fa-star text-secondary"></i>
                        <?php endif; ?>
                      <?php endfor; ?>
                    </div>
                    <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars((string)($rev['review_text'] ?? ''))); ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <?php if (!empty($mostSellingItems)): ?>
      <section class="mt-5 pt-2">
        <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
            <h3 class="mb-1">Most Selling Items</h3>
            <p class="text-muted mb-0">Customers are buying these the most right now.</p>
          </div>
          <a href="shop.php" class="text-success text-decoration-none fw-bold">View All <i class="fa-solid fa-arrow-right-long ms-1"></i></a>
        </div>

        <div class="row g-4">
          <?php foreach ($mostSellingItems as $item): ?>
            <div class="col-xl-3 col-md-6">
              <div class="card product-card h-100">
                <a href="product.php?id=<?php echo $item['id']; ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                  <div class="img-wrapper">
                    <?php $mostImgSrc = str_starts_with($item['image'], 'http') ? $item['image'] : $item['image']; ?>
                    <img src="<?php echo htmlspecialchars($mostImgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>">
                  </div>
                  <div class="card-body d-flex flex-column">
                    <span class="product-category"><?php echo htmlspecialchars($item['category']); ?></span>
                    <h3 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                </a>

                    <div class="d-flex justify-content-between align-items-end mt-auto pt-3">
                      <div class="product-price mb-0">
                        <?php if (!empty($item['discounted_price']) && $item['discounted_price'] > 0 && $item['discounted_price'] < $item['price']): ?>
                          <span class="text-decoration-line-through text-secondary me-2">$<?php echo number_format($item['price'], 2); ?></span>
                          <span class="text-success fw-bold">$<?php echo number_format($item['discounted_price'], 2); ?></span>
                        <?php else: ?>
                          $<?php echo number_format($item['price'], 2); ?>
                        <?php endif; ?>
                        <span class="small text-muted fw-normal">/ kg</span>
                      </div>
                      <button class="btn btn-primary rounded-circle p-0" style="width: 45px; height: 45px;" onclick="addToCart(<?php echo $item['id']; ?>)">
                        <i class="fa-solid fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>

  <?php require_once 'includes/footer.php'; ?>
  <script src="assets/js/cart.js"></script>
</body>
</html>
