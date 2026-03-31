<?php
// admin/includes/topbar.php
?>
<!-- Top Header -->
<header class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
  <div class="d-flex align-items-center gap-3">
    <?php if(isset($showBackButton)): ?>
       <a href="<?php echo htmlspecialchars($backLink); ?>" class="btn btn-light rounded-circle shadow-sm">
         <i class="fa-solid fa-arrow-left"></i>
       </a>
    <?php endif; ?>
    
    <h2 class="fw-bold text-dark mb-0"><?php echo $pageHeader ?? 'Dashboard'; ?></h2>
    
    <?php if(isset($headerActionHtml)): ?>
       <?php echo $headerActionHtml; ?>
    <?php endif; ?>
  </div>
  
  <?php if(!isset($hideProfile)): ?>
  <div class="d-flex align-items-center gap-3">
    <span class="text-muted fw-semibold d-none d-sm-inline">Hello, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px;">
      <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
    </div>
  </div>
  <?php endif; ?>
</header>
