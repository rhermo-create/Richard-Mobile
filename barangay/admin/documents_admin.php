<?php
require_once '../includes/auth.php';
requireAdmin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $id = $_POST['id'];
  $status = $_POST['status'];
  $stmt = $pdo->prepare("UPDATE document_requests SET status = ? WHERE id = ?");
  $stmt->execute([$status, $id]);
  $_SESSION['message'] = 'Status updated successfully.';
  header('Location: documents_admin.php');
  exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM document_requests WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = 'Document request deleted.';
    header('Location: documents_admin.php');
    exit;
}

// Fetch all document requests with user email
$requests = $pdo->query("
    SELECT document_requests.*, users.email as user_email 
    FROM document_requests 
    JOIN users ON document_requests.user_id = users.id 
    ORDER BY created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document Requests — Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/sidebar.css" />
  <link rel="stylesheet" href="../assets/css/settings.css">
  
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
        <h5>Document Requests</h5>
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
        <a href="../admin/admin_portal.php" class="sidebar__link"><span class="sidebar__icon">📊</span> Dashboard</a>
        <div class="sidebar__section-label">Management</div>
        <a href="../admin/incidents_admin.php" class="sidebar__link"><span class="sidebar__icon">🚨</span> Incident Reports</a>
        <a href="../admin/documents_admin.php" class="sidebar__link sidebar__link--active"><span class="sidebar__icon">📄</span> Document Requests</a>
        <a href="../admin/residents_admin.php" class="sidebar__link"><span class="sidebar__icon">👥</span> Residents</a>
        <div class="sidebar__section-label">System Settings</div>
        <a href="../admin/create_admin.php" class="sidebar__link"><span class="sidebar__icon">➕</span> Create Admin</a>
        <a href="../admin/settings-barangay-info.php" class="sidebar__link"><span class="sidebar__icon">⚙️</span> Settings</a>
      </nav>
      <div class="sidebar__footer">
        <a href="../public/logout.php" class="sidebar__link"><span class="sidebar__icon">🚪</span> Logout</a>
      </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <main class="sidebar-main">
      <section class="portal-section">
        <div class="portal-section__header">
          <h2 class="portal-section__title">Document Requests</h2>
        </div>

        <!-- Display session message if any -->
        <?php if (isset($_SESSION['message'])): ?>
          <div class="alert alert-info"><?= $_SESSION['message'];
            unset($_SESSION['message']); ?>
          </div>
        <?php endif; ?>

        <div class="portal-table-wrap">
          <table class="portal-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Reporter</th>
                <th>Email</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $req): ?>
              <tr>
                <td><?= $req['id'] ?></td>
                <td><?= date('M j, Y', strtotime($req['created_at'])) ?></td>
                <td><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?></td>
                <td><?= htmlspecialchars($req['user_email']) ?></td>
                <td><?= htmlspecialchars($req['document_type']) ?></td>
                <td>
                  <span class="status-badge status-badge--<?= $req['status'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $req['status'])) ?>
                  </span>
                </td>
                <td>
                  <!-- Update Status Form -->
                  <form method="post" style="display:inline-block; margin-right:5px;">
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <select name="status" class="select select--small" required>
                      <option value="pending" <?= $req['status']=='pending'?'selected':'' ?>>Pending</option>
                      <option value="processing" <?= $req['status']=='processing'?'selected':'' ?>>Processing</option>
                      <option value="ready" <?= $req['status']=='ready'?'selected':'' ?>>Ready for Pickup</option>
                      <option value="completed" <?= $req['status']=='completed'?'selected':'' ?>>Completed</option>
                      <option value="rejected" <?= $req['status']=='rejected'?'selected':'' ?>>Rejected</option>
                  </select>

                    <button type="submit" name="update_status" class="btn btn--small">Update</button>

                    <!-- Delete Button/Link -->
                  <a href="?delete=<?= $req['id'] ?>" class="btn btn--danger btn--small" onclick="return confirm('Delete this request?')">Delete</a>

                  </form>
                </td>  
              </tr>
              <?php endforeach; ?>
              <?php if (empty($requests)): ?>
              <tr>
                <td colspan="8" style="text-align:center;">No requests found.</td>
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

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar--open');
        document.getElementById('sidebarOverlay').classList.toggle('sidebar-overlay--visible');
    }
</script>
  <script src="../assets/js/table-pager.js"></script>
  
</body>
</html>