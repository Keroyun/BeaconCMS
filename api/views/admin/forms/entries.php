<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-inbox"></i> <?php echo he($pageTitle); ?></h1>
    <div class="d-flex" style="gap:10px">
        <a href="<?php echo url('/admin/forms/builder/' . $form['id']); ?>" class="btn btn-primary">
            <i class="fa-solid fa-pen-ruler"></i> Builder
        </a>
        <a href="<?php echo url('/admin/forms'); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Forms
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($entries)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-envelope-open-text"></i>
                <h3>No Entries Yet</h3>
                <p>There are no submissions for this form.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Data</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td style="white-space:nowrap;"><?php echo date('M d, Y h:i A', strtotime($entry['created_at'])); ?></td>
                                <td>
                                    <?php 
                                        $data = json_decode($entry['entry_data_json'], true);
                                        if (is_array($data)): 
                                            foreach ($data as $key => $value):
                                    ?>
                                        <div style="margin-bottom:5px;">
                                            <strong style="color:#64748b;"><?php echo he(ucfirst(str_replace(['_', '-'], ' ', $key))); ?>:</strong> 
                                            <span><?php echo he(is_array($value) ? implode(', ', $value) : $value); ?></span>
                                        </div>
                                    <?php 
                                            endforeach;
                                        endif; 
                                    ?>
                                </td>
                                <td><small class="text-muted"><?php echo he($entry['ip_address']); ?></small></td>
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
