<?php
require_once '../includes/auth.php';
requireAdmin(); // Only admins can access this page

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Basic validation
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($contact) || empty($address)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Insert new admin
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role, first_name, last_name, contact, address) VALUES (?, ?, 'admin', ?, ?, ?, ?)");
            if ($stmt->execute([$email, $hashed, $first_name, $last_name, $contact, $address])) {
                $success = 'Admin account created successfully.';
            } else {
                $error = 'Failed to create admin account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin — Barangay Online Services</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header__inner">
            <a href="admin_portal.php" class="header__brand">
                <div class="header__logo">🏛️</div>
                <div>
                    <div class="header__title">Barangay Online Services</div>
                    <div class="header__subtitle">Admin Portal</div>
                </div>
            </a>
            <nav class="nav">
                <a href="admin_portal.php" class="nav__link">Home</a>
                <span>|</span>
                <span class="nav__current">Create Admin</span>
            </nav>
        </div>
    </header>

    <div class="layout-with-sidebar">
        <!-- Sidebar (same as other admin pages) -->
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
                <a href="admin_portal.php" class="sidebar__link"><span class="sidebar__icon">📊</span> Dashboard</a>
                <div class="sidebar__section-label">Management</div>
                <a href="incidents_admin.php" class="sidebar__link"><span class="sidebar__icon">🚨</span> Incident Reports</a>
                <a href="documents_admin.php" class="sidebar__link"><span class="sidebar__icon">📄</span> Document Requests</a>
                <a href="residents_admin.php" class="sidebar__link"><span class="sidebar__icon">👥</span> Residents</a>
                <div class="sidebar__section-label">System Settings</div>
                <a href="../admin/create_admin.php" class="sidebar__link sidebar__link--active"><span class="sidebar__icon">➕</span> Create Admin</a>
                <a href="../admin/settings-barangay-info.php" class="sidebar__link"><span class="sidebar__icon">⚙️</span> Settings</a>
            </nav>
            <div class="sidebar__footer">
                <a href="../public/logout.php" class="sidebar__link">🚪 Logout</a>
            </div>
        </aside>

        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <main class="sidebar-main">
            <div class="portal-welcome">
                <div class="portal-welcome__avatar">➕</div>
                <div>
                    <h1 class="portal-welcome__name">Create New Admin</h1>
                    <p class="portal-welcome__role">Add another administrator</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="form-card">
                <form method="post">
                    <div class="form-group">
                        <label class="label">First Name <span class="label__required">*</span></label>
                        <input type="text" name="first_name" class="input" required>
                    </div>
                    <div class="form-group">
                        <label class="label">Last Name <span class="label__required">*</span></label>
                        <input type="text" name="last_name" class="input" required>
                    </div>
                    <div class="form-group">
                        <label class="label">Email <span class="label__required">*</span></label>
                        <input type="email" name="email" class="input" required>
                    </div>
                    <div class="form-group">
                        <label class="label">Contact Number <span class="label__required">*</span></label>
                        <input type="tel" id="contactNumber" name="contact" class="input" placeholder="09XXXXXXXXX" pattern="^09\d{9}$" maxlength="11" title="Enter 11 digits starting with 09" required />
                    </div>
                    <div class="form-group">
                        <label class="label">Address <span class="label__required">*</span></label>
                        <input type="text" name="address" class="input" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="label">Password <span class="label__required">*</span></label>
                            <input type="password" name="password" class="input" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label class="label">Confirm Password <span class="label__required">*</span></label>
                            <input type="password" name="confirm_password" class="input" minlength="8" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn--submit">Create Admin</button>
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

    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar--open');
            document.querySelector('.sidebar-overlay').classList.toggle('sidebar-overlay--visible');
        }
    </script>
</body>
</html>