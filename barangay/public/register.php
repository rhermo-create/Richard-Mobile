<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect('/barangay/residents/resident_portal.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Map form fields
    $data = [
        'first_name'  => trim($_POST['firstName'] ?? ''),
        'middle_name' => trim($_POST['middleName'] ?? ''),
        'last_name'   => trim($_POST['lastName'] ?? ''),
        'suffix'      => trim($_POST['suffix'] ?? ''),
        'email'       => trim($_POST['email'] ?? ''),
        'contact'     => trim($_POST['contact'] ?? ''),
        'address'     => trim($_POST['address'] ?? ''),
        'password'    => $_POST['password'] ?? '',
        'confirm'     => $_POST['confirmPassword'] ?? ''
    ];

    // Validation
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['contact']) || empty($data['address']) || empty($data['password'])) {
        $errors[] = 'All required fields must be filled.';
    }
    if ($data['password'] !== $data['confirm']) {
        $errors[] = 'Passwords do not match.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    // Check email uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        $errors[] = 'Email is already registered.';
    }

    if (empty($errors)) {
        if (registerUser($pdo, $data)) {
          $_SESSION['success'] = 'Registration successful! You can now log in.';
          header('Location: login.php');
          exit;
            // $success = 'Registration successful! You can now <a href="login.php">log in</a>.';
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Register for Barangay Online Services" />
  <title>Register — Barangay Online Services</title>
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
        <a href="../public/login.php" class="nav__link">Login</a>
        <a href="../public/register.php" class="nav__link nav__link--active">Create Account</a>
      </nav>
    </div>
  </header>

  <main class="page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-card__header">
          <div class="auth-card__icon">📝</div>
          <h1 class="auth-card__title">Create Account</h1>
          <p class="auth-card__desc">Register to access barangay services online</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
              <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form class="auth-form" action="" method="post">
          <div class="form-row">
            <div class="form-group">
              <label class="label" for="firstName">First Name <span class="label__required">*</span></label>
              <input class="input" type="text" id="firstName" name="firstName" placeholder="Juan" required />
            </div>
            <div class="form-group">
              <label class="label" for="middleName">Middle Name</label>
              <input class="input" type="text" id="middleName" name="middleName" placeholder="Optional" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="label" for="lastName">Last Name <span class="label__required">*</span></label>
              <input class="input" type="text" id="lastName" name="lastName" placeholder="Dela Cruz" required />
            </div>
            <div class="form-group">
              <label class="label" for="suffix">Suffix</label>
              <input class="input" type="text" id="suffix" name="suffix" placeholder="Jr. / Sr. / III (optional)" />
            </div>
          </div>

          <div class="form-group">
            <label class="label" for="email">Email Address <span class="label__required">*</span></label>
            <input class="input" type="email" id="email" name="email" placeholder="you@example.com" required />
          </div>

          <div class="form-group">
            <label class="label" for="contact">Contact Number <span class="label__required">*</span></label>
            <input type="tel" id="contactNumber" name="contact" class="input" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" title="Enter 11 digits starting with 09" required />
          </div>

          <div class="form-group">
            <label class="label" for="address">Complete Address <span class="label__required">*</span></label>
            <input class="input" type="text" id="address" name="address" placeholder="House No., Street, Purok/Sitio" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="label" for="password">Password <span class="label__required">*</span></label>
              <input class="input" type="password" id="password" name="password" placeholder="Min. 8 characters" required />
            </div>
            <div class="form-group">
              <label class="label" for="confirmPassword">Confirm Password <span class="label__required">*</span></label>
              <input class="input" type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn--submit">Create Account</button>
            <button type="reset" class="btn btn--reset">Reset Form</button>
          </div>
        </form>

        <div class="auth-card__footer">
          Already have an account? <a href="../public/login.php">Sign in here</a>
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

</body>
</html>
