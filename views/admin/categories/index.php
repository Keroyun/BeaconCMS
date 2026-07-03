<?php
$pageTitle = $typeInfo['label'] ?? 'Categories';
ob_start();
?>

<div class="content-header">
    <h1><i class="<?php echo he($typeInfo['icon'] ?? 'fa-solid fa-tags'); ?>"></i> <?php echo he($pageTitle); ?></h1>
    <a href="<?php echo url('/admin/categories/create?type=' . urlencode($taxonomyType)); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New <?php echo he($typeInfo['singular'] ?? 'Category'); ?>
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
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="<?php echo he($typeInfo['icon'] ?? 'fa-solid fa-tags'); ?>"></i>
                <h3>No Categories Yet</h3>
                <p>Create categories to organize your content.</p>
                <a href="<?php echo url('/admin/categories/create?type=' . urlencode($taxonomyType)); ?>" class="btn btn-primary">Add <?php echo he($typeInfo['singular'] ?? 'Category'); ?></a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Slug</th>
                            <th>Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center" style="gap:10px">
                                        <?php if (!empty($cat['color'])): ?>
                                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?php echo he($cat['color']); ?>"></span>
                                        <?php endif; ?>
                                        <a href="<?php echo url('/admin/categories/edit/' . $cat['id']); ?>" class="table-title">
                                            <?php echo he($cat['name']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td><span class="text-muted"><?php echo he(substr($cat['description'], 0, 50)); ?></span></td>
                                <td><code><?php echo he($cat['slug']); ?></code></td>
                                <td><span class="badge badge-info"><?php echo (int)($cat['item_count'] ?? 0); ?></span></td>
                                <td class="actions">
                                    <a href="<?php echo url('/admin/categories/edit/' . $cat['id']); ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/categories/delete/' . $cat['id']); ?>" class="inline-form" onsubmit="return confirm('Delete this category? Items will lose this category tag.');">
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
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
