<?php
$pageTitle = 'Posts';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-file-pen"></i> Posts</h1>
    <a href="<?php echo url('/admin/posts/create'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New Post
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
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-file-pen"></i>
                <h3>No Posts Yet</h3>
                <p>Create your first post to get started.</p>
                <a href="<?php echo url('/admin/posts/create'); ?>" class="btn btn-primary">Create Post</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo url('/admin/posts/edit/' . $post['id']); ?>" class="table-title">
                                        <?php echo he($post['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo he($post['author_name'] ?? 'Unknown'); ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($post['status']) {
                                        'published' => 'badge-success',
                                        'draft' => 'badge-warning',
                                        'archived' => 'badge-secondary',
                                        default => 'badge-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo he(ucfirst($post['status'])); ?></span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="<?php echo url('/admin/posts/edit/' . $post['id']); ?>" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/posts/delete/' . $post['id']); ?>" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this post?');">
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
include dirname(__DIR__) . '/layout.php';
?>
