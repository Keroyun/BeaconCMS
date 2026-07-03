<?php
$pageTitle = 'Pages';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-file"></i> Pages</h1>
    <a href="<?php echo url('/admin/pages/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Page
    </a>
</div>

<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>
<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($pages)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-file"></i>
                <h3>No Pages Yet</h3>
                <p>Create your first page to get started.</p>
                <a href="<?php echo url('/admin/pages/create'); ?>" class="btn btn-primary">Create Page</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Template</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo url('/admin/pages/edit/' . $page['id']); ?>" class="table-title">
                                        <?php echo he($page['title']); ?>
                                    </a>
                                </td>
                                <td><span class="badge badge-info"><?php echo he(ucfirst($page['template'] ?? 'default')); ?></span></td>
                                <td><?php echo (int)($page['sort_order'] ?? 0); ?></td>
                                <td>
                                    <span class="badge <?php echo ($page['status'] ?? 'draft') === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo he(ucfirst($page['status'] ?? 'draft')); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($page['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="<?php echo url('/admin/pages/edit/' . $page['id']); ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/pages/delete/' . $page['id']); ?>" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this page?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>
