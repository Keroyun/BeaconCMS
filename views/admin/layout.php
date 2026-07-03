<?php
/**
 * BeaconCMS Admin Layout
 * Receives: $pageTitle, $content
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo htmlspecialchars(Auth::generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?> — BeaconCMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="<?php echo View::asset('/css/admin.css'); ?>">
  <link rel="stylesheet" href="<?php echo View::asset('/css/editor.css'); ?>">
</head>
<body>
  <!-- Mobile Overlay -->
  <div class="mobile-overlay" id="mobileOverlay"></div>

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">B</div>
      <span class="brand-text">BeaconCMS</span>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-title">Main</div>
      <a href="<?php echo View::url('/admin'); ?>" class="<?php echo ($currentPath === '/admin') ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
        <span class="nav-label">Dashboard</span>
      </a>
      <a href="<?php echo View::url('/admin/posts'); ?>" class="<?php echo (strpos($currentPath, '/admin/posts') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-lines"></i>
        <span class="nav-label">Posts</span>
      </a>
      <a href="<?php echo View::url('/admin/pages'); ?>" class="<?php echo (strpos($currentPath, '/admin/pages') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-file"></i>
        <span class="nav-label">Pages</span>
      </a>
      <a href="<?php echo View::url('/admin/media'); ?>" class="<?php echo (strpos($currentPath, '/admin/media') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-images"></i>
        <span class="nav-label">Media</span>
      </a>

      <div class="nav-section-title">Taxonomy & Content</div>
      <a href="<?php echo View::url('/admin/forms'); ?>" class="<?php echo (strpos($currentPath, '/admin/forms') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-wpforms"></i>
        <span class="nav-label">Forms</span>
      </a>
      <a href="<?php echo View::url('/admin/consultants'); ?>" class="<?php echo (strpos($currentPath, '/admin/consultants') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-user-doctor"></i>
        <span class="nav-label">Consultants</span>
      </a>
      <a href="<?php echo View::url('/admin/promotions'); ?>" class="<?php echo (strpos($currentPath, '/admin/promotions') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-bullhorn"></i>
        <span class="nav-label">Promotions</span>
      </a>
      <a href="<?php echo View::url('/admin/specialties'); ?>" class="<?php echo (strpos($currentPath, '/admin/specialties') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-hospital"></i>
        <span class="nav-label">Specialties</span>
      </a>
      
      <!-- Category Links -->
      <a href="<?php echo View::url('/admin/categories?type=post_category'); ?>" class="<?php echo ($currentPath === '/admin/categories' && ($_GET['type'] ?? '') === 'post_category') ? 'active' : ''; ?>">
        <i class="fa-solid fa-folder"></i>
        <span class="nav-label">Post Categories</span>
      </a>
      <a href="<?php echo View::url('/admin/categories?type=doctor_category'); ?>" class="<?php echo ($currentPath === '/admin/categories' && ($_GET['type'] ?? '') === 'doctor_category') ? 'active' : ''; ?>">
        <i class="fa-solid fa-user-tag"></i>
        <span class="nav-label">Doctor Types</span>
      </a>
      <a href="<?php echo View::url('/admin/categories?type=language_spoken'); ?>" class="<?php echo ($currentPath === '/admin/categories' && ($_GET['type'] ?? '') === 'language_spoken') ? 'active' : ''; ?>">
        <i class="fa-solid fa-language"></i>
        <span class="nav-label">Spoken Langs</span>
      </a>

      <div class="nav-section-title">System</div>
      <a href="<?php echo View::url('/admin/users'); ?>" class="<?php echo (strpos($currentPath, '/admin/users') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span class="nav-label">Users</span>
      </a>
      <a href="<?php echo View::url('/admin/snippets'); ?>" class="<?php echo (strpos($currentPath, '/admin/snippets') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-code"></i>
        <span class="nav-label">Code Snippets</span>
      </a>
      <a href="<?php echo View::url('/admin/settings'); ?>" class="<?php echo (strpos($currentPath, '/admin/settings') === 0) ? 'active' : ''; ?>">
        <i class="fa-solid fa-gear"></i>
        <span class="nav-label">Settings</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <button class="collapse-btn" id="sidebarCollapseBtn">
        <i class="fa-solid fa-chevron-left"></i>
        <span class="btn-label">Collapse</span>
      </button>
    </div>
  </aside>

  <!-- Topbar -->
  <header class="admin-topbar">
    <div class="d-flex align-center gap-2">
      <button class="topbar-hamburger" id="hamburgerBtn">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="topbar-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search anything..." id="globalSearch">
      </div>
    </div>

    <div class="topbar-user" id="userDropdown">
      <button class="topbar-user-btn" id="userDropdownBtn">
        <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['username'] ?? 'Admin') . '&background=6366f1&color=fff', ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar">
        <span class="user-name"><?php echo htmlspecialchars($user['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></span>
        <i class="fa-solid fa-chevron-down user-chevron"></i>
      </button>
      <div class="topbar-user-dropdown">
        <a href="<?php echo View::url('/admin/users/profile'); ?>">
          <i class="fa-solid fa-user"></i> My Profile
        </a>
        <a href="<?php echo View::url('/admin/settings'); ?>">
          <i class="fa-solid fa-gear"></i> Settings
        </a>
        <a href="<?php echo View::url('/'); ?>" target="_blank">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> View Site
        </a>
        <a href="<?php echo View::url('/admin/logout'); ?>" class="danger-link">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="admin-main">
    <?php echo $content; ?>
  </main>

  <!-- Flash Messages -->
  <div class="flash-container" id="flashContainer">
    <?php if ($successMsg = View::flash('success')): ?>
      <div class="flash-message success">
        <i class="fa-solid fa-circle-check"></i>
        <span><?php echo htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    <?php endif; ?>
    <?php if ($errorMsg = View::flash('error')): ?>
      <div class="flash-message error">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    <?php endif; ?>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <h3 class="modal-title">Confirm Delete</h3>
      <div class="modal-body">
        Are you sure you want to delete <strong id="deleteItemName">this item</strong>? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
        <form id="deleteForm" method="POST" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Auth::generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
            <i class="fa-solid fa-trash"></i> Delete
          </button>
        </form>
      </div>
    </div>
  </div>

  <script src="<?php echo View::asset('/js/admin.js'); ?>"></script>
  <script src="<?php echo View::asset('/js/editor.js'); ?>"></script>
</body>
</html>
