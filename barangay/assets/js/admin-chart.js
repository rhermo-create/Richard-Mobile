// Admin Portal Dashboard Charts
const c = {
  primary: '#1e40af',
  light: '#3b82f6',
  accent: '#0ea5e9',
  success: '#16a34a',
  warning: '#f59e0b',
  danger: '#dc2626',
  muted: '#94a3b8'
};

// Monthly Incidents - Bar Chart
new Chart(document.getElementById('incidentsChart'), {
  type: 'bar',
  data: {
    labels: ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'],
    datasets: [{
      label: 'Incidents',
      data: [12, 19, 8, 15, 20, 24],
      backgroundColor: c.light,
      borderRadius: 6
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// Document Requests - Doughnut Chart
new Chart(document.getElementById('documentsChart'), {
  type: 'doughnut',
  data: {
    labels: ['Barangay Clearance', 'Certificate of Indigency', 'Certificate of Residency', 'Business Permit'],
    datasets: [{
      data: [8, 4, 3, 3],
      backgroundColor: [c.primary, c.accent, c.success, c.warning]
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } }
  }
});

// Incident Categories - Pie Chart
new Chart(document.getElementById('categoriesChart'), {
  type: 'pie',
  data: {
    labels: ['Noise Complaint', 'Vandalism', 'Flooding', 'Stray Animals', 'Other'],
    datasets: [{
      data: [7, 5, 4, 3, 5],
      backgroundColor: [c.primary, c.danger, c.accent, c.warning, c.muted]
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } }
  }
});

// Resident Registration - Line Chart
new Chart(document.getElementById('residentsChart'), {
  type: 'line',
  data: {
    labels: ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'],
    datasets: [{
      label: 'New Residents',
      data: [8, 14, 10, 18, 22, 12],
      borderColor: c.success,
      backgroundColor: 'rgba(22,163,74,0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 4,
      pointBackgroundColor: c.success
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
