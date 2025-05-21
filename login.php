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

// Check for existing remember me cookie
if (isset($_COOKIE['remember_token'])) {
    require_once 'includes/database.php';
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare("SELECT user_id, username FROM remember_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch();
    
    if ($tokenData) {
        if (login($tokenData['username'], '', true)) {
            header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
            exit();
        }
    }
    // Clear invalid cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Check for login attempts and implement rate limiting
if (isset($_SESSION['login_attempts'])) {
    $lastAttempt = $_SESSION['last_login_attempt'] ?? 0;
    $timeSinceLastAttempt = time() - $lastAttempt;
    
    if ($_SESSION['login_attempts'] >= 3) {
        if ($timeSinceLastAttempt < 30) {
            $error = 'Too many login attempts. Please wait ' . (30 - $timeSinceLastAttempt) . ' seconds.';
        } else {
            // Reset attempts if time window has passed
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_login_attempt']);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    // Sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Basic validation
    if (empty($username) || empty($password)) {
        $error = 'Both username and password are required';
    } else {
        // Track login attempts
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        $_SESSION['login_attempts']++;
        $_SESSION['last_login_attempt'] = time();
        
        if (login($username, $password)) {
            // Reset attempt counter on successful login
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_login_attempt']);
            
            // Handle "Remember Me" functionality
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + 60 * 60 * 24 * 30; // 30 days
                
       
                $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
                $stmt->execute([$_SESSION['user_id'], $token, $expires]);
                
                setcookie('remember_token', $token, [
                    'expires' => $expires,
                    'path' => '/',
                    'domain' => '',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }
            
            // Redirect to appropriate dashboard
            header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - File Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <div class="login-container">
        <h1>Welcome Back</h1>
        
        <?php if ($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="post" id="loginForm" novalidate>
            <div class="form-group">
                <label for="username">Username or Email</label>
                <i class="fas fa-user form-icon"></i>
                <input type="text" id="username" name="username" class="form-control"
                       value="<?php echo htmlspecialchars($username); ?>" required
                       autocomplete="username" autocapitalize="off" autocorrect="off" spellcheck="false">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock form-icon"></i>
                <input type="password" id="password" name="password" class="form-control" required
                       autocomplete="current-password">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>
            
            <div class="login-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" <?php echo $remember ? 'checked' : ''; ?>>
                    <label for="remember">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </button>
            
            <div class="divider">Continue with</div>
            
            <div class="social-login">
                <div class="social-buttons">
                    <button type="button" class="social-btn google">
                        <i class="fab fa-google"></i>
                        <span>Google</span>
                    </button>
                    <button type="button" class="social-btn github">
                        <i class="fab fa-github"></i>
                        <span>GitHub</span>
                    </button>
                </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const cookieConsent = document.getElementById('cookieConsent');
            const acceptCookiesBtn = document.getElementById('acceptCookies');
            
            // Check if cookies are accepted
            if (!localStorage.getItem('cookiesAccepted')) {
                setTimeout(() => {
                    cookieConsent.classList.add('show');
                }, 1000);
            }
            
            // Accept cookies
            acceptCookiesBtn.addEventListener('click', function() {
                localStorage.setItem('cookiesAccepted', 'true');
                cookieConsent.classList.remove('show');
            });
            
            // Auto-focus username field
            document.getElementById('username').focus();
            
            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Client-side validation
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                
                if (!username || !password) {
                    e.preventDefault();
                    showError('Please fill in both username and password fields');
                    return false;
                }
                
                // Show loading state
                loginBtn.classList.add('loading');
                loginBtn.querySelector('span').textContent = 'Logging in...';
            });
            
            // Check for password managers that might autofill
            setTimeout(() => {
                if (document.getElementById('password').value) {
                    document.getElementById('remember').checked = true;
                }
            }, 300);
            
            // Social login handlers (placeholder)
            document.querySelector('.social-btn.google').addEventListener('click', function() {
                window.location.href = 'auth/google.php';
            });
            
            document.querySelector('.social-btn.github').addEventListener('click', function() {
                window.location.href = 'auth/github.php';
            });
            
            // Error animation
            function showError(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert error';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i><span>${message}</span>`;
                
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.replaceWith(errorDiv);
                } else {
                    loginForm.insertBefore(errorDiv, loginForm.firstChild);
                }
                
                // Add animation
                errorDiv.style.animation = 'none';
                void errorDiv.offsetWidth; // Trigger reflow
                errorDiv.style.animation = 'slideInDown 0.3s ease-out';
            }
            
            // Add pulse animation on focus
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.animation = 'pulse 0.5s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.animation = '';
                });
            });
        });
    </script>
</body>
</html>