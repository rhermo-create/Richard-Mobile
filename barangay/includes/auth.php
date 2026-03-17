<?php
require_once 'config.php';

/**
 * Register a new user (resident only)
 */
function registerUser($pdo, $data) {
    $sql = "INSERT INTO users (email, password, role, first_name, last_name, middle_name, suffix, contact, address)
            VALUES (:email, :password, 'resident', :first_name, :last_name, :middle_name, :suffix, :contact, :address)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':email'      => $data['email'],
        ':password'   => password_hash($data['password'], PASSWORD_DEFAULT),
        ':first_name' => $data['first_name'],
        ':last_name'  => $data['last_name'],
        ':middle_name'=> $data['middle_name'] ?? null,
        ':suffix'     => $data['suffix'] ?? null,
        ':contact'    => $data['contact'],
        ':address'    => $data['address']
    ]);
}

/**
 * Log in a user (any role)
 */
function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        return true;
    }
    return false;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login – redirect to public login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /barangay/public/login.php');
        exit;
    }
}

/**
 * Require admin role – if not admin, redirect to resident portal
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: /barangay/residents/resident_portal.php');
        exit;
    }
}

/**
 * Logout
 */
function logout() {
    session_destroy();
    header('Location: /barangay/public/login.php');
    exit;
}
?>