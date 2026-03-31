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
  <title>Create Account - Vegora</title>
  
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
            <i class="fa-solid fa-user-plus fs-2"></i>
          </div>
          <h2>Create Account</h2>
          <p class="text-muted">Join Vegora to get fresh veggies delivered!</p>
        </div>
        
        <?php if (isset($_SESSION['auth_error'])): ?>
          <div class="alert alert-danger shadow-sm border-0 fw-semibold text-center mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?>
          </div>
        <?php endif; ?>

        <form action="controllers/authController.php?action=register" method="POST">
          <div class="mb-4">
            <label for="name" class="form-label">Full Name</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0 border-2 border-light text-muted"><i class="fa-regular fa-user"></i></span>
              <input type="text" name="name" class="form-control border-start-0" id="name" placeholder="John Doe" required>
            </div>
          </div>
          <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0 border-2 border-light text-muted"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" name="email" class="form-control border-start-0" id="email" placeholder="name@example.com" required>
            </div>
          </div>
          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0 border-2 border-light text-muted"><i class="fa-solid fa-lock"></i></span>
              <input type="password" name="password" class="form-control border-start-0" id="password" placeholder="Create a password" required>
            </div>
          </div>
          <div class="mb-4">
            <label for="confirm" class="form-label">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0 border-2 border-light text-muted"><i class="fa-solid fa-check"></i></span>
              <input type="password" name="confirm" class="form-control border-start-0" id="confirm" placeholder="Confirm your password" required>
            </div>
          </div>
          <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input border-2 border-success border-opacity-50" id="terms" required>
            <label class="form-check-label text-muted fw-semibold" for="terms">I agree to the <a href="#" class="text-success text-decoration-none">Terms & Conditions</a></label>
          </div>
          
          <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-4 shadow-sm">Sign Up <i class="fa-solid fa-arrow-right ms-2"></i></button>
          
          <div class="text-center">
            <p class="text-muted mb-0">Already have an account? <a href="login.php" class="text-success fw-bold text-decoration-none">Sign In</a></p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Injected Footer -->
  <?php require_once 'includes/footer.php'; ?>
</html>
