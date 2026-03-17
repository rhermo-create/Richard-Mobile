<?php
require_once '../includes/auth.php';
requireAdmin();

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Only delete users with role 'resident'
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'resident'");
    $stmt->execute([$id]);
    $_SESSION['message'] = 'Resident deleted successfully.';
    header('Location: residents_admin.php');
    exit;
}

// Fetch all residents
$residents = $pdo->query("
    SELECT id, first_name, last_name, email, contact, address, created_at 
    FROM users 
    WHERE role = 'resident' 
    ORDER BY created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registered Residents — Admin</title>
  <link rel="stylesheet" href="../assets/css/sidebar.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
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
        <h5>Registered Residents</h5>
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
        <a href="../admin/documents_admin.php" class="sidebar__link"><span class="sidebar__icon">📄</span> Document Requests</a>
        <a href="../admin/residents_admin.php" class="sidebar__link sidebar__link--active"><span class="sidebar__icon">👥</span> Residents</a>
        <div class="sidebar__section-label">System Settings</div>
        <a href="../admin/create_admin.php" class="sidebar__link">
          <span class="sidebar__icon">➕</span> Create Admin
        </a>
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
          <h2 class="portal-section__title">Registered Residents</h2>
        </div>

        <!-- Display session message -->
        <?php if (isset($_SESSION['message'])): ?>
          <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="portal-table-wrap">
          <table class="portal-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Registered</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($residents as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= htmlspecialchars($r['contact']) ?></td>
                <td><?= htmlspecialchars($r['address']) ?></td>
                <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                <td>
                  <a href="?delete=<?= $r['id'] ?>" class="btn btn--danger btn--small" onclick="return confirm('Delete this resident?');">Delete</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($residents)): ?>
              <tr>
                <td colspan="6" style="text-align:center;">No residents found.</td>
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
