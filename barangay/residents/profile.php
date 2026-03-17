<?php
require_once '../includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $middleName = trim($_POST['middleName'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validate
    if (empty($firstName) || empty($lastName) || empty($email) || empty($contact) || empty($address)) {
        $error = 'Required fields cannot be empty.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, middle_name=?, suffix=?, email=?, contact=?, address=? WHERE id=?");
        $stmt->execute([$firstName, $lastName, $middleName, $suffix, $email, $contact, $address, $user_id]);
        $_SESSION['success'] = 'Profile updated.';
        header("Location: profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="My Profile — Barangay Online Services" />
  <title>My Profile — Barangay Online Services</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/sidebar.css" />

</head>
<body>

  <header class="header">
    <div class="header__inner">
      <a href="../residents/resident_portal.php" class="header__brand">
        <div class="header__logo">🏛️</div>
        <div>
          <div class="header__title">Barangay Online Services</div>
          <div class="header__subtitle">Resident Profile</div>
        </div>
      </a>
      <nav class="nav">
        <a href="../residents/resident_portal.php" class="nav__link">Home</a>
        <p> |</p>
        <h5>My Profile</h5>
      </nav>
    </div>
  </header>

  <div class="layout-with-sidebar">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar__header">
        <div class="sidebar__avatar">👤</div>
        <div>
          <h1 class="portal-welcome__name">Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
          <div class="sidebar__role">Resident</div>
        </div>
      </div>
      <nav class="sidebar__nav">
        <div class="sidebar__section-label">Main</div>
        <a href="../residents/resident_portal.php" class="sidebar__link">
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
        <a href="../residents/profile.php" class="sidebar__link sidebar__link--active">
          <span class="sidebar__icon">⚙️</span> Profile
        </a>
      </nav>
      <div class="sidebar__footer">
        <a href="../public/logout.php" class="sidebar__link"><span class="sidebar__icon">🚪</span> Logout</a>
      </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <main class="sidebar-main">
      <h1 class="page__heading">My Profile</h1>
      <p class="page__subheading">View and manage your profile information. Use Edit to update your details.</p>

      <div class="form-card">
        <form id="profileForm" action="profile.php" method="post">
          <input type="hidden" name="csrf_token" value="">
          <div id="formAlert" role="status" aria-live="polite" style="display:none;margin-bottom:0.75rem;padding:0.6rem;border-radius:8px;background:var(--success-color);color:white;font-weight:600;"></div>
          <div class="form-row">
            <div class="form-group">
              <label class="label" for="firstName">First Name</label>
              <input id="firstName" name="firstName" class="input" type="text" value="<?= htmlspecialchars($user['first_name']) ?>" readonly />
            </div>
            <div class="form-group">
              <label class="label" for="middleName">Middle Name</label>
              <input id="middleName" name="middleName" class="input" type="text" value="<?= htmlspecialchars($user['middle_name']) ?>" readonly />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="label" for="lastName">Last Name</label>
              <input id="lastName" name="lastName" class="input" type="text" value="<?= htmlspecialchars($user['last_name']) ?>" readonly />
            </div>
            <div class="form-group">
              <label class="label" for="suffix">Suffix</label>
              <input id="suffix" name="suffix" class="input" type="text" value="<?= htmlspecialchars($user['suffix']) ?> " readonly placeholder="Jr. / Sr. / III" />
            </div>
          </div>
          <div class="form-group">
            <label class="label" for="email">Email</label>
            <input id="email" name="email" class="input" type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly />
          </div>
          <div class="form-group">
            <label for="contactNumber" class="label">
            Contact Number <span class="label__required">*</span>
          </label>
          <input type="tel" id="contact" name="contact" class="input" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" title="Enter 11 digits starting with 09" value="<?= htmlspecialchars($user['contact']) ?>" required />
          </div>
          <div class="form-group">
            <label class="label" for="address">Address</label>
            <input id="address" name="address" class="input" type="text" value="<?= htmlspecialchars($user['address']) ?>" readonly />
          </div>
          <div class="form-actions" style="justify-content:flex-start;gap:0.5rem;">
            <button type="button" id="editBtn" class="btn btn--primary">Edit</button>
            <button type="submit" id="saveBtn" class="btn btn--submit" style="display:none;">Save</button>
            <button type="button" id="cancelBtn" class="btn btn--reset" style="display:none;">Cancel</button>
          </div>
        </form>
      </div>
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
    function toggleSidebar(){
      document.getElementById('sidebar').classList.toggle('sidebar--open');
      document.getElementById('sidebarOverlay').classList.toggle('sidebar-overlay--visible');
    }
  </script>

  <script>
    // Frontend-only profile edit toggle + simple validation + fake save
    (function(){
      const form = document.getElementById('profileForm');
      if(!form) return;
      const editBtn = document.getElementById('editBtn');
      const saveBtn = document.getElementById('saveBtn');
      const cancelBtn = document.getElementById('cancelBtn');
      const alertEl = document.getElementById('formAlert');
      const inputs = Array.from(form.querySelectorAll('.input'));
      // store original values for cancel
      let original = {};
      function snapshot(){
        original = {};
        inputs.forEach(i=> original[i.id] = i.value);
      }
      function setReadonly(v){
        inputs.forEach(i=> i.readOnly = v);
      }
      function showAlert(msg){
        alertEl.textContent = msg;
        alertEl.style.display = 'block';
        setTimeout(()=> alertEl.style.display = 'none', 2500);
      }
      // initialize
      snapshot();
      setReadonly(true);

      editBtn.addEventListener('click', function(){
        setReadonly(false);
        editBtn.style.display = 'none';
        saveBtn.style.display = '';
        cancelBtn.style.display = '';
        // focus first editable
        const first = form.querySelector('input:not([readonly])');
        if(first) first.focus();
      });

      cancelBtn.addEventListener('click', function(){
        // restore values
        inputs.forEach(i=> { if(original[i.id] !== undefined) i.value = original[i.id]; });
        setReadonly(true);
        editBtn.style.display = '';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
      });

      form.addEventListener('submit', function(e){
        const email = form.querySelector('#email');
        const contact = form.querySelector('#contact');

        // Email validation
        if(email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)){
          showAlert('Please enter a valid email address.');
          email.focus();
          e.preventDefault();
          return;
        }

        // Contact validation
        if(contact && contact.pattern && contact.value.trim().length > 0){
          const re = new RegExp(contact.getAttribute('pattern'));
          if(!re.test(contact.value.trim())){
            showAlert('Enter a valid contact number (09XXXXXXXXX).');
            contact.focus();
            e.preventDefault();
            return;
          }
        }
      });
    })();
  </script>

</body>
</html>
