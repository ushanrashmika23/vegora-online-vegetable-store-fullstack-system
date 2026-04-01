<?php
session_start();
require_once __DIR__ . '/controllers/productController.php';

// Fetch products from database
$products = getAllProducts();

$selectedCategory = trim((string)($_GET['category'] ?? ''));
$selectedSort = trim((string)($_GET['sort'] ?? 'default'));
$searchQuery = trim((string)($_GET['q'] ?? ''));
$selectedMaxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 50.0;
if ($selectedMaxPrice <= 0) {
  $selectedMaxPrice = 50.0;
}

$categoryCounts = [];
foreach ($products as $p) {
  $cat = trim((string)($p['category'] ?? 'Uncategorized'));
  if ($cat === '') {
    $cat = 'Uncategorized';
  }
  if (!isset($categoryCounts[$cat])) {
    $categoryCounts[$cat] = 0;
  }
  $categoryCounts[$cat]++;
}
ksort($categoryCounts);

$filteredProducts = array_values(array_filter($products, function ($p) use ($selectedCategory, $selectedMaxPrice, $searchQuery) {
  $effectivePrice = (!empty($p['discounted_price']) && $p['discounted_price'] > 0 && $p['discounted_price'] < $p['price'])
    ? (float)$p['discounted_price']
    : (float)$p['price'];

  $matchesCategory = $selectedCategory === '' || strcasecmp((string)($p['category'] ?? ''), $selectedCategory) === 0;
  $matchesPrice = $effectivePrice <= $selectedMaxPrice;
  $haystack = strtolower(trim((string)(($p['name'] ?? '') . ' ' . ($p['category'] ?? '') . ' ' . ($p['description'] ?? ''))));
  $needle = strtolower($searchQuery);
  $matchesQuery = $needle === '' || strpos($haystack, $needle) !== false;

  return $matchesCategory && $matchesPrice && $matchesQuery;
}));

switch ($selectedSort) {
  case 'price_asc':
    usort($filteredProducts, function ($a, $b) {
      $aPrice = (!empty($a['discounted_price']) && $a['discounted_price'] > 0 && $a['discounted_price'] < $a['price']) ? (float)$a['discounted_price'] : (float)$a['price'];
      $bPrice = (!empty($b['discounted_price']) && $b['discounted_price'] > 0 && $b['discounted_price'] < $b['price']) ? (float)$b['discounted_price'] : (float)$b['price'];
      return $aPrice <=> $bPrice;
    });
    break;
  case 'price_desc':
    usort($filteredProducts, function ($a, $b) {
      $aPrice = (!empty($a['discounted_price']) && $a['discounted_price'] > 0 && $a['discounted_price'] < $a['price']) ? (float)$a['discounted_price'] : (float)$a['price'];
      $bPrice = (!empty($b['discounted_price']) && $b['discounted_price'] > 0 && $b['discounted_price'] < $b['price']) ? (float)$b['discounted_price'] : (float)$b['price'];
      return $bPrice <=> $aPrice;
    });
    break;
  case 'latest':
    usort($filteredProducts, function ($a, $b) {
      return strtotime((string)($b['created_at'] ?? '')) <=> strtotime((string)($a['created_at'] ?? ''));
    });
    break;
  default:
    // Keep default DB order.
    break;
}

