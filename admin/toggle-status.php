<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    verifyCSRFToken($_POST['csrf_token']);

    $userId = intval($_POST['user_id']);
    $action = $_POST['user_action'];

    // Only allow valid actions
    if (!in_array($action, ['disable', 'activate'])) {
        $_SESSION['error'] = "Invalid action.";
        header("Location: admin_dashboard.php");
        exit;
    }

    $newStatus = $action === 'disable' ? 'inactive' : 'active';

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    if ($stmt->execute([$newStatus, $userId])) {
        $_SESSION['message'] = "User has been " . ($action === 'disable' ? "disabled" : "activated") . " successfully.";
    } else {
        $_SESSION['error'] = "Failed to update user status.";
    }

    header("Location: admin_dashboard.php"); // Change this to your admin page
    exit();
}
?>
