<?php
$isEdit = isset($user) && $user;
$pageTitle = $isEdit ? 'Edit User' : 'Add New User';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-user"></i> <?php echo $pageTitle; ?></h1>
    <a href="<?php echo url('/admin/users'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Users
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?php echo he($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo url('/admin/users/' . ($isEdit ? 'edit/' . $user['id'] : 'create')); ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header"><h3>User Details</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo he($user['username'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo he($user['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password <?php echo $isEdit ? '<small>(leave blank to keep current)</small>' : '<span class="required">*</span>'; ?></label>
                        <input type="password" id="password" name="password" class="form-control" <?php echo $isEdit ? '' : 'required'; ?> minlength="8" placeholder="<?php echo $isEdit ? 'Leave blank to keep current password' : 'Minimum 8 characters'; ?>">
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control">
                            <option value="editor" <?php echo ($user['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                            <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card">
                <div class="card-header"><h3>Avatar</h3></div>
                <div class="card-body">
                    <div class="image-upload-zone">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?php echo he(url('/' . $user['avatar'])); ?>" class="preview-image avatar-preview" id="avatarPreview" alt="Avatar">
                        <?php else: ?>
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i class="fa-solid fa-user-circle"></i>
                                <p>Upload avatar</p>
                            </div>
                            <img src="" class="preview-image avatar-preview hidden" id="avatarPreview" alt="Avatar preview">
                        <?php endif; ?>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="file-input">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update User' : 'Create User'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>
