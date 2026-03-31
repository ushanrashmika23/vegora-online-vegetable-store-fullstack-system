<?php
// admin/includes/header.php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $pageTitle ?? 'Admin Dashboard - Vegora'; ?></title>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
  
  <style>
    :root {
      --vegi-green: #2ecc71;
      --sidebar-width: 250px;
    }
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      width: var(--sidebar-width);
      background-color: #fff;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
      z-index: 1000;
    }
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 30px;
    }
    .nav-link {
      color: #6c757d;
      font-weight: 500;
      padding: 12px 20px;
      margin-bottom: 5px;
      border-radius: 8px;
      transition: all 0.2s ease-in-out;
    }
    .nav-link:hover, .nav-link.active {
      background-color: rgba(46, 204, 113, 0.1);
      color: var(--vegi-green);
    }
    
    /* Reusable Dashboard Utils */
    .card-stat { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .prod-thumb { width: 50px; height: 50px; object-fit: cover; background: #f0fdf4; border-radius: 8px; padding: 2px; }
    .prod-thumb-sm { width: 40px; height: 40px; object-fit: cover; background: #f0fdf4; border-radius: 8px; padding: 2px; }
    
    /* Status pills */
    .custom-status-bg-Placed { background-color: #fef08a; color: #854d0e; }
    .custom-status-bg-Packed { background-color: #fed7aa; color: #9a3412; }
    .custom-status-bg-Shipped { background-color: #bfdbfe; color: #1e40af; }
    .custom-status-bg-Delivered { background-color: #bbf7d0; color: #166534; }
    .custom-status-bg-Cancelled { background-color: #fecaca; color: #991b1b; }
  </style>
</head>
<body>
