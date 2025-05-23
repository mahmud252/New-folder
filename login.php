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

    <link rel="stylesheet" href="assetsstyle.css">
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
 <style>
:root {
    --primary-color: #4361ee;
    --primary-hover: #3a56d4;
    --error-color: #e63946;
    --text-color: #2b2d42;
    --text-light: #8d99ae;
    --bg-color: #f8f9fa;
    --card-bg: #ffffff;
    --border-color: #e9ecef;
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    line-height: 1.6;
    color: var(--text-color);
    background-color: var(--bg-color);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 1rem;
}

/* Login Container */
.login-container {
    background-color: var(--card-bg);
    border-radius: 16px;
    box-shadow: var(--shadow);
    width: 100%;
    max-width: 420px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
}

.login-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 8px;
    background: linear-gradient(90deg, var(--primary-color), #4cc9f0);
}

.login-container h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
    color: var(--primary-color);
}

/* Alert Messages */
.alert {
    padding: 0.8rem 1rem;
    margin-bottom: 1.5rem;
    border-radius: 8px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.alert.error {
    background-color: rgba(230, 57, 70, 0.1);
    color: var(--error-color);
    border-left: 4px solid var(--error-color);
}

.alert i {
    margin-right: 0.5rem;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
    position: relative;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.form-control {
    width: 100%;
    padding: 0.8rem 1rem;
    font-size: 1rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background-color: var(--bg-color);
    transition: var(--transition);
    padding-left: 2.5rem;
}

.form-control:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
}

.form-icon {
    position: absolute;
    left: 1rem;
    top: 2.5rem;
    color: var(--text-light);
}

/* Remember Me & Forgot Password */
.login-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.remember-me {
    display: flex;
    align-items: center;
}

.remember-me input {
    margin-right: 0.5rem;
    accent-color: var(--primary-color);
}

.forgot-password a {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
}

.forgot-password a:hover {
    text-decoration: underline;
}

/* Submit Button */
.btn {
    width: 100%;
    padding: 0.9rem;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
}

.btn:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
}

/* Divider */
.divider {
    display: flex;
    align-items: center;
    margin: 1.5rem 0;
    color: var(--text-light);
    font-size: 0.9rem;
}

.divider::before, .divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid var(--border-color);
}

.divider::before {
    margin-right: 1rem;
}

.divider::after {
    margin-left: 1rem;
}

/* Social Login */
.social-login {
    margin-bottom: 1.5rem;
}

.social-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.social-btn {
    padding: 0.7rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: white;
    cursor: pointer;
    transition: var(--transition);
}

.social-btn:hover {
    background-color: var(--bg-color);
}

.social-btn i {
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

.social-btn.google i {
    color: #DB4437;
}

.social-btn.github i {
    color: #333;
}

/* Registration Link */
.register-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.95rem;
}

.register-link a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.register-link a:hover {
    text-decoration: underline;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .login-container {
        padding: 1.5rem;
    }
    
    .social-buttons {
        grid-template-columns: 1fr;
    }
    
    .login-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .forgot-password {
        width: 100%;
        text-align: right;
    }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    :root {
        --text-color: #f8f9fa;
        --text-light: #adb5bd;
        --bg-color: #212529;
        --card-bg: #2b2d42;
        --border-color: #495057;
    }
    
    .form-control {
        background-color: #343a40;
        color: white;
    }
    
    .social-btn {
        background-color: #343a40;
        border-color: #495057;
    }
    
    .social-btn:hover {
        background-color: #495057;
    }
}

/* Accessibility */
button:focus, input:focus, a:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Loading state */
.btn.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn.loading::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
.password-toggle {
    position: absolute;
    right: 1rem;
    top: 2.5rem; /* same vertical alignment as the input */
    cursor: pointer;
    color: var(--text-light);
    user-select: none;
    transition: color 0.3s ease;
}
.password-toggle:hover {
    color: var(--primary-color);
}
.cookie-consent {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 420px;
    background-color: var(--card-bg, #fff);
    color: var(--text-color, #333);
    border: 1px solid var(--border-color, #ccc);
    box-shadow: var(--shadow, 0 4px 6px rgba(0, 0, 0, 0.1));
    border-radius: 12px;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    font-size: 0.9rem;
    z-index: 1000;
    opacity: 1;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.cookie-consent p {
    flex: 1;
    margin: 0;
    line-height: 1.3;
}

.cookie-consent button#acceptCookies {
    background-color: var(--primary-color, #4361ee);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.cookie-consent button#acceptCookies:hover,
.cookie-consent button#acceptCookies:focus {
    background-color: var(--primary-hover, #3a56d4);
    outline: none;
}

@media (max-width: 480px) {
    .cookie-consent {
        bottom: 10px;
        max-width: 90vw;
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .cookie-consent button#acceptCookies {
        align-self: flex-end;
    }
}

/* Optional: To hide the banner smoothly when accepted */
.cookie-consent.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}


   
   .cookie-consent.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}
</style>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
  const cookieConsent = document.getElementById('cookieConsent');
  const acceptBtn = document.getElementById('acceptCookies');

  // Check if user already accepted cookies
  if (localStorage.getItem('cookieAccepted') === 'true') {
    cookieConsent.style.display = 'none';
  }

  acceptBtn.addEventListener('click', () => {
    localStorage.setItem('cookieAccepted', 'true');
    // Hide the banner with a fade effect
    cookieConsent.classList.add('hidden');
    // Optional: remove from DOM after animation (assuming 300ms fade)
    setTimeout(() => {
      cookieConsent.style.display = 'none';
    }, 300);
  });
});

    </script>
</body>
</html>
