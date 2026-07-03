<?php
/**
 * BeaconCMS Admin Login Page
 * Standalone page — does NOT use admin layout
 */
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — BeaconCMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="<?php echo View::asset('/css/admin.css'); ?>">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-brand">
        <div class="brand-icon">B</div>
        <h1>BeaconCMS</h1>
        <p>Sign in to your admin panel</p>
      </div>

      <?php if ($error): ?>
        <div class="login-error">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php endif; ?>

      <form class="login-form" method="POST" action="<?php echo View::url('/admin/login'); ?>" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Auth::generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">

        <div class="form-group">
          <label for="loginUsername">Username</label>
          <input type="text" id="loginUsername" name="username" class="form-control" placeholder="Enter your username" required autofocus autocomplete="username">
        </div>

        <div class="form-group">
          <label for="loginPassword">Password</label>
          <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
          <label for="totp_code" style="display:flex; justify-content:space-between; align-items:center;">
            <span><i class="fa-solid fa-shield-halved" style="color:#6366f1; margin-right:5px;"></i> 2FA Code</span>
            <small style="color:#94a3b8; font-weight:normal;">(Optional)</small>
          </label>
          <input type="text" id="totp_code" name="totp_code" class="form-control" placeholder="123456" autocomplete="one-time-code" style="text-align:center; letter-spacing:3px; font-size:1.1rem; padding:12px;">
          <small style="color:#64748b; font-size:0.8rem; display:block; margin-top:5px;">Required only if 2FA is enabled.</small>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" id="loginSubmitBtn">
          <span class="spinner"></span>
          <span class="btn-text">Sign In</span>
        </button>
      </form>
    </div>
  </div>

  <script>
    (function() {
      var form = document.getElementById('loginForm');
      var btn = document.getElementById('loginSubmitBtn');

      form.addEventListener('submit', function() {
        btn.classList.add('loading');
        btn.disabled = true;
      });
    })();
  </script>
</body>
</html>
