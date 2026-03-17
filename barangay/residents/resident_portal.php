<?php
require_once '../includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT first_name, last_name, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch recent incidents
// $stmt = $pdo->prepare("SELECT id, created_at, incident_type, location, status FROM incidents WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");

// Fetch recent incidents (including evidence path)
$stmt = $pdo->prepare("SELECT id, created_at, incident_type, location, status, evidence_path FROM incidents WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$incidents = $stmt->fetchAll();

// Fetch recent document requests
$stmt = $pdo->prepare("SELECT id, created_at, document_type, purpose, status FROM document_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$documents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Resident Portal - Track your incidents and document requests" />
  <title>Resident Portal — Barangay Online Services</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/sidebar.css" />

</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="header__inner">
      <a href="../residents/resident_portal.php" class="header__brand">
        <div class="header__logo">🏛️</div>
        <div>
          <div class="header__title">Barangay Online Services</div>
          <div class="header__subtitle">Resident Portal</div>
        </div>
      </a>
      <nav class="nav">
        <a href="../residents/resident_portal.php" class="nav__link">Home</a>
        <p> |</p>
        <h5>Dashboard</h5>
      </nav>
    </div>
  </header>

  <div class="layout-with-sidebar">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar__header">
        <div class="sidebar__avatar">👤</div>
        <div>
          <h1 class="portal-welcome__name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
          <div class="sidebar__role">Resident</div>
        </div>
      </div>

      <nav class="sidebar__nav">
        <div class="sidebar__section-label">Main</div>
        <a href="../residents/resident_portal.php" class="sidebar__link sidebar__link--active">
          <span class="sidebar__icon">📊</span> Dashboard
        </a>

        <div class="sidebar__section-label">Services</div>
        <a href="../residents/incident.php" class="sidebar__link">
          <span class="sidebar__icon">🚨</span> Report Incident
        </a>
        <a href="../residents/request.php" class="sidebar__link">
          <span class="sidebar__icon">📄</span> Request Document
        </a>

        <div class="sidebar__section-label">My Profile</div>
        <a href="../residents/profile.php" class="sidebar__link">
          <span class="sidebar__icon">⚙️</span> Profile
        </a>
      </nav>

      <div class="sidebar__footer">
        <a href="../public/logout.php" class="sidebar__link">
          <span class="sidebar__icon">🚪</span> Logout
        </a>
      </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="sidebar-main">
      <!-- Welcome Section -->
      <div class="portal-welcome">
        <div class="portal-welcome__avatar">👤</div>
        <div>
          <h1 class="portal-welcome__name">Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
          <p class="portal-welcome__role">Resident • <?= htmlspecialchars($user['address']) ?></p>
        </div>
      </div>

      <!-- Quick Actions -->
      <section class="portal-actions">
        <a href="../residents/incident.php" class="portal-action-card">
          <span class="portal-action-card__icon portal-action-card__icon--incident">🚨</span>
          <span class="portal-action-card__label">Report Incident</span>
        </a>
        <a href="../residents/request.php" class="portal-action-card">
          <span class="portal-action-card__icon portal-action-card__icon--document">📄</span>
          <span class="portal-action-card__label">Request Document</span>
        </a>
      </section>

      <!-- Evidence Modal -->
      <div id="evidenceModal" class="modal">
          <div class="modal-content">
              <span class="close">&times;</span>
              <img id="modalImage" src="" alt="Evidence" style="width:100%; height: 300px;">
          </div>
      </div>

      <!-- My Incidents -->
      <section class="portal-section" id="incidents">
        <h2 class="portal-section__title">My Incident Reports</h2>
        <div class="portal-table-wrap">
          <table class="portal-table" data-rows="10">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Location</th>
                <th>Evidence</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($incidents as $inc): ?>
              <tr>
                <td><?= date('M j, Y', strtotime($inc['created_at'])) ?></td>
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
                  <form method="post" action="delete_incident.php" onsubmit="return confirm('Delete this report?');">
                    <input type="hidden" name="id" value="<?= $inc['id'] ?>">
                    <button type="submit" class="btn btn--danger">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($incidents)): ?>
              <tr>
                <td colspan="8" style="text-align:center;">No incidents requests found.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- My Document Requests -->
      <section class="portal-section" id="documents">
        <h2 class="portal-section__title">My Document Requests</h2>
        <div class="portal-table-wrap">
          <table class="portal-table" data-rows="10">
            <thead>
              <tr>
                <th>Date</th>
                <th>Document</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $doc): ?>
              <tr>
                <td><?= date('M j, Y', strtotime($doc['created_at'])) ?></td>
                <td><?= htmlspecialchars($doc['document_type']) ?></td>
                <td><?= htmlspecialchars($doc['purpose']) ?></td>
                <td>
                  <span class="status-badge status-badge--<?= $doc['status'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $doc['status'])) ?>
                  </span>
                </td>
                <td>
                  <form method="post" action="delete_request.php" onsubmit="return confirm('Delete this request?');">
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                    <button type="submit" class="btn btn--danger">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($documents)): ?>
              <tr>
                <td colspan="8" style="text-align:center;">No document requests found.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
      <!-- Profile is on a separate page (profile.html) -->
    </main>
  </div>

  <!-- Footer -->
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