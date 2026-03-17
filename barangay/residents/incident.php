<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$categories = $pdo->query("SELECT name FROM incident_categories WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $contact = trim($_POST['contactNumber'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $type = $_POST['incidentType'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Handle file upload
    $evidencePath = null;
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/incidents/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['evidence']['tmp_name'], $destination)) {
            $evidencePath = 'uploads/incidents/' . $filename; // relative path for db
        }
    }

    $stmt = $pdo->prepare("INSERT INTO incidents (user_id, first_name, last_name, contact, location, incident_type, description, evidence_path) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$user_id, $firstName, $lastName, $contact, $location, $type, $description, $evidencePath]);

    $_SESSION['success'] = 'Incident reported successfully.';
    redirect('/barangay/residents/resident_portal.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Report an incident to your barangay officials." />
  <title>Report Incident – Barangay Online Services</title>
  <link rel="stylesheet" href="../assets/css/style.css" />

</head>
<body>
  <!-- ===== Header ===== -->
  <header class="header">
    <div class="header__inner">
      <a href="../residents/resident_portal.php" class="header__brand">
        <div class="header__logo">🏛️</div>
        <div>
          <div class="header__title">Barangay Online Services</div>
          <div class="header__subtitle">Municipal Government Portal</div>
        </div>
      </a>
      <nav class="nav" aria-label="Main Navigation">
        <a href="../residents/resident_portal.php" class="nav__link">Home</a>
        <p> |</p>
        <h5>Incident Reports</h5>
      </nav>
    </div>
  </header>
  <!-- ===== Main Content ===== -->
  <main class="page">
    <!-- Back Navigation -->
    <a href="../residents/resident_portal.php" class="page__back">← Back to Home</a>
    <!-- Page Heading -->
    <h1 class="page__heading">Report an Incident</h1>
    <p class="page__subheading">
    Fill out the form below to report an incident to your barangay. All fields marked with * are required.
    </p>
    <!-- Incident Report Form -->
    <div class="form-card">
      <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="">
        <!-- Full Name -->
          <div class="form-row">
            <div class="form-group">
                <label for="firstName" class="label">First Name <span class="label__required">*</span></label>
                <input type="text" id="firstName" name="firstName" class="input" placeholder="Juan" required />
            </div>
            <div class="form-group">
                <label for="lastName" class="label">Last Name <span class="label__required">*</span></label>
                <input type="text" id="lastName" name="lastName" class="input" placeholder="Dela Cruz" required />
            </div>
          </div>
        <!-- Contact Number -->
        <div class="form-group">
          <label for="contactNumber" class="label">
            Contact Number <span class="label__required">*</span>
          </label>
          <input type="tel" id="contactNumber" name="contactNumber" class="input" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" title="Enter 11 digits starting with 09" required />
        </div>
        <!-- Incident Location -->
        <div class="form-group">
          <label for="location" class="label">
            Incident Location <span class="label__required">*</span>
          </label>
          <input type="text" id="location" name="location" class="input" placeholder="Street, Purok, or Landmark" required />
        </div>
        <!-- Incident Type -->
        <div class="form-group">
          <label for="incidentType" class="label">
            Incident Type <span class="label__required">*</span>
          </label>
          <select id="incidentType" name="incidentType" class="select" required>
            <option value="" disabled selected>Select incident type</option>
            <option value="noise">Noise Complaint</option>
            <option value="theft">Theft / Robbery</option>
            <option value="assault">Assault / Physical Harm</option>
            <option value="fire">Fire Incident</option>
            <option value="flood">Flooding</option>
            <option value="vandalism">Vandalism</option>
            <option value="domestic">Domestic Dispute</option>
            <option value="other">Other</option>
          </select>
        </div>
        <!-- Description -->
        <div class="form-group">
          <label for="description" class="label">
            Description <span class="label__required">*</span>
          </label>
          <textarea id="description" name="description" class="textarea" placeholder="Provide a detailed description of the incident..." required></textarea>
        </div>
        <!-- Upload Evidence -->
        <div class="form-group">
          <label for="evidence" class="label">Upload Evidence (optional)</label>
          <input type="file" id="evidence" name="evidence" class="file-input" accept="image/*,video/*,.pdf" />
        </div>
        <!-- Submit Button -->
        <button type="submit" class="btn btn--submit">Submit Incident Report</button>
      </form>
    </div>
    <p class="privacy-note">By submitting you agree to our <a href="#">Privacy Policy</a>. Personal data is processed for incident handling only.</p>
    <script src="../assets/js/app.js" defer></script>
  </main>
  <!-- ===== Footer ===== -->
  <footer class="footer">
    <div class="footer__inner">
      <div>
        <div class="footer__brand">
            <div class="logoandname">
                <div class="footer__logo">🏛️</div>
                <span class="footer__name">Barangay Online Services</span>
            </div>
          <p class="footer__text">Your trusted digital gateway for barangay services. Faster, easier, and more accessible for every resident.</p>
        </div>
      </div>
      <div>
        <h3 class="footer__section-title">Contact</h3>
        <p class="footer__text">
          Barangay Hall, Main Street<br />
          City, Province 1234<br />
          Tel: (02) 8123-4567<br />
          Email: info@barangay.gov.ph
        </p>
      </div>
      <div>
        <h3 class="footer__section-title">Office Hours</h3>
        <p class="footer__text">
          Monday – Friday<br />
          8:00 AM – 5:00 PM<br />
          Saturday: 8:00 AM – 12:00 PM<br />
          Sunday: Closed
        </p>
      </div>
      <hr class="footer__divider" />
      <p class="footer__bottom">© 2025 Barangay Online Services. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>