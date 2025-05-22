<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
    exit();
}

$error = '';
$username = '';
$remember = false;

// Check for remember me cookie
if (isset($_COOKIE['remember_token'])) {
    require_once 'includes/database.php';
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare("SELECT user_id, username FROM remember_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([hash('sha256', $token)]);
    $tokenData = $stmt->fetch();

    if ($tokenData && login($tokenData['username'], '', true)) {
        header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
        exit();
    }

    // Clear invalid token
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Rate limiting
$loginAttempts = $_SESSION['login_attempts'] ?? 0;
$lastAttempt = $_SESSION['last_login_attempt'] ?? 0;
$timeSinceLastAttempt = time() - $lastAttempt;

if ($loginAttempts >= 5 && $timeSinceLastAttempt < 300) {
    $error = 'Too many login attempts. Try again in ' . (300 - $timeSinceLastAttempt) . ' seconds.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Both username and password are required.';
    } else {
        $_SESSION['login_attempts'] = $loginAttempts + 1;
        $_SESSION['last_login_attempt'] = time();

        if (login($username, $password)) {
            unset($_SESSION['login_attempts'], $_SESSION['last_login_attempt']);

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + 60 * 60 * 24 * 30;

                $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
                $stmt->execute([$_SESSION['user_id'], hash('sha256', $token), $expires]);

                setcookie('remember_token', $token, [
                    'expires' => $expires,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }

            header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - File Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Preload & preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preload" href="assets/css/main.css" as="style">
    <link rel="preload" href="assets/js/login.js" as="script">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style" crossorigin="anonymous">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <h1>Welcome Back</h1>

        <?php if ($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" id="loginForm" novalidate>
            <div class="form-group">
                <label for="username">Username or Email</label>
                <i class="fas fa-user form-icon"></i>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock form-icon"></i>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>

            <div class="login-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" <?= $remember ? 'checked' : '' ?>>
                    Remember me
                </label>
                <div class="forgot-password">
                    <a href="top/forgot-password.php">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="divider">Continue with</div>
            <div class="social-login">
                <button type="button" class="social-btn google"><i class="fab fa-google"></i> Google</button>
                <button type="button" class="social-btn github"><i class="fab fa-github"></i> GitHub</button>
            </div>

            <div class="register-link">
                Don't have an account? <a href="register.php">Create one</a>
            </div>
        </form>
    </div>

    <div class="cookie-consent" id="cookieConsent">
        <p>We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.</p>
        <button id="acceptCookies">Accept</button>
    </div>

    <noscript>
        <link rel="stylesheet" href="assets/css/main.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </noscript>
</body>
</html>
