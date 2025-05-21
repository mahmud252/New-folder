<?php
require_once 'config.php';
require_once 'functions.php';
 
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: ../user/dashboard.php");
        exit();
    }
}

// Login function
function login($username, $password) {
    global $conn;
    
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);
    
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // Record login history
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = $user['id'];
        $conn->query("INSERT INTO login_history (user_id, ip_address) VALUES ($userId, '$ip')");
        
        return true;
    }
    
    return false;
}

// Register function
function register($name, $email, $username, $password) {
    global $conn;
    
    $name = $conn->real_escape_string($name);
    $email = $conn->real_escape_string($email);
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);
    
    // Check if username or email already exists
    $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if ($check->num_rows > 0) {
        return false;
    }
    
    $sql = "INSERT INTO users (name, email, username, password) VALUES ('$name', '$email', '$username', '$password')";
    return $conn->query($sql);
}

?>