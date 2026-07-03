<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-users"></i> Users</h1>
    <a href="<?php echo url('/admin/users/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New User
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
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>2FA Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <strong><?php echo he($u['username']); ?></strong>
                            </td>
                            <td><?php echo he($u['email']); ?></td>
                            <td><span class="badge badge-secondary"><?php echo he(ucfirst($u['role'])); ?></span></td>
                            <td>
                                <?php if (!empty($u['two_factor_enabled'])): ?>
                                    <span class="badge badge-success"><i class="fa-solid fa-shield-check"></i> Enabled</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="fa-solid fa-shield-xmark"></i> Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo url('/admin/users/edit/' . $u['id']); ?>" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </a>
                                <?php if ($u['id'] !== Auth::user()['id']): ?>
                                <form method="POST" action="<?php echo url('/admin/users/delete/' . $u['id']); ?>" class="inline-form" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
