<?php

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        $_SESSION['error'] = "Invalid email address.";
        header("Location: forgot-password.php");
        exit;
    }

    // Check if user exists
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

    // Generate token
    $token = bin2hex(random_bytes(16));
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Store token and expiry
    $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
    $stmt->bind_param("sss", $token, $expires, $email);
    $stmt->execute();
    $stmt->close();

    // Email details
    $resetLink = "https://yourdomain.com/reset-password.php?token=$token";
    $subject = "Password Reset Request";
    $message = "Hi,\n\nClick the link below to reset your password:\n$resetLink\n\nThis link will expire in 1 hour.\n\nIf you did not request this, you can ignore this email.";
    $headers = "From: no-reply@yourdomain.com\r\n";

    if (mail($email, $subject, $message, $headers)) {
        $_SESSION['success'] = "Password reset link sent to your email.";
    } else {
        $_SESSION['error'] = "Failed to send reset email. Please try again.";
    }

    header("Location: forgot-password.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #1d4ed8;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #2563eb;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST" action="forgot-password.php">
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" placeholder="you@example.com" required>
        <button type="submit">Send Reset Link</button>
    </form>
</div>
</body>
</html>
