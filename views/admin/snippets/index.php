<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-code"></i> Code Snippets (HFCM)</h1>
    <a href="<?php echo url('/admin/snippets/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Snippet
    </a>
</div>

<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($snippets)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-code"></i>
                <h3>No Snippets Yet</h3>
                <p>Add scripts like Google Analytics, Facebook Pixel, or custom CSS here.</p>
                <a href="<?php echo url('/admin/snippets/create'); ?>" class="btn btn-primary">Add Snippet</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($snippets as $s): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo url('/admin/snippets/edit/' . $s['id']); ?>" class="table-title">
                                        <?php echo he($s['title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?php echo he(ucfirst($s['location'])); ?></span>
                                </td>
                                <td>
                                    <?php if ($s['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo url('/admin/snippets/edit/' . $s['id']); ?>" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/snippets/delete/' . $s['id']); ?>" class="inline-form" onsubmit="return confirm('Delete this snippet?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
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
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
