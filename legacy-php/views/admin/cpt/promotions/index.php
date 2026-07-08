<?php
$pageTitle = 'Promotions';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-bullhorn"></i> Promotions</h1>
    <a href="<?php echo url('/admin/promotions/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Promotion
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
        <?php if (empty($promotions)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-bullhorn"></i>
                <h3>No Promotions Yet</h3>
                <p>Create your first promotion or special offer.</p>
                <a href="<?php echo url('/admin/promotions/create'); ?>" class="btn btn-primary">Create Promotion</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Date Range</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promotions as $promo): ?>
                            <tr>
                                <td>
                                    <div class="table-thumbnail">
                                        <?php if (!empty($promo['featured_image'])): ?>
                                            <img src="<?php echo he(url('/' . $promo['featured_image'])); ?>" alt="">
                                        <?php else: ?>
                                            <div class="thumbnail-placeholder"><i class="fa-solid fa-image"></i></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo url('/admin/promotions/edit/' . $promo['id']); ?>" class="table-title">
                                        <?php echo he($promo['title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <small>
                                        <?php
                                        $start = $promo['start_date'] ? date('M j, Y', strtotime($promo['start_date'])) : '—';
                                        $end = $promo['end_date'] ? date('M j, Y', strtotime($promo['end_date'])) : 'Ongoing';
                                        echo "$start → $end";
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <?php
                                    $now = date('Y-m-d');
                                    $isExpired = $promo['end_date'] && $promo['end_date'] < $now;
                                    $statusClass = match(true) {
                                        $promo['status'] === 'published' && !$isExpired => 'badge-success',
                                        $isExpired => 'badge-danger',
                                        default => 'badge-warning'
                                    };
                                    $statusText = $isExpired ? 'Expired' : ucfirst($promo['status']);
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td class="actions">
                                    <a href="<?php echo url('/admin/promotions/edit/' . $promo['id']); ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/promotions/delete/' . $promo['id']); ?>" class="inline-form" onsubmit="return confirm('Delete this promotion?');">
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
