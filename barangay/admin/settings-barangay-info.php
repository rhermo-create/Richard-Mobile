<?php
require_once '../includes/auth.php';
requireAdmin();

$stmt = $pdo->prepare("SELECT * FROM barangay_info WHERE id=?");
$stmt->execute([1]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE barangay_info 
        SET name=?, city=?, province=?, region=?, address=?, phone=?, email=?, captain=?, sk_chair=?, office_hours=? 
        WHERE id=1
    ");
    $stmt->execute([
        $_POST['name'], $_POST['city'], $_POST['province'], $_POST['region'],
        $_POST['address'], $_POST['phone'], $_POST['email'], $_POST['captain'],
        $_POST['sk_chair'], $_POST['office_hours']
    ]);
    $_SESSION['message'] = 'Barangay information updated.';
    header('Location: settings-barangay-info.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Barangay Information Settings - Update barangay details" />
  <title>Barangay Info — Settings</title>
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
        <h5>Barangay Info</h5>
      </nav>
    </div>
  </header>

  <div class="layout-with-sidebar">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar__header">
        <div class="sidebar__avatar">🛡️</div>
        <div>
          <div class="sidebar__name">_<?= htmlspecialchars($_SESSION['user_name']) ?></div>
          <div class="sidebar__role">Barangay Captain</div>
        </div>
      </div>
      <nav class="sidebar__nav">
        <div class="sidebar__section-label">Overview</div>
        <a href="../admin/admin_portal.php" class="sidebar__link">
          <span class="sidebar__icon">📊</span> Dashboard
        </a>
        <div class="sidebar__section-label">Management</div>
        <a href="../admin/incidents_admin.php" class="sidebar__link">
          <span class="sidebar__icon">🚨</span> Incident Reports
        </a>
        <a href="../admin/documents_admin.php" class="sidebar__link">
          <span class="sidebar__icon">📄</span> Document Requests
        </a>
        <a href="../admin/residents_admin.php" class="sidebar__link">
          <span class="sidebar__icon">👥</span> Residents
        </a>
        <div class="sidebar__section-label">System Settings</div>
        <a href="../admin/create_admin.php" class="sidebar__link">
          <span class="sidebar__icon">➕</span> Create Admin
        </a>
        <a href="../admin/settings-barangay-info.php" class="sidebar__link sidebar__link--active">
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

      <!-- Settings Tabs -->
      <nav class="settings-tabs">
        <a href="../admin/settings-barangay-info.php" class="settings-tab settings-tab--active">🏛️ Barangay Info</a>
        <a href="../admin/settings-incident-categories.php" class="settings-tab">🚨 Incident Categories</a>
        <a href="../admin/settings-document-types.php" class="settings-tab">📄 Document Types</a>
        <a href="../admin/settings-change-password.php" class="settings-tab">🔒 Change Password</a>
      </nav>

      <!-- Display session message -->
      <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
      <?php endif; ?>

      <!-- Barangay Info — Read-Only View -->
      <section class="settings-card" id="viewMode">
        <div class="settings-card__header-row">
          <div>
            <h2 class="settings-card__title">Barangay Information</h2>
            <p class="settings-card__desc" style="margin-bottom:0;">Official details of your barangay displayed across the system.</p>
          </div>
          <button type="button" class="btn btn--compact btn--outline" onclick="toggleEdit(true)">✏️ Edit</button>
        </div>

        <div class="info-grid">
          <div class="info-item">
            <span class="info-item__label">Barangay Name</span>

            <input type="text" class="input" value="<?= htmlspecialchars($info['name']) ?>" readonly>
          </div>
          <div class="info-item">
            <span class="info-item__label">Municipality / City</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['city']) ?>" readonly>
          </div>
          <div class="info-item">
            <span class="info-item__label">Province</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['province']) ?>" readonly>
          </div>
          <div class="info-item">
            <span class="info-item__label">Region</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['region']) ?>" readonly>
          </div>
          <div class="info-item info-item--full">
            <span class="info-item__label">Full Address</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['address']) ?>" readonly>
          </div>
          <div class="info-item">
            <span class="info-item__label">Contact Number</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['phone']) ?>" readonly>
          </div>
          <div class="info-item">
            <span class="info-item__label">Email Address</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['email']) ?>" readonly>
          </div>
          <div class="info-item">
            <span class="info-item__label">Barangay Captain</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['captain']) ?>" readonly>
          </div>
          <div class="info-item">
            <span class="info-item__label">SK Chairperson</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['sk_chair']) ?>" readonly>
          </div>
          <div class="info-item info-item--full">
            <span class="info-item__label">Office Hours</span>
            <input type="text" class="input" value="<?= htmlspecialchars($info['office_hours']) ?>" readonly>
          </div>
        </div>
      </section>

      <!-- Barangay Info — Edit Form (hidden by default) -->
      <section class="settings-card" id="editMode" style="display:none;">
        <div class="settings-card__header-row">
          <div>
            <h2 class="settings-card__title">Edit Barangay Information</h2>
            <p class="settings-card__desc" style="margin-bottom:0;">Update the basic details of your barangay.</p>
          </div>
          <button type="button" class="btn btn--compact btn--ghost" onclick="toggleEdit(false)">✖ Cancel</button>
        </div>

        <form class="settings-form" method="POST">
          <div class="form-row">
            <div class="form-group">
              <label class="label">Barangay Name <span class="label__required">*</span></label>
              <input type="text" name="name" class="input" value="<?= htmlspecialchars($info['name']) ?>" required />
            </div>
            <div class="form-group">
              <label class="label">Municipality / City <span class="label__required">*</span></label>
              <input type="text" name="city" class="input" value="<?= htmlspecialchars($info['city']) ?>" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="label">Province</label>
              <input type="text" name="province" class="input" value="<?= htmlspecialchars($info['province']) ?>" />
            </div>
            <div class="form-group">
              <label class="label">Region</label>
              <input type="text" name="region" class="input" value="<?= htmlspecialchars($info['region']) ?>" />
            </div>
          </div>

          <div class="form-group">
            <label class="label">Full Address</label>
            <textarea class="textarea" rows="2" name="address" required><?= htmlspecialchars($info['address']) ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="contactNumber" class="label">
                Contact Number <span class="label__required">*</span>
              </label>
              <input type="tel" id="contactNumber" name="phone" class="input" value="<?= htmlspecialchars($info['phone']) ?>" pattern="^09\d{9}$" maxlength="11" title="Enter 11 digits starting with 09" required />
            </div>
            <div class="form-group">
              <label class="label">Email Address</label>
              <input type="email" name="email" class="input" value="<?= htmlspecialchars($info['email']) ?>" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="label">Barangay Captain</label>
              <input type="text" name="captain" class="input" value="<?= htmlspecialchars($info['captain']) ?>" required />
            </div>
            <div class="form-group">
              <label class="label">SK Chairperson</label>
              <input type="text" name="sk_chair" class="input" value="<?= htmlspecialchars($info['sk_chair']) ?>" required />
            </div>
          </div>

          <div class="form-group">
            <label class="label">Office Hours</label>
            <input type="text" name="office_hours" class="input" value="<?= htmlspecialchars($info['office_hours']) ?>" required />
          </div>

          <div class="settings-form__actions">
            <button type="submit" class="btn btn--submit">💾 Save Changes</button>
            <button type="button" class="btn btn--compact btn--ghost" onclick="toggleEdit(false)" style="margin-left:0; margin-top:0.5rem;">Cancel</button>
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

    function toggleEdit(show) {
      document.getElementById('viewMode').style.display = show ? 'none' : '';
      document.getElementById('editMode').style.display = show ? '' : 'none';
    }
  </script>

</body>
</html>
