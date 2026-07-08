<?php
$isEdit = !empty($userData);
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-user"></i> <?php echo he($pageTitle); ?></h1>
    <a href="<?php echo url('/admin/users'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Users
    </a>
</div>

<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>
<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo he($userData['username'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo he($userData['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password <?php echo !$isEdit ? '<span class="required">*</span>' : ''; ?></label>
                        <input type="password" id="password" name="password" class="form-control" <?php echo !$isEdit ? 'required' : ''; ?>>
                        <?php if ($isEdit): ?>
                            <small class="text-muted">Leave blank to keep current password.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control">
                            <option value="admin" <?php echo ($userData['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            <option value="editor" <?php echo ($userData['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                            <option value="author" <?php echo ($userData['role'] ?? '') === 'author' ? 'selected' : ''; ?>>Author</option>
                        </select>
                    </div>

                    <?php if ($isEdit): ?>
                        <hr>
                        <h3>Two-Factor Authentication (2FA)</h3>
                        
                        <?php if (empty($userData['two_factor_secret'])): ?>
                            <p class="text-muted">2FA is currently disabled. Generate a secret to enable it.</p>
                            <a href="?action=generate_2fa" class="btn btn-secondary">Generate 2FA Secret</a>
                        <?php else: ?>
                            <?php 
                                require_once dirname(dirname(dirname(__DIR__))) . '/core/TOTP.php';
                                $qrUrl = TOTP::getQRCodeUrl('BeaconCMS', $userData['username'], $userData['two_factor_secret']);
                            ?>
                            <div style="background:#f8fafc; padding:15px; border-radius:5px; margin-bottom:15px;">
                                <p><strong>1. Scan this QR Code with Google Authenticator or Authy:</strong></p>
                                <img src="<?php echo he($qrUrl); ?>" alt="QR Code" style="margin-bottom:10px;">
                                <p>Or manually enter this secret: <code><?php echo he($userData['two_factor_secret']); ?></code></p>
                                
                                <div class="form-group mt-3">
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="two_factor_enabled" value="1" <?php echo !empty($userData['two_factor_enabled']) ? 'checked' : ''; ?>>
                                        <span>Enable 2FA for this account</span>
                                    </label>
                                </div>

                                <?php if (empty($userData['two_factor_enabled'])): ?>
                                    <div class="form-group">
                                        <label>Enter 6-digit code to verify:</label>
                                        <input type="text" name="totp_code" class="form-control" style="max-width:200px;" placeholder="123456">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="?action=disable_2fa" class="btn btn-danger btn-sm" onclick="return confirm('Disable and remove 2FA?');">Remove 2FA completely</a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
