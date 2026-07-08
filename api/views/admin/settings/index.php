<?php
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-gear"></i> <?php echo he($pageTitle); ?></h1>
</div>

<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>
<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo url('/admin/settings'); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
    
    <div class="form-grid">
        <div class="form-main">
            
            <!-- General Settings -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-info-circle"></i> General</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?php echo he($settings['site_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Site Description</label>
                        <textarea name="site_description" class="form-control" rows="2"><?php echo he($settings['site_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Footer Text</label>
                        <input type="text" name="footer_text" class="form-control" value="<?php echo he($settings['footer_text'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="homepage_page_id">
                            <i class="fa-solid fa-house" style="margin-right:5px;color:var(--accent);"></i>
                            Front Page (Homepage)
                        </label>
                        <select id="homepage_page_id" name="homepage_page_id" class="form-control">
                            <option value="">— Default Dynamic Homepage —</option>
                            <?php if (!empty($allPages)): ?>
                                <?php foreach ($allPages as $p): ?>
                                    <option value="<?php echo (int)$p['id']; ?>"
                                        <?php echo ((int)($settings['homepage_page_id'] ?? 0) === (int)$p['id']) ? 'selected' : ''; ?>>
                                        <?php echo he($p['title']); ?>
                                        <?php echo $p['status'] !== 'published' ? ' (Draft)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small style="color:var(--text-muted);margin-top:4px;display:block;">
                            Select a page to use as the site's front page, or leave as "Default Dynamic Homepage" to show the automatic feed of doctors, specialties and promotions.
                        </small>
                    </div>
                </div>
            </div>

            <!-- SMTP Settings -->
            <div class="card mt-4">
                <div class="card-header"><h3><i class="fa-solid fa-envelope"></i> SMTP Email (WP Mail SMTP)</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-8">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?php echo he($settings['smtp_host'] ?? ''); ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="form-group col-4">
                            <label>SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="<?php echo he($settings['smtp_port'] ?? '587'); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>SMTP Username</label>
                            <input type="text" name="smtp_username" class="form-control" value="<?php echo he($settings['smtp_username'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-6">
                            <label>SMTP Password / App Password</label>
                            <input type="password" name="smtp_password" class="form-control" value="<?php echo he($settings['smtp_password'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-4">
                            <label>Encryption</label>
                            <select name="smtp_encryption" class="form-control">
                                <option value="tls" <?php echo ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="none" <?php echo ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label>From Email</label>
                            <input type="email" name="smtp_from_email" class="form-control" value="<?php echo he($settings['smtp_from_email'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-4">
                            <label>From Name</label>
                            <input type="text" name="smtp_from_name" class="form-control" value="<?php echo he($settings['smtp_from_name'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Captcha Settings -->
            <div class="card mt-4">
                <div class="card-header"><h3><i class="fa-solid fa-shield-halved"></i> Form Captcha</h3></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:0.85rem;">Protect your forms from spam using Cloudflare Turnstile or Google reCAPTCHA v3.</p>
                    <div class="form-group">
                        <label>Provider</label>
                        <select name="captcha_provider" class="form-control">
                            <option value="">Disabled</option>
                            <option value="turnstile" <?php echo ($settings['captcha_provider'] ?? '') === 'turnstile' ? 'selected' : ''; ?>>Cloudflare Turnstile</option>
                            <option value="recaptcha_v3" <?php echo ($settings['captcha_provider'] ?? '') === 'recaptcha_v3' ? 'selected' : ''; ?>>Google reCAPTCHA v3</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Site Key</label>
                            <input type="text" name="captcha_site_key" class="form-control" value="<?php echo he($settings['captcha_site_key'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-6">
                            <label>Secret Key</label>
                            <input type="text" name="captcha_secret_key" class="form-control" value="<?php echo he($settings['captcha_secret_key'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu Settings -->
            <div class="card mt-4">
                <div class="card-header"><h3><i class="fa-solid fa-bars"></i> Navigation Menu (HTML)</h3></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:0.85rem;">Customise your main menu links using HTML. Leaving these fields blank will fall back to the default hardcoded navigation menu.</p>
                    
                    <?php
                    $activeLanguages = class_exists('Language') ? Language::getAll() : ['en' => ['name' => 'English', 'native_name' => 'English', 'flag' => '🇬🇧']];
                    foreach ($activeLanguages as $code => $lang):
                        $settingKey = 'navbar_menu_' . $code;
                        $menuHtml = $settings[$settingKey] ?? '';
                    ?>
                        <div class="form-group">
                            <label for="<?php echo $settingKey; ?>">
                                <?php echo htmlspecialchars($lang['flag'] ?? '', ENT_QUOTES, 'UTF-8'); ?> 
                                Menu HTML (<?php echo htmlspecialchars($lang['native_name'] ?? $lang['name'], ENT_QUOTES, 'UTF-8'); ?>)
                            </label>
                            <textarea id="<?php echo $settingKey; ?>" name="<?php echo $settingKey; ?>" class="form-control" rows="6" style="font-family:monospace; font-size:0.85rem; background:#16181f; color:#cbd5e1; border:1px solid #2d313f;" placeholder='<a href="/doctors" class="navbar__link">Doctors</a>&#10;<a href="/specialties" class="navbar__link">Specialties</a>'><?php echo htmlspecialchars($menuHtml, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <small class="text-muted" style="font-size:0.75rem; display:block; margin-top:4px;">
                                Use <code>&lt;a href="/path" class="navbar__link"&gt;Label&lt;/a&gt;</code> for each item.
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
        
        <div class="form-sidebar">
            <div class="card">
                <div class="card-header"><h3>Save</h3></div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
