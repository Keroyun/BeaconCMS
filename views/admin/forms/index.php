<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-list"></i> Forms</h1>
    <a href="<?php echo url('/admin/forms/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Form
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
        <?php if (empty($forms)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-wpforms"></i>
                <h3>No Forms Yet</h3>
                <p>Create a form to collect leads, contact requests, and more.</p>
                <a href="<?php echo url('/admin/forms/create'); ?>" class="btn btn-primary">Create Form</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Shortcode</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $f): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo url('/admin/forms/builder/' . $f['id']); ?>" class="table-title">
                                        <?php echo he($f['title']); ?>
                                    </a>
                                </td>
                                <td><code>[beacon_form id="<?php echo he($f['shortcode']); ?>"]</code></td>
                                <td>
                                    <span class="badge badge-<?php echo $f['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo he(ucfirst($f['status'])); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo url('/admin/forms/entries/' . $f['id']); ?>" class="btn btn-sm btn-info" title="Entries (Inbox)">
                                        <i class="fa-solid fa-inbox"></i> Entries
                                    </a>
                                    <a href="<?php echo url('/admin/forms/builder/' . $f['id']); ?>" class="btn btn-sm btn-primary" title="Builder">
                                        <i class="fa-solid fa-pen-ruler"></i> Builder
                                    </a>
                                    <a href="<?php echo url('/admin/forms/connectors/' . $f['id']); ?>" class="btn btn-sm btn-warning" title="Connectors">
                                        <i class="fa-solid fa-plug"></i> Connectors
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/forms/delete/' . $f['id']); ?>" class="inline-form" onsubmit="return confirm('Delete this form and ALL its entries?');">
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
