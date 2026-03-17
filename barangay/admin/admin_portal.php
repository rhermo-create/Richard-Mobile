<?php
require_once '../includes/auth.php';
requireAdmin();

// ---------- STATISTICS CARDS ----------
$totalIncidents = $pdo->query("SELECT COUNT(*) FROM incidents")->fetchColumn();
$pendingIncidents = $pdo->query("SELECT COUNT(*) FROM incidents WHERE status = 'pending'")->fetchColumn();

$totalDocuments = $pdo->query("SELECT COUNT(*) FROM document_requests")->fetchColumn();
$processingDocuments = $pdo->query("SELECT COUNT(*) FROM document_requests WHERE status = 'processing'")->fetchColumn();

$totalResidents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'resident'")->fetchColumn();
$newThisMonth = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'resident' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();

// Resolution rate: (resolved incidents / total incidents) * 100
$resolvedIncidents = $pdo->query("SELECT COUNT(*) FROM incidents WHERE status = 'resolved'")->fetchColumn();
$resolutionRate = ($totalIncidents > 0) ? round(($resolvedIncidents / $totalIncidents) * 100) : 0;

// ---------- CHART DATA ----------

// 1. Monthly incidents (last 6 months)
$monthlyIncidents = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
    FROM incidents
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
")->fetchAll();
$months = [];
$incidentCounts = [];
foreach ($monthlyIncidents as $row) {
  $months[] = $row['month'];
  $incidentCounts[] = $row['count'];
}

// 2. Document requests by type
$docTypeStats = $pdo->query("
    SELECT document_type, COUNT(*) as count
    FROM document_requests
    GROUP BY document_type
")->fetchAll();
$docLabels = [];
$docCounts = [];
foreach ($docTypeStats as $row) {
  $docLabels[] = $row['document_type'];
  $docCounts[] = $row['count'];
}

// 3. Incident categories (from incident_types table or using incident_type field)
$catStats = $pdo->query("
    SELECT incident_type, COUNT(*) as count
    FROM incidents
    GROUP BY incident_type
")->fetchAll();
$catLabels = [];
$catCounts = [];
foreach ($catStats as $row) {
  $catLabels[] = $row['incident_type'];
  $catCounts[] = $row['count'];
}

// 4. Resident registration trend (last 6 months)
$residentTrend = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
    FROM users
    WHERE role = 'resident' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
")->fetchAll();
$trendMonths = [];
$trendCounts = [];
foreach ($residentTrend as $row) {
  $trendMonths[] = $row['month'];
  $trendCounts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Portal — Barangay Online Services</title>
  <link rel="stylesheet" href="../assets/css/sidebar.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/settings.css" />
  <link rel="stylesheet" href="../assets/css/chart.css" />

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

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
        <p>|</p>
        <h5>Dashboard</h5>
      </nav>
    </div>
  </header>

  <div class="layout-with-sidebar">
    <!-- Sidebar -->
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
        <a
          href="../admin/admin_portal.php"
          class="sidebar__link sidebar__link--active">
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
        <a href="../admin/settings-barangay-info.php" class="sidebar__link">
          <span class="sidebar__icon">⚙️</span> Settings
        </a>
      </nav>

      <div class="sidebar__footer">
        <a href="../public/logout.php" class="sidebar__link">
          <span class="sidebar__icon">🚪</span> Logout
        </a>
      </div>
    </aside>

    <!-- Mobile overlay -->
    <div
      class="sidebar-overlay"
      id="sidebarOverlay"
      onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="sidebar-main">
      <!-- Admin Welcome -->
      <div class="portal-welcome portal-welcome--admin">
        <div class="portal-welcome__avatar">🛡️</div>
        <div>
          <h1 class="portal-welcome__name">Admin Dashboard</h1>
          <p class="portal-welcome__role">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
        </div>
      </div>

      <!-- Stats Overview -->
      <section class="admin-stats">
        <div class="admin-stat-card">
          <div class="admin-stat-card__number"><?= $totalIncidents ?></div>
          <div class="admin-stat-card__label">Total Incidents</div>
          <div class="admin-stat-card__sub"><?= $pendingIncidents ?> pending</div>
        </div>
        <div class="admin-stat-card">
          <div class="admin-stat-card__number"><?= $totalDocuments ?></div>
          <div class="admin-stat-card__label">Document Requests</div>
          <div class="admin-stat-card__sub"><?= $processingDocuments ?> processing</div>
        </div>
        <div class="admin-stat-card">
          <div class="admin-stat-card__number"><?= $totalResidents ?></div>
          <div class="admin-stat-card__label">Registered Residents</div>
          <div class="admin-stat-card__sub"><?= $newThisMonth ?> this month</div>
        </div>
        <div class="admin-stat-card">
          <div class="admin-stat-card__number"><?= $resolutionRate ?>%</div>
          <div class="admin-stat-card__label">Resolution Rate</div>
          <div class="admin-stat-card__sub">Based on resolved incidents</div>
        </div>
      </section>

      <!-- Charts Section -->
      <section class="charts-grid">
        <div class="chart-card">
          <h3 class="chart-card__title">Monthly Incidents (Last 6 Months)</h3>
          <canvas id="incidentsChart"></canvas>
        </div>
        <div class="chart-card">
          <h3 class="chart-card__title">Document Requests by Type</h3>
          <canvas id="documentsChart"></canvas>
        </div>
        <div class="chart-card">
          <h3 class="chart-card__title">Incident Categories</h3>
          <canvas id="categoriesChart"></canvas>
        </div>
        <div class="chart-card">
          <h3 class="chart-card__title">Resident Registration Trend</h3>
          <canvas id="residentsChart"></canvas>
        </div>
      </section>

    </main>
  </div>



  <!-- Footer -->
  <footer class="footer">
    <div class="footer__inner">
      <hr class="footer__divider" />
      <div class="footer__bottom">
        © 2026 Barangay Online Services. All rights reserved.
      </div>
    </div>
  </footer>

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    
  <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('sidebar--open');
        document.getElementById('sidebarOverlay').classList.toggle('sidebar-overlay--visible');
      }

      (function() {
        // Monthly Incidents Chart
        const ctx1 = document.getElementById('incidentsChart').getContext('2d');
        new Chart(ctx1, {
          type: 'line',
          data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
              label: 'Number of Incidents',
              data: <?= json_encode($incidentCounts) ?>,
              borderColor: 'rgb(255, 99, 132)',
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              tension: 0.1
            }]
          },
          options: {
            responsive: true
          }
        });

        // Document Requests by Type (Pie Chart)
        const ctx2 = document.getElementById('documentsChart').getContext('2d');
        new Chart(ctx2, {
          type: 'pie',
          data: {
            labels: <?= json_encode($docLabels) ?>,
            datasets: [{
              data: <?= json_encode($docCounts) ?>,
              backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
          }
        });

        // Incident Categories (Bar Chart)
        const ctx3 = document.getElementById('categoriesChart').getContext('2d');
        new Chart(ctx3, {
          type: 'bar',
          data: {
            labels: <?= json_encode($catLabels) ?>,
            datasets: [{
              label: 'Number of Reports',
              data: <?= json_encode($catCounts) ?>,
              backgroundColor: 'rgba(54, 162, 235, 0.5)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        });

        // Resident Registration Trend (Line Chart)
        const ctx4 = document.getElementById('residentsChart').getContext('2d');
        new Chart(ctx4, {
          type: 'line',
          data: {
            labels: <?= json_encode($trendMonths) ?>,
            datasets: [{
              label: 'New Registrations',
              data: <?= json_encode($trendCounts) ?>,
              borderColor: 'rgb(75, 192, 192)',
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              tension: 0.1
            }]
          },
          options: {
            responsive: true
          }
        });
      })();
  </script>

</body>

</html>