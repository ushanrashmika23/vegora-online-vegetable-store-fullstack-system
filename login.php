<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Vegora</title>
  
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

  <!-- Login Form -->
  <div class="container auth-container justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="auth-card">
        <div class="text-center mb-4">
          <div class="bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 70px; height: 70px;">
            <i class="fa-solid fa-user-lock fs-2"></i>
          </div>
          <h2>Welcome Back</h2>
          <p class="text-muted">Sign in to your Vegora account to continue</p>
        </div>
        
        <?php if (isset($_SESSION['auth_error'])): ?>
          <div class="alert alert-danger shadow-sm border-0 fw-semibold text-center mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['auth_success'])): ?>
          <div class="alert alert-success shadow-sm border-0 fw-semibold text-center mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?>
          </div>
        <?php endif; ?>
        
        <form action="controllers/authController.php?action=login" method="POST">
          <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0 border-2 border-light text-muted"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" name="email" class="form-control border-start-0" id="email" placeholder="name@example.com" required>
            </div>
          </div>
          <div class="mb-4">
            <div class="d-flex justify-content-between">
              <label for="password" class="form-label">Password</label>
              <a href="#" class="text-success text-decoration-none small fw-semibold">Forgot Password?</a>
            </div>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0 border-2 border-light text-muted"><i class="fa-solid fa-lock"></i></span>
              <input type="password" name="password" class="form-control border-start-0" id="password" placeholder="••••••••" required>
            </div>
          </div>
          <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input border-2 border-success border-opacity-50" id="remember">
            <label class="form-check-label text-muted fw-semibold" for="remember">Remember me for 30 days</label>
          </div>
          
          <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-4 shadow-sm">Sign In <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i></button>
          
          <div class="text-center">
            <p class="text-muted mb-0">Don't have an account? <a href="register.php" class="text-success fw-bold text-decoration-none">Sign Up</a></p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
</body>
</html>
