<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-pen-ruler"></i> <?php echo he($pageTitle); ?></h1>
    <div class="d-flex" style="gap:10px">
        <a href="<?php echo url('/admin/forms/entries/' . $form['id']); ?>" class="btn btn-info">
            <i class="fa-solid fa-inbox"></i> View Entries
        </a>
        <a href="<?php echo url('/admin/forms/connectors/' . $form['id']); ?>" class="btn btn-warning">
            <i class="fa-solid fa-plug"></i> Connectors
        </a>
        <a href="<?php echo url('/admin/forms'); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>
<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Shortcode</h3>
    </div>
    <div class="card-body">
        <p>Copy and paste this shortcode into any post or page content to display this form:</p>
        <code style="font-size:1.1rem; padding:10px; display:inline-block; background:#1a1d27; color:#10b981;">[beacon_form id="<?php echo he($form['shortcode']); ?>"]</code>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Fields Schema (JSON format)</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">For now, define your fields using JSON. (A visual drag-and-drop builder can be added in a future update).</p>
        <p class="text-muted">Example format:</p>
        <pre style="background:#1a1d27; padding:10px; border-radius:5px; color:#cbd5e1; font-size:0.85rem;">[
  { "name": "full_name", "label": "Full Name", "type": "text", "required": true },
  { "name": "email", "label": "Email Address", "type": "email", "required": true },
  { "name": "enquiry_type", "label": "Enquiry Type", "type": "select", "options": ["General", "Appointment", "Billing"] },
  { "name": "message", "label": "Message", "type": "textarea", "required": true }
]</pre>
        <form method="POST" action="<?php echo url('/admin/forms/builder/' . $form['id']); ?>" class="mt-4">
            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
            <div class="form-group">
                <textarea id="fields_json" name="fields_json" class="form-control" rows="15" style="font-family:monospace;"><?php echo he($form['fields_json']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Fields</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
