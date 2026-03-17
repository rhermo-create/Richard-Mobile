<?php
require_once '../includes/auth.php';
requireAdmin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $id = $_POST['id'];
  $status = $_POST['status'];
  $stmt = $pdo->prepare("UPDATE incidents SET status = ? WHERE id = ?");
  $stmt->execute([$status, $id]);
  $_SESSION['message'] = 'Status updated successfully.';
  header('Location: incidents_admin.php');
  exit;
}

// Handle delete
// if (isset($_GET['delete'])) {
//   $id = $_GET['delete'];
  // Optional: verify incident exists (you can add a check)
//   $stmt = $pdo->prepare("DELETE FROM incidents WHERE id = ?");
//   $stmt->execute([$id]);
//   $_SESSION['message'] = 'Incident deleted.';
//   header('Location: incidents_admin.php');
//   exit;
// }

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // First, fetch the evidence path
    $stmt = $pdo->prepare("SELECT evidence_path FROM incidents WHERE id = ?");
    $stmt->execute([$id]);
    $incident = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If there's an evidence file, delete it from the server
    if ($incident && !empty($incident['evidence_path'])) {
        $filePath = dirname(__DIR__) . '/' . $incident['evidence_path']; // constructs absolute path
        if (file_exists($filePath)) {
            unlink($filePath); // delete the file
        }
    }
    
    // Now delete the incident record from database
    $stmt = $pdo->prepare("DELETE FROM incidents WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = 'Incident deleted.';
    header('Location: incidents_admin.php');
    exit;
}

// Fetch all incidents with user email
$incidents = $pdo->query("
    SELECT incidents.*, users.email as user_email 
    FROM incidents 
    JOIN users ON incidents.user_id = users.id 
    ORDER BY created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Incident Reports — Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/sidebar.css" />
  <link rel="stylesheet" href="../assets/css/settings.css">

</head>

<body>

  <!-- Header -->
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
        <h5>Incident Reports</h5>
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
        <a href="../admin/incidents_admin.php" class="sidebar__link sidebar__link--active"><span class="sidebar__icon">🚨</span> Incident Reports</a>
        <a href="../admin/documents_admin.php" class="sidebar__link"><span class="sidebar__icon">📄</span> Document Requests</a>
        <a href="../admin/residents_admin.php" class="sidebar__link"><span class="sidebar__icon">👥</span> Residents</a>
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
          <h2 class="portal-section__title">Recent Incident Reports</h2>
        </div>

        <!-- Display session message if any -->
        <?php if (isset($_SESSION['message'])): ?>
          <div class="alert alert-info"><?= $_SESSION['message'];
            unset($_SESSION['message']); ?>
          </div>
        <?php endif; ?>

        <!-- Evidence Modal -->
        <div id="evidenceModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <img id="modalImage" src="" alt="Evidence" style="width:100%; height:300px;">
            </div>
        </div>

        <div class="portal-table-wrap">
          <table class="portal-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Reporter</th>
                <th>Email</th>
                <th>Type</th>
                <th>Location</th>
                <th>Evidence</th> 
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($incidents as $inc): ?>
              <tr>
                <td><?= $inc['id'] ?></td>
                <td><?= date('M j, Y', strtotime($inc['created_at'])) ?></td>
                <td><?= htmlspecialchars($inc['first_name'] . ' ' . $inc['last_name']) ?></td>
                <td><?= htmlspecialchars($inc['user_email']) ?></td>
                <td><?= htmlspecialchars($inc['incident_type']) ?></td>
                <td><?= htmlspecialchars($inc['location']) ?></td>
                <td>
                  <?php if (!empty($inc['evidence_path'])): ?>
                      <button class="btn btn--small view-evidence" data-path="../<?= htmlspecialchars($inc['evidence_path']) ?>">View</button>
                  <?php else: ?>
                      <span class="text-muted">None</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge status-badge--<?= $inc['status'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $inc['status'])) ?>
                  </span>
                </td>
                <td>
                  <!-- Update Status Form -->
                  <form method="post" style="display:inline-block; margin-right:0; width: 100px; gap: 5px;">
                    <input type="hidden" name="id" value="<?= $inc['id'] ?>">
                    <select name="status" class="select select--small" required>
                        <option value="pending" <?= $inc['status']=='pending'?'selected':'' ?>>Pending</option>
                        <option value="in_progress" <?= $inc['status']=='in_progress'?'selected':'' ?>>In Progress</option>
                        <option value="resolved" <?= $inc['status']=='resolved'?'selected':'' ?>>Resolved</option>
                        <option value="dismissed" <?= $inc['status']=='dismissed'?'selected':'' ?>>Dismissed</option>
                    </select>

                    <button type="submit" name="update_status" class="btn btn--small">Update</button>

                     <!-- Delete Button/Link -->
                  <a href="?delete=<?= $inc['id'] ?>" class="btn btn--danger btn--small" onclick="return confirm('Delete this incident?')">Delete</a>

                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($incidents)): ?>
              <tr>
                <td colspan="8" style="text-align:center;">No incidents found.</td>
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
<script>
    // Get modal elements
    const modal = document.getElementById('evidenceModal');
    const modalImg = document.getElementById('modalImage');
    const closeBtn = document.querySelector('.close');

    // Add click event to all "View" buttons
    document.querySelectorAll('.view-evidence').forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'block';
            modalImg.src = this.dataset.path;
        });
    });

    // Close modal when clicking the close button
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside the image
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>
  <script src="../assets/js/table-pager.js"></script>
  
</body>

</html>