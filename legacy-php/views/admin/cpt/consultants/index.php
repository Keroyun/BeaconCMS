<?php
$pageTitle = 'Consultants';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-user-doctor"></i> Consultants</h1>
    <a href="<?php echo url('/admin/consultants/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Consultant
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
        <?php if (empty($consultants)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-user-doctor"></i>
                <h3>No Consultants Yet</h3>
                <p>Add your first consultant/doctor profile.</p>
                <a href="<?php echo url('/admin/consultants/create'); ?>" class="btn btn-primary">Add Consultant</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Specialty</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultants as $consultant): ?>
                            <tr>
                                <td>
                                    <div class="table-avatar">
                                        <?php if (!empty($consultant['photo'])): ?>
                                            <img src="<?php echo he(url('/' . $consultant['photo'])); ?>" alt="<?php echo he($consultant['name']); ?>">
                                        <?php else: ?>
                                            <span class="avatar-placeholder"><?php echo strtoupper(substr($consultant['name'], 0, 1)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo url('/admin/consultants/edit/' . $consultant['id']); ?>" class="table-title">
                                        <?php echo he($consultant['name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($consultant['specialty_name'])): ?>
                                        <span class="badge badge-info"><?php echo he($consultant['specialty_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($consultant['contact_number'])): ?>
                                        <small><?php echo he($consultant['contact_number']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $consultant['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo he(ucfirst($consultant['status'])); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo url('/doctors/' . $consultant['slug']); ?>" class="btn btn-sm btn-secondary" title="View" target="_blank">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('/admin/consultants/edit/' . $consultant['id']); ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/consultants/delete/' . $consultant['id']); ?>" class="inline-form" onsubmit="return confirm('Delete this consultant?');">
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