function buildShopUrl(array $overrides = [])
{
  $params = [
    'category' => $_GET['category'] ?? '',
    'max_price' => $_GET['max_price'] ?? 50,
    'sort' => $_GET['sort'] ?? 'default',
    'q' => $_GET['q'] ?? ''
  ];

  foreach ($overrides as $key => $value) {
    $params[$key] = $value;
  }

  $params = array_filter($params, function ($v) {
    return !($v === '' || $v === null);
  });

  $query = http_build_query($params);
  return 'shop.php' . ($query !== '' ? '?' . $query : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Vegora</title>
  
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
      <h1 class="display-4 fw-bold text-dark">Shop Fresh Veggies</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center mb-0 mt-3">
          <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Shop</li>
        </ol>
      </nav>
    </div>
  </div>

  <main class="container mb-5 pb-5">
    <div class="row g-5">
      
      <!-- Sidebar Filters -->
      <aside class="col-lg-3">
        <div class="filter-card">
          <h4 class="filter-title">Categories</h4>
          <div class="list-group list-group-flush mb-4">
            <a href="<?php echo htmlspecialchars(buildShopUrl(['category' => ''])); ?>" class="list-group-item list-group-item-action <?php echo $selectedCategory === '' ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
              All Fresh
              <span class="badge bg-success rounded-pill"><?php echo count($products); ?></span>
            </a>
            <?php foreach ($categoryCounts as $categoryName => $count): ?>
              <a href="<?php echo htmlspecialchars(buildShopUrl(['category' => $categoryName])); ?>" class="list-group-item list-group-item-action <?php echo strcasecmp($selectedCategory, $categoryName) === 0 ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
                <?php echo htmlspecialchars($categoryName); ?>
                <span class="badge bg-light text-dark rounded-pill"><?php echo (int)$count; ?></span>
              </a>
            <?php endforeach; ?>
          </div>

          <h4 class="filter-title mt-2">Filter by Price</h4>
          <form method="GET" action="shop.php" class="px-2">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($selectedSort); ?>">
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="range" class="form-range" min="1" max="200" step="1" id="priceRange" name="max_price" value="<?php echo (int)$selectedMaxPrice; ?>" style="accent-color: var(--vegi-green);">
            <div class="d-flex justify-content-between text-muted fw-bold mt-2 mb-2">
              <span>$0</span>
              <span id="priceValue">$<?php echo (int)$selectedMaxPrice; ?></span>
            </div>
            <button type="submit" class="btn btn-outline-primary w-100 mt-3 rounded-pill">Apply Filter</button>
          </form>
        </div>
      </aside>

      <!-- Main Product Grid -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
          <p class="mb-0 text-muted">
            Showing <strong><?php echo count($filteredProducts); ?></strong> of <?php echo count($products); ?> results
            <?php if ($searchQuery !== ''): ?>
              for "<?php echo htmlspecialchars($searchQuery); ?>"
            <?php endif; ?>
          </p>
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted fw-semibold flex-shrink-0">Sort by:</label>
            <form method="GET" action="shop.php" id="sortForm" class="m-0">
              <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
              <input type="hidden" name="max_price" value="<?php echo (int)$selectedMaxPrice; ?>">
              <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>">
              <select name="sort" class="form-select border-0 shadow-sm rounded-pill fw-semibold bg-white" style="cursor: pointer;" onchange="document.getElementById('sortForm').submit();">
                <option value="default" <?php echo $selectedSort === 'default' ? 'selected' : ''; ?>>Default Sorting</option>
                <option value="price_asc" <?php echo $selectedSort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo $selectedSort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="latest" <?php echo $selectedSort === 'latest' ? 'selected' : ''; ?>>Latest</option>
              </select>
            </form>
          </div>
        </div>

        <div class="row g-4">
          <?php if (empty($filteredProducts)): ?>
            <div class="col-12 py-5 text-center">
              <i class="fa-solid fa-basket-shopping text-muted fs-1 mb-3 opacity-50"></i>
              <h4 class="text-muted">No products match your selected filters.</h4>
              <a href="shop.php" class="btn btn-outline-primary rounded-pill mt-3 px-4">Clear Filters</a>
            </div>
          <?php else: ?>
            <?php foreach ($filteredProducts as $product): ?>
              <div class="col-xl-4 col-md-6">
                <div class="card product-card h-100">
                  <!-- Wrapped content inside anchor -->
                  <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                    <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-3 z-1 rounded-circle shadow-sm" style="width: 35px; height: 35px;"><i class="fa-regular fa-heart text-muted"></i></button>
                    
                    <div class="img-wrapper">
                      <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <div class="card-body">
                      <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                      <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                  </a>
                    
                    <div class="d-flex justify-content-between align-items-end mt-auto pt-3">
                      <div class="product-price mb-0">
                        <?php if (!empty($product['discounted_price']) && $product['discounted_price'] > 0 && $product['discounted_price'] < $product['price']): ?>
                          <span class="text-decoration-line-through text-secondary me-2">$<?php echo number_format($product['price'], 2); ?></span>
                          <span class="text-success fw-bold">$<?php echo number_format($product['discounted_price'], 2); ?></span>
                        <?php else: ?>
                          $<?php echo number_format($product['price'], 2); ?>
                        <?php endif; ?>
                        <span class="small text-muted fw-normal">/ kg</span>
                      </div>
                      <button class="btn btn-primary rounded-circle p-0" style="width: 45px; height: 45px;" onclick="addToCart(<?php echo $product['id']; ?>)">
                        <i class="fa-solid fa-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (count($filteredProducts) > 0): ?>
        <nav class="mt-5 d-flex justify-content-center">
          <ul class="pagination">
            <li class="page-item disabled">
              <span class="page-link border-0 rounded-circle me-2 text-muted fw-bold p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fa-solid fa-chevron-left"></i></span>
            </li>
            <li class="page-item active">
              <span class="page-link border-0 rounded-circle me-2 bg-success text-white fw-bold p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">1</span>
            </li>
            <li class="page-item">
              <a class="page-link border-0 rounded-circle me-2 text-dark bg-white fw-bold p-3 shadow-sm d-flex align-items-center justify-content-center" href="#" style="width: 50px; height: 50px;"><i class="fa-solid fa-chevron-right"></i></a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
      </div>

    </div>
  </main>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
  <script>
    (function () {
      const range = document.getElementById('priceRange');
      const value = document.getElementById('priceValue');
      if (!range || !value) return;
      range.addEventListener('input', function () {
        value.textContent = '$' + range.value;
      });
    })();
  </script>
  <script src="assets/js/cart.js"></script>
</body>
</html>
