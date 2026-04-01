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

    /* Keep form labels and hints readable on all admin cards */
    .main-content .form-label,
    .main-content label {
      color: #1f2937 !important;
      font-weight: 700;
    }
    .main-content .form-label.text-muted,
    .main-content label.text-muted {
      color: #374151 !important;
      opacity: 1 !important;
    }
    .main-content .form-text {
      color: #4b5563 !important;
    }

    /* Unified admin badge style */
    .badge-chip {
      border-radius: 999px;
      padding: 0.45rem 0.85rem;
      font-weight: 700;
      border: 0;
      line-height: 1;
    }
    .badge-chip-sm {
      border-radius: 999px;
      padding: 0.25rem 0.6rem;
      font-size: 0.75rem;
      font-weight: 700;
      border: 0;
      line-height: 1;
    }
    .badge-chip-neutral { background: #f8fafc; color: #1f2937; }
    .badge-chip-primary { background: rgba(59,130,246,0.12); color: #1d4ed8; }
    .badge-chip-success { background: rgba(16,185,129,0.12); color: #047857; }
    .badge-chip-info { background: rgba(6,182,212,0.12); color: #0e7490; }
    .badge-chip-warning { background: rgba(245,158,11,0.12); color: #b45309; }
    .badge-chip-danger { background: rgba(239,68,68,0.12); color: #b91c1c; }
    .badge-chip-dark { background: #111827; color: #f9fafb; }

    /* Remove legacy borders from any badge markup still using border utility classes */
    .main-content .badge {
      border: 0 !important;
    }

    /* Consistent filter toolbar layout across admin pages */
    .admin-filter-grid .form-label {
      margin-bottom: 0.45rem;
    }
    .admin-filter-actions {
      display: flex;
      align-items: end;
      gap: 0.5rem;
      height: 100%;
    }
    .admin-filter-control {
      min-height: 44px;
      height: 44px;
      padding-top: 0.55rem;
      padding-bottom: 0.55rem;
      font-size: 0.95rem;
    }
    .admin-filter-grid .input-group .input-group-text.admin-filter-control {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 44px;
      padding-left: 0.75rem;
      padding-right: 0.75rem;
    }
    .admin-filter-btn {
      min-height: 44px;
      height: 44px;
      min-width: 120px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      white-space: nowrap;
      font-size: 0.95rem;
      font-weight: 600;
    }
    @media (max-width: 991.98px) {
      .admin-filter-actions .admin-filter-btn {
        flex: 1 1 auto;
        min-width: 0;
      }
    }
  </style>
</head>
<body>
