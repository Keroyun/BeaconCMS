<?php
/**
 * BeaconCMS Admin Dashboard
 * Receives: $stats (array with post_count, page_count, consultant_count, promotion_count, media_count)
 *           $recentPosts (array of post arrays)
 */
$pageTitle = 'Dashboard';
$stats = $stats ?? [];
$recentPosts = $recentPosts ?? [];
ob_start();
?>

<div class="page-header">
  <h1><i class="fa-solid fa-house" style="margin-right:10px; color:var(--accent);"></i>Dashboard</h1>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card gradient-indigo" style="animation-delay:0.05s;">
    <div class="stat-icon">
      <i class="fa-solid fa-file-lines"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?php echo htmlspecialchars($stats['post_count'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="stat-label">Total Posts</div>
    </div>
  </div>
  <div class="stat-card gradient-emerald" style="animation-delay:0.1s;">
    <div class="stat-icon">
      <i class="fa-solid fa-file"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?php echo htmlspecialchars($stats['page_count'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="stat-label">Total Pages</div>
    </div>
  </div>
  <div class="stat-card gradient-amber" style="animation-delay:0.15s;">
    <div class="stat-icon">
      <i class="fa-solid fa-user-doctor"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?php echo htmlspecialchars($stats['consultant_count'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="stat-label">Consultants</div>
    </div>
  </div>
  <div class="stat-card gradient-rose" style="animation-delay:0.2s;">
    <div class="stat-icon">
      <i class="fa-solid fa-bullhorn"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?php echo htmlspecialchars($stats['promotion_count'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="stat-label">Promotions</div>
    </div>
  </div>
  <div class="stat-card gradient-sky" style="animation-delay:0.25s;">
    <div class="stat-icon">
      <i class="fa-solid fa-images"></i>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?php echo htmlspecialchars($stats['media_count'] ?? '0', ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="stat-label">Media Files</div>
    </div>
  </div>
</div>

<!-- Dashboard Grid: Recent Posts + Quick Actions -->
<div class="dashboard-grid">
  <!-- Recent Posts -->
  <div class="card" style="animation-delay:0.15s;">
    <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left" style="margin-right:8px; color:var(--accent);"></i>Recent Posts</h3>
    <?php if (!empty($recentPosts)): ?>
      <div class="table-wrapper" style="border:none;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentPosts as $post): ?>
              <tr>
                <td>
                  <a href="<?php echo View::url('/admin/posts/edit/' . ($post['id'] ?? '')); ?>">
                    <?php echo htmlspecialchars($post['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                </td>
                <td>
                  <?php
                    $status = $post['status'] ?? 'draft';
                    $badgeClass = 'badge-draft';
                    if ($status === 'published') $badgeClass = 'badge-published';
                    elseif ($status === 'archived') $badgeClass = 'badge-archived';
                  ?>
                  <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?></span>
                </td>
                <td class="text-muted"><?php echo htmlspecialchars($post['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                  <div class="table-actions">
                    <a href="<?php echo View::url('/admin/posts/edit/' . ($post['id'] ?? '')); ?>" class="btn btn-sm btn-secondary" title="Edit">
                      <i class="fa-solid fa-pen"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state" style="padding:30px 20px;">
        <div class="empty-icon"><i class="fa-solid fa-file-lines"></i></div>
        <h3>No posts yet</h3>
        <p>Create your first post to get started.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick Actions -->
  <div class="card" style="animation-delay:0.25s;">
    <h3 class="card-title"><i class="fa-solid fa-bolt" style="margin-right:8px; color:var(--warning);"></i>Quick Actions</h3>
    <div class="quick-actions" style="flex-direction:column;">
      <a href="<?php echo View::url('/admin/posts/create'); ?>" class="quick-action-btn">
        <i class="fa-solid fa-plus"></i>
        <span>New Post</span>
      </a>
      <a href="<?php echo View::url('/admin/pages/create'); ?>" class="quick-action-btn">
        <i class="fa-solid fa-file-circle-plus"></i>
        <span>New Page</span>
      </a>
      <a href="<?php echo View::url('/admin/consultants/create'); ?>" class="quick-action-btn">
        <i class="fa-solid fa-user-plus"></i>
        <span>New Consultant</span>
      </a>
      <a href="<?php echo View::url('/admin/media'); ?>" class="quick-action-btn">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <span>Upload Media</span>
      </a>
      <a href="<?php echo View::url('/admin/promotions/create'); ?>" class="quick-action-btn">
        <i class="fa-solid fa-bullhorn"></i>
        <span>New Promotion</span>
      </a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
