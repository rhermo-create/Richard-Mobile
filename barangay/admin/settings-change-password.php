<?php
require_once '../includes/auth.php';
requireAdmin();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  // Fetch current password hash from database
  $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user || !password_verify($current, $user['password'])) {
    $error = 'Current password is incorrect.';
  } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/', $new)) {
    $error = 'Password must contain uppercase, lowercase, number and special character.';
  } elseif ($new !== $confirm) {
    $error = 'New password and confirmation do not match.';
  } else {
    // Update password
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$newHash, $user_id]);
    $_SESSION['message'] = 'Password changed successfully.';
    header('Location: settings-change-password.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Change Password — Settings</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/sidebar.css" />
  <link rel="stylesheet" href="../assets/css/settings.css" />
</head>

<body>

  <header class="header">
    <div class="header__inner">
      <a href="../admin/admin_portal.php" class="header__brand">
        <div class="header__logo">🏛️</div>
        <div>
          <div class="header__title">Barangay Online Services</div>
          <div class="header__subtitle">Admin Portal</div>
        </div>
      </a>
      <nav class="nav">
        <a href="../admin/admin_portal.php" class="nav__link">Home</a>
        <p> |</p>
        <h5>Change Password</h5>
      </nav>
    </div>
  </header>

  <div class="layout-with-sidebar">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar__header">
        <div class="sidebar__avatar">🛡️</div>
        <div>
          <div class="sidebar__name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
          <div class="sidebar__role">Barangay Captain</div>
        </div>
      </div>
      <nav class="sidebar__nav">
        <div class="sidebar__section-label">Overview</div>
        <a href="../admin/admin_portal.php" class="sidebar__link">
          <span class="sidebar__icon">📊</span> Dashboard
        </a>
        <div class="sidebar__section-label">Management</div>
        <a href="../admin/admin_portal.php#incidents" class="sidebar__link">
          <span class="sidebar__icon">🚨</span> Incident Reports
        </a>
        <a href="../admin/admin_portal.php#documents" class="sidebar__link">
          <span class="sidebar__icon">📄</span> Document Requests
        </a>
        <a href="../admin/admin_portal.php#residents" class="sidebar__link">
          <span class="sidebar__icon">👥</span> Residents
        </a>
        <div class="sidebar__section-label">System Settings</div>
        <a href="../admin/create_admin.php" class="sidebar__link">
          <span class="sidebar__icon">➕</span> Create Admin
        </a>
        <a href="../admin/settings-change-password.php" class="sidebar__link sidebar__link--active">
          <span class="sidebar__icon">⚙️</span> Settings
        </a>
      </nav>
      <div class="sidebar__footer">
        <a href="../public/logout.php" class="sidebar__link">
          <span class="sidebar__icon">🚪</span> Logout
        </a>
      </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <main class="sidebar-main">
      <div class="portal-welcome portal-welcome--admin">
        <div class="portal-welcome__avatar">⚙️</div>
        <div>
          <h1 class="portal-welcome__name">Settings</h1>
          <p class="portal-welcome__role">Manage system configuration</p>
        </div>
      </div>

      <nav class="settings-tabs">
        <a href="../admin/settings-barangay-info.php" class="settings-tab">🏛️ Barangay Info</a>
        <a href="../admin/settings-incident-categories.php" class="settings-tab">🚨 Incident Categories</a>
        <a href="../admin/settings-document-types.php" class="settings-tab">📄 Document Types</a>
        <a href="../admin/settings-change-password.php" class="settings-tab settings-tab--active">🔒 Change Password</a>
      </nav>

      <!-- Display session message or error -->
      <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'];
          unset($_SESSION['message']); ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
      <?php endif; ?>


      <section class="settings-card">
        <h2 class="settings-card__title">Change Password</h2>
        <p class="settings-card__desc">Update your admin account password. Use a strong password with at least 8 characters.</p>

        <form class="settings-form settings-form--narrow" method="POST">
          <div class="form-group">
            <label class="label">Current Password <span class="label__required">*</span></label>
            <input type="password" name="current_password" class="input" placeholder="Enter current password" required minlength="8" />
          </div>

          <div class="form-group">
            <label class="label">New Password <span class="label__required">*</span></label>
            <input type="password" name="new_password" class="input" placeholder="Enter new password" required minlength="8" />
            <span class="form-hint">Must be at least 8 characters long</span>
          </div>

          <div class="form-group">
            <label class="label">Confirm New Password <span class="label__required">*</span></label>
            <input type="password" name="confirm_password" class="input" placeholder="Re-enter new password" required minlength="8" />
          </div>

          <div class="password-requirements">
            <div class="password-requirements__title">Password Requirements:</div>
            <ul class="password-requirements__list">
              <li>At least 8 characters long</li>
              <li>Contains at least one uppercase letter</li>
              <li>Contains at least one lowercase letter</li>
              <li>Contains at least one number</li>
              <li>Contains at least one special character (!@#$%^&*)</li>
            </ul>
          </div>

          <div class="settings-form__actions">
            <button type="submit" class="btn btn--submit">Update Password</button>
          </div>
        </form>
      </section>
    </main>
  </div>

  <footer class="footer">
    <div class="footer__inner">
      <hr class="footer__divider" />
      <div class="footer__bottom">© 2026 Barangay Online Services. All rights reserved.</div>
    </div>
  </footer>

  <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">☰</button>
  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('sidebar--open');
      document.getElementById('sidebarOverlay').classList.toggle('sidebar-overlay--visible');
    }
  </script>
</body>

</html>