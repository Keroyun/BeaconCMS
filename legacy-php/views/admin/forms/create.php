<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-plus"></i> Create New Form</h1>
    <a href="<?php echo url('/admin/forms'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Forms
    </a>
</div>

<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="<?php echo url('/admin/forms/create'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
            <div class="form-group">
                <label for="title">Form Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Contact Us">
                <small class="text-muted">A shortcode will be automatically generated.</small>
            </div>
            <button type="submit" class="btn btn-primary">Create Form</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
