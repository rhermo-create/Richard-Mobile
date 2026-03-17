<?php
require_once '../includes/auth.php';
requireLogin();

$docTypes = $pdo->query("SELECT name FROM document_types WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $contact = trim($_POST['contactNumber'] ?? '');
  $docType = $_POST['documentType'] ?? '';
  $purpose = trim($_POST['purpose'] ?? '');
  $claimDate = $_POST['claimDate'] ?? '';
  $user_id = $_SESSION['user_id'];

  $stmt = $pdo->prepare("INSERT INTO document_requests (user_id, first_name, last_name, address, contact, document_type, purpose, claim_date) VALUES (?,?,?,?,?,?,?,?)");
  $stmt->execute([$user_id, $firstName, $lastName, $address, $contact, $docType, $purpose, $claimDate]);

  $_SESSION['success'] = 'Document request submitted successfully.';
    header('Location: resident_portal.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Request a barangay document online – clearance, residency, or indigency certificates." />
  <title>Request Document – Barangay Online Services</title>
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
        <h5>Document Requests</h5>
      </nav>
    </div>
  </header>
  <!-- ===== Main Content ===== -->
  <main class="page">
    <!-- Back Navigation -->
    <a href="../residents/resident_portal.php" class="page__back">← Back to Home</a>
    <!-- Page Heading -->
    <h1 class="page__heading">Request a Document</h1>
    <p class="page__subheading">
      Fill out the form below to request a barangay document. All fields marked with * are required.
    </p>

    <!-- Display error/success messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

    <!-- Document Request Form -->
    <div class="form-card">
      <form action="" method="post">
        <input type="hidden" name="csrf_token" value="">
        <!-- Full Name -->
        <div class="form-group">
          <div class="form-group">
                <label for="firstName" class="label">First Name <span class="label__required">*</span></label>
                <input type="text" id="firstName" name="firstName" class="input" placeholder="Juan" required />
            </div>
            <div class="form-group">
                <label for="lastName" class="label">Last Name <span class="label__required">*</span></label>
                <input type="text" id="lastName" name="lastName" class="input" placeholder="Dela Cruz" required />
            </div>
        </div>
        <!-- Address -->
        <div class="form-group">
          <label for="address" class="label">
            Address <span class="label__required">*</span>
          </label>
          <input type="text" id="address" name="address" class="input" placeholder="House No., Street, Purok" required />
        </div>
        <!-- Contact Number -->
        <div class="form-group">
          <label for="contactNumber" class="label">
            Contact Number <span class="label__required">*</span>
          </label>
          <input type="tel" id="contactNumber" name="contactNumber" class="input" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" title="Enter 11 digits starting with 09" required />
        </div>
        <!-- Document Type -->
        <div class="form-group">
          <label for="documentType" class="label">
            Select Document Type <span class="label__required">*</span>
          </label>
          <select id="documentType" name="documentType" class="select" required>
            <option value="" disabled selected>Select document type</option>
            <option value="clearance">Barangay Clearance</option>
            <option value="residency">Certificate of Residency</option>
            <option value="indigency">Certificate of Indigency</option>
          </select>
        </div>
        <!-- Purpose -->
        <div class="form-group">
          <label for="purpose" class="label">
            Purpose <span class="label__required">*</span>
          </label>
          <textarea id="purpose" name="purpose" class="textarea" placeholder="State the purpose for requesting this document..." required></textarea>
        </div>
        <!-- Preferred Claim Date -->
        <div class="form-group">
          <label for="claimDate" class="label">
            Preferred Claim Date <span class="label__required">*</span>
          </label>
          <input type="date" id="claimDate" name="claimDate" class="input" required />
        </div>
        <!-- Submit Button -->
        <button type="submit" class="btn btn--submit">Submit Document Request</button>
      </form>
    </div>
    <p class="privacy-note">By submitting you agree to our <a href="#">Privacy Policy</a>. Personal data is processed for document issuance only.</p>
    <script src="assets/js/app.js" defer></script>
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