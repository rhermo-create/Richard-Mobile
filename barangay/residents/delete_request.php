<?php
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $request_id = $_POST['id'];
    $user_id = $_SESSION['user_id'];

    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM document_requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$request_id, $user_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM document_requests WHERE id = ?");
        $stmt->execute([$request_id]);
        $_SESSION['success'] = 'Document request deleted.';
    } else {
        $_SESSION['error'] = 'Permission denied.';
    }
}
header('Location: resident_portal.php');
exit;