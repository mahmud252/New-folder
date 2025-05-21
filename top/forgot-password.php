<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 require_once '../includes/config.php';
    
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $_SESSION['error'] = "Invalid email address.";
        header("Location: forgot-password.php");
        exit;
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $_SESSION['error'] = "No account found with that email.";
        header("Location: forgot-password.php");
        exit;
    }
    $stmt->close();
    
    // Generate token and expiry (e.g., 1 hour)
    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));
    
    // Store token and expiry in DB
    $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
    $stmt->bind_param("sss", $token, $expires, $email);
    $stmt->execute();
    $stmt->close();
    
    // Send email with reset link
    $resetLink = "https://yourdomain.com/reset-password.php?token=$token";
    $subject = "Password Reset Request";
    $message = "Click the following link to reset your password: $resetLink\n\nThis link will expire in 1 hour.";
    $headers = "From: no-reply@yourdomain.com\r\n";
    
    if (mail($email, $subject, $message, $headers)) {
        $_SESSION['success'] = "Password reset link sent to your email.";
    } else {
        $_SESSION['error'] = "Failed to send email.";
    }
    header("Location: forgot-password.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Forgot Password</title></head>
<body>
    <h2>Forgot Password</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo "<p style='color:red'>".$_SESSION['error']."</p>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<p style='color:green'>".$_SESSION['success']."</p>";
        unset($_SESSION['success']);
    }
    ?>
    <form method="POST" action="forgot-password.php">
        <label>Email:</label>
        <input type="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>
