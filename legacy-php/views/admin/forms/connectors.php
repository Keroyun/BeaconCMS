<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-plug"></i> <?php echo he($pageTitle); ?></h1>
    <div class="d-flex" style="gap:10px">
        <a href="<?php echo url('/admin/forms/builder/' . $form['id']); ?>" class="btn btn-primary">
            <i class="fa-solid fa-pen-ruler"></i> Builder
        </a>
        <a href="<?php echo url('/admin/forms'); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Forms
        </a>
    </div>
</div>

<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-8">
        <?php foreach ($availableConnectors as $type => $connector): ?>
            <?php 
                $isActive = isset($activeConfig[$type]) && $activeConfig[$type]['is_active']; 
                $config = $activeConfig[$type]['config'] ?? [];
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><?php echo he($connector->getName()); ?></h3>
                    <?php if ($isActive): ?>
                        <span class="badge badge-success">Active</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Inactive</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/admin/forms/connectors/' . $form['id']); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
                        <input type="hidden" name="connector_type" value="<?php echo he($type); ?>">
                        
                        <div class="form-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="is_active" value="1" <?php echo $isActive ? 'checked' : ''; ?>>
                                <span>Enable this integration</span>
                            </label>
                        </div>

                        <?php foreach ($connector->getConfigFields() as $fieldKey => $fieldDef): ?>
                            <div class="form-group">
                                <label for="config_<?php echo he($fieldKey); ?>">
                                    <?php echo he($fieldDef['label']); ?>
                                    <?php if (!empty($fieldDef['required'])) echo ' <span class="required">*</span>'; ?>
                                </label>
                                <input type="<?php echo he($fieldDef['type']); ?>" 
                                       id="config_<?php echo he($fieldKey); ?>" 
                                       name="config_<?php echo he($fieldKey); ?>" 
                                       class="form-control" 
                                       value="<?php echo he($config[$fieldKey] ?? ''); ?>"
                                       <?php echo !empty($fieldDef['required']) ? 'required' : ''; ?>>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Settings</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="col-4">
        <div class="card bg-light">
            <div class="card-header">
                <h3>How to Map Fields</h3>
            </div>
            <div class="card-body">
                <p>When configuring integrations like Zendesk, you can insert data from the form using variables.</p>
                <p>Wrap the exact <strong>Field Name</strong> (defined in the Builder) in curly braces.</p>
                <hr style="border-color:#e2e8f0; margin:1rem 0;">
                <p><strong>Example:</strong></p>
                <p>If your form has a field named <code>full_name</code>, you can use:</p>
                <code>{full_name}</code>
                <p class="mt-2">In the Zendesk subject line: <em>Support Request from {full_name}</em></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
