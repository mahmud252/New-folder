<?php
session_start();
require_once 'includes/config.php';

if (!isset($_GET['token'])) {
    die("No token provided.");
}

$token = $_GET['token'];

// Validate token
$stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    die("Invalid token.");
}
$stmt->bind_result($userId, $expires);
$stmt->fetch();
$stmt->close();

// Check if token expired
if (strtotime($expires) < time()) {
    die("Token expired.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if ($password !== $password_confirm) {
        $_SESSION['error'] = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
    } else {
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear token
        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
        $stmt->bind_param("si", $passwordHash, $userId);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success'] = "Password updated successfully.";
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
    <h2>Reset Password</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo "<p style='color:red'>".$_SESSION['error']."</p>";
        unset($_SESSION['error']);
    }
    ?>
    <form method="POST" action="">
        <label>New Password:</label><br>
        <input type="password" name="password" required><br>
        <label>Confirm New Password:</label><br>
        <input type="password" name="password_confirm" required><br>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
