<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// If already logged in, redirect
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'admin') {
        redirect('/barangay/admin/admin_portal.php');
    } else {
        redirect('/barangay/residents/resident_portal.php');
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (loginUser($pdo, $email, $password)) {
        if ($_SESSION['user_role'] === 'admin') {
            redirect('/barangay/admin/admin_portal.php');
        } else {
            redirect('/barangay/residents/resident_portal.php');
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Barangay Online Services</title>
  <link rel="stylesheet" href="../assets/css/style.css" />

</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="header__inner">
      <a href="../public/index.php" class="header__brand">
        <div class="header__logo">🏛️</div>
        <div>
          <div class="header__title">Barangay Online Services</div>
          <div class="header__subtitle">Official Digital Portal</div>
        </div>
      </a>
      <nav class="nav">
        <a href="../public/index.php" class="nav__link">Home</a>
        <a href="../public/login.php" class="nav__link nav__link--active">Login</a>
        <a href="../public/register.php" class="nav__link">Create Account</a>
      </nav>
    </div>
  </header>

  <main class="page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-card__header">
          <div class="auth-card__icon">🔐</div>
          <h1 class="auth-card__title">Welcome Back</h1>
          <p class="auth-card__desc">Sign in to access your barangay portal</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="auth-form login-form" action="" method="post">
          <div class="form-group">
            <label class="label" for="email">Email Address <span class="label__required">*</span></label>
            <input class="input" type="email" id="email" name="email" placeholder="you@example.com" required />
          </div>

          <div class="form-group">
            <label class="label" for="password">Password <span class="label__required">*</span></label>
            <input class="input" type="password" id="password" name="password" placeholder="Enter your password" required />
          </div>

          <div class="auth-options">
            <label class="auth-remember">
              <input type="checkbox" name="remember" /> Remember me
            </label>
            <a href="#" class="auth-forgot">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn--submit">Log In</button>
        </form>

        <div class="auth-card__footer">
          Don't have an account? <a href="../public/register.php">Register here</a>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer__inner">
      <div>
        <div class="footer__brand">
            <div class="logoandname">
                <div class="footer__logo">🏛️</div>
                <span class="footer__name">Barangay Online Services</span>
            </div>
          <p class="footer__text">Your trusted digital gateway for barangay services. Faster, easier, and more accessible for every resident.</p>
        </div>
      </div>
      <div>
        <h3 class="footer__section-title">Contact</h3>
        <p class="footer__text">
          Barangay Hall, Main Street<br />
          City, Province 1234<br />
          Tel: (02) 8123-4567<br />
          Email: info@barangay.gov.ph
        </p>
      </div>
      <div>
        <h3 class="footer__section-title">Office Hours</h3>
        <p class="footer__text">
          Monday – Friday<br />
          8:00 AM – 5:00 PM<br />
          Saturday: 8:00 AM – 12:00 PM<br />
          Sunday: Closed
        </p>
      </div>
      <hr class="footer__divider" />
      <p class="footer__bottom">© 2025 Barangay Online Services. All rights reserved.</p>
    </div>
  </footer>
  <script src="../assets/js/app.js" defer></script>

</body>
</html>
