<?php
$pageTitle = 'Specialties';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-hospital"></i> Specialties</h1>
    <a href="<?php echo url('/admin/specialties/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Specialty
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
        <?php if (empty($specialties)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-hospital"></i>
                <h3>No Specialties Yet</h3>
                <p>Add medical specialties for your hospital.</p>
                <a href="<?php echo url('/admin/specialties/create'); ?>" class="btn btn-primary">Add Specialty</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Name</th>
                            <th>Consultants</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($specialties as $specialty): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($specialty['icon'])): ?>
                                        <i class="<?php echo he($specialty['icon']); ?> fa-lg"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-stethoscope fa-lg text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo url('/admin/specialties/edit/' . $specialty['id']); ?>" class="table-title">
                                        <?php echo he($specialty['name']); ?>
                                    </a>
                                    <?php if (!empty($specialty['description'])): ?>
                                        <br><small class="text-muted"><?php echo he(substr($specialty['description'], 0, 80)); ?><?php echo strlen($specialty['description']) > 80 ? '...' : ''; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo (int)($specialty['consultant_count'] ?? 0); ?> doctors</span>
                                </td>
                                <td><?php echo (int)($specialty['sort_order'] ?? 0); ?></td>
                                <td>
                                    <span class="badge <?php echo $specialty['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo he(ucfirst($specialty['status'])); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo url('/admin/specialties/edit/' . $specialty['id']); ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/specialties/delete/' . $specialty['id']); ?>" class="inline-form" onsubmit="return confirm('<?php echo ($specialty['consultant_count'] ?? 0) > 0 ? 'This specialty has ' . ($specialty['consultant_count'] ?? 0) . ' consultant(s). ' : ''; ?>Delete this specialty?');">
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
