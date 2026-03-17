<?php
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $incident_id = $_POST['id'];
    $user_id = $_SESSION['user_id'];

    // Optional: verify the incident belongs to this user
    $stmt = $pdo->prepare("SELECT id FROM incidents WHERE id = ? AND user_id = ?");
    $stmt->execute([$incident_id, $user_id]);
    if ($stmt->fetch()) {
        // Delete the incident
        $stmt = $pdo->prepare("DELETE FROM incidents WHERE id = ?");
        $stmt->execute([$incident_id]);
        $_SESSION['success'] = 'Incident deleted successfully.';
    } else {
        $_SESSION['error'] = 'You do not have permission to delete this incident.';
    }
}
header('Location: resident_portal.php');
exit;