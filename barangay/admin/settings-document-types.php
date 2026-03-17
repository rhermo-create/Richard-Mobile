<?php
require_once '../includes/auth.php';
requireAdmin();

// Handle actions: add, edit, toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Add new document type
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $processing_time = trim($_POST['processing_time'] ?? '');
            $fee = floatval($_POST['fee'] ?? 0);
            if (!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO document_types (name, processing_time, fee) VALUES (?, ?, ?)");
                $stmt->execute([$name, $processing_time, $fee]);
                $_SESSION['message'] = 'Document type added successfully.';
            } else {
                $_SESSION['error'] = 'Document type name is required.';
            }
            header('Location: settings-document-types.php');
            exit;
        }

        // Update existing document type
        if ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $processing_time = trim($_POST['processing_time'] ?? '');
            $fee = floatval($_POST['fee'] ?? 0);
            if ($id && !empty($name)) {
                $stmt = $pdo->prepare("UPDATE document_types SET name = ?, processing_time = ?, fee = ? WHERE id = ?");
                $stmt->execute([$name, $processing_time, $fee, $id]);
                $_SESSION['message'] = 'Document type updated successfully.';
            } else {
                $_SESSION['error'] = 'Document type name is required.';
            }
            header('Location: settings-document-types.php');
            exit;
        }

        // Toggle status (disable/enable)
        if ($action === 'toggle') {
            $id = $_POST['id'] ?? 0;
            if ($id) {
                $stmt = $pdo->prepare("SELECT status FROM document_types WHERE id = ?");
                $stmt->execute([$id]);
                $current = $stmt->fetchColumn();
                $newStatus = ($current === 'active') ? 'disabled' : 'active';
                $stmt = $pdo->prepare("UPDATE document_types SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $id]);
                $_SESSION['message'] = 'Document type status updated.';
            }
            header('Location: settings-document-types.php');
            exit;
        }
    }
}

// Fetch all document types
$docTypes = $pdo->query("SELECT * FROM document_types ORDER BY id DESC")->fetchAll();

// If editing, fetch the record to pre-fill form
$editDoc = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM document_types WHERE id = ?");
    $stmt->execute([$id]);
    $editDoc = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document Types — Settings</title>
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
        <h5>Document Types</h5>
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
        <a href="../admin/settings-incident-categories.php" class="settings-tab">🚨 Incident Categories</a>
        <a href="../admin/settings-document-types.php" class="settings-tab settings-tab--active">📄 Document Types</a>
        <a href="../admin/settings-change-password.php" class="settings-tab">🔒 Change Password</a>
      </nav>

      <!-- Messages -->
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
            <h2 class="settings-card__title">Document Types</h2>
            <p class="settings-card__desc">Manage the types of documents residents can request.</p>
          </div>
        </div>

        <form method="post" class="settings-form settings-form--narrow">
          <input type="hidden" name="action" value="<?= $editDoc ? 'update' : 'add' ?>">
          <?php if ($editDoc): ?>
            <input type="hidden" name="id" value="<?= $editDoc['id'] ?>">
          <?php endif; ?>
          <div class="form-group">
            <label class="label">Document Type <span class="label__required">*</span></label>
            <input type="text" name="name" class="input" value="<?= $editDoc ? htmlspecialchars($editDoc['name']) : '' ?>" required>
          </div>
          <div class="form-group">
            <label class="label">Processing Time (e.g., "1–2 days")</label>
            <input type="text" name="processing_time" class="input" value="<?= $editDoc ? htmlspecialchars($editDoc['processing_time']) : '' ?>">
          </div>
          <div class="form-group">
            <label class="label">Fee (₱)</label>
            <input type="number" step="0.01" min="0" name="fee" class="input" value="<?= $editDoc ? htmlspecialchars($editDoc['fee']) : '0' ?>">
          </div>
          <div class="settings-form__actions">
            <button type="submit" class="btn btn--submit" style="margin-bottom: 20px;"><?= $editDoc ? 'Update' : 'Add' ?> Document Type</button>
            <?php if ($editDoc): ?>
              <a href="settings-document-types.php" class="btn btn--reset">Cancel</a>
            <?php endif; ?>
          </div>
        </form>

        <div class="portal-table-wrap">
          <table class="portal-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Document Type</th>
                <th>Processing Time</th>
                <th>Fee</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($docTypes as $doc): ?>
                <tr>
                  <td><?= $doc['id'] ?></td>
                  <td><?= htmlspecialchars($doc['name']) ?></td>
                  <td><?= htmlspecialchars($doc['processing_time']) ?></td>
                  <td>₱<?= number_format($doc['fee'], 2) ?></td>
                  <td>
                    <span class="status-badge status-badge--<?= $doc['status'] ?>">
                      <?= ucfirst($doc['status']) ?>
                    </span>
                  </td>
                  <td class="settings-actions">
                    <a href="?edit=<?= $doc['id'] ?>" class="btn-icon" title="Edit">✏️</a>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                      <button type="submit" class="btn-icon" title="<?= $doc['status'] === 'active' ? 'Disable' : 'Enable' ?>">
                        <?= $doc['status'] === 'active' ? '🚫' : '✅' ?>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($docTypes)): ?>
                <tr>
                  <td colspan="6" style="text-align:center;">No document types found.</td>
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