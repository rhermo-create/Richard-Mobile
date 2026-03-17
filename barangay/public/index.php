<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Barangay Online Services - Report incidents and request documents from your barangay." />
  <title>Barangay Online Services</title>
  <link rel="stylesheet" href="../assets/css/style.css" />

</head>
<body>
  <!-- ===== Header ===== -->
  <header class="header">
    <div class="header__inner">
      <a href="../public/index.php" class="header__brand">
        <div class="header__logo">🏛️</div>
        <div>
          <div class="header__title">Barangay Online Services</div>
          <div class="header__subtitle">Municipal Government Portal</div>
        </div>
      </a>
      <nav class="nav" aria-label="Main Navigation">
        <a href="../public/index.php" class="nav__link nav__link--active">Home</a>
        <a href="../public/login.php" class="nav__link">Login</a>
        <a href="../public/register.php" class="nav__link">Create Account</a>
      </nav>
    </div>
  </header>
  <main>
    <!-- ===== Hero Section ===== -->
    <section class="hero">
      <div class="hero__inner">
        <h1 class="hero__heading">Barangay Online Services</h1>
        <p class="hero__desc">
          Access essential barangay services from the comfort of your home. Report incidents and request documents quickly, easily, and securely.
        </p>
        <a href="../public/login.php" class="btn btn--primary">Get Started →</a>
      </div>
    </section>
    <!-- ===== Feature Cards ===== -->
    <section class="features" aria-label="Available Services">
      <div class="features__grid">
        <!-- Report Incident Card -->
        <a href="../public/login.php" class="feature-card">
          <div class="feature-card__icon feature-card__icon--incident">🚨</div>
          <h2 class="feature-card__title">Report Incident</h2>
          <p class="feature-card__desc">
            Report emergencies, disturbances, or community incidents directly to your barangay officials for swift action and response.
          </p>
          <span class="feature-card__action">File a Report <span aria-hidden="true">→</span></span>
        </a>
        <!-- Request Document Card -->
        <a href="../public/login.php" class="feature-card">
          <div class="feature-card__icon feature-card__icon--document">📄</div>
          <h2 class="feature-card__title">Request Document</h2>
          <p class="feature-card__desc">
            Request barangay clearance, certificate of residency, certificate of indigency, and other essential documents online.
          </p>
          <span class="feature-card__action">Request Now <span aria-hidden="true">→</span></span>
        </a>
      </div>
    </section>
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
  <script src="../assets/js/app.js" defer></script>
</body>
</html>