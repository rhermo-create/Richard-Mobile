<?php
require_once '../includes/auth.php';
requireAdmin();

// Handle actions: add, edit, toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // Add new category
    if ($action === 'add') {
      $name = trim($_POST['name'] ?? '');
      $description = trim($_POST['description'] ?? '');
      if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO incident_categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $_SESSION['message'] = 'Category added successfully.';
      } else {
        $_SESSION['error'] = 'Category name is required.';
      }
      header('Location: settings-incident-categories.php');
      exit;
    }

    // Update existing category
    if ($action === 'update') {
      $id = $_POST['id'] ?? 0;
      $name = trim($_POST['name'] ?? '');
      $description = trim($_POST['description'] ?? '');
      if ($id && !empty($name)) {
        $stmt = $pdo->prepare("UPDATE incident_categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        $_SESSION['message'] = 'Category updated successfully.';
      } else {
        $_SESSION['error'] = 'Category name is required.';
      }
      header('Location: settings-incident-categories.php');
      exit;
    }

    // Toggle status (disable/enable)
    if ($action === 'toggle') {
      $id = $_POST['id'] ?? 0;
      if ($id) {
        // Get current status
        $stmt = $pdo->prepare("SELECT status FROM incident_categories WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetchColumn();
        $newStatus = ($current === 'active') ? 'disabled' : 'active';
        $stmt = $pdo->prepare("UPDATE incident_categories SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        $_SESSION['message'] = 'Category status updated.';
      }
      header('Location: settings-incident-categories.php');
      exit;
    }
  }
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM incident_categories ORDER BY id DESC")->fetchAll();

// If editing, fetch the record to pre-fill form
$editCategory = null;
if (isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $stmt = $pdo->prepare("SELECT * FROM incident_categories WHERE id = ?");
  $stmt->execute([$id]);
  $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Incident Categories — Settings</title>
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
        <h5>Incident Categories</h5>
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
        <a href="../admin/admin_portal.php" class="sidebar__link">
          <span class="sidebar__icon">🚨</span> Incident Reports
        </a>
        <a href="../admin/admin_portal.php" class="sidebar__link">
          <span class="sidebar__icon">📄</span> Document Requests
        </a>
        <a href="../admin/admin_portal.php" class="sidebar__link">
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

      <nav class="settings-tabs">
        <a href="../admin/settings-barangay-info.php" class="settings-tab">🏛️ Barangay Info</a>
        <a href="../admin/settings-incident-categories.php" class="settings-tab settings-tab--active">🚨 Incident Categories</a>
        <a href="../admin/settings-document-types.php" class="settings-tab">📄 Document Types</a>
        <a href="../admin/settings-change-password.php" class="settings-tab">🔒 Change Password</a>
      </nav>

      <!-- Display session messages -->
      <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'];
                                          unset($_SESSION['message']); ?></div>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['error'];
                                        unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <section class="settings-card">
        <div class="settings-card__header-row">
          <div>
            <h2 class="settings-card__title">Incident Categories</h2>
            <p class="settings-card__desc">Manage the types of incidents residents can report.</p>
          </div>
        </div>

        <!-- Add new category -->
        <form method="post" class="settings-form settings-form--narrow">
          <input type="hidden" name="action" value="<?= $editCategory ? 'update' : 'add' ?>">
          <?php if ($editCategory): ?>
            <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
          <?php endif; ?>
          <div class="form-group">
            <label class="label">Category Name <span class="label__required">*</span></label>
            <input type="text" name="name" class="input" value="<?= $editCategory ? htmlspecialchars($editCategory['name']) : '' ?>" required>
          </div>
          <div class="form-group">
            <label class="label">Description (optional)</label>
            <textarea name="description" class="textarea" rows="2"><?= $editCategory ? htmlspecialchars($editCategory['description']) : '' ?></textarea>
          </div>
          <div class="settings-form__actions">
            <button type="submit" class="btn btn--submit"><?= $editCategory ? 'Update' : 'Add' ?> Category</button>
            <?php if ($editCategory): ?>
              <a href="settings-incident-categories.php" class="btn btn--reset">Cancel</a>
            <?php endif; ?>
          </div>
        </form>

        <!-- Existing categories -->
        <div class="portal-table-wrap">
          <table class="portal-table">
            <thead>
              <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $cat): ?>
              <tr>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td><?= htmlspecialchars($cat['description']) ?></td>
                <td>
                  <span class="status-badge status-badge--<?= $cat['status'] ?>">
                    <?= ucfirst($cat['status']) ?>
                  </span>
                </td>
                <td class="settings-actions">
                  <a href="?edit=<?= $cat['id'] ?>" class="btn-icon" title="Edit">✏️</a>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="btn-icon" title="<?= $cat['status'] === 'active' ? 'Disable' : 'Enable' ?>">
                      <?= $cat['status'] === 'active' ? '🚫' : '✅' ?>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($categories)): ?>
              <tr>
                <td colspan="4" style="text-align:center;">No categories found.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
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