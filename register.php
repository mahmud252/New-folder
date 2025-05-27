<?php
// Start output buffering to improve performance
ob_start();

// Load configuration and authentication
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
    exit();
}

// Initialize variables
$error = '';
$success = false;
$formData = [
    'name' => '',
    'email' => '',
    'username' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs efficiently
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Store sanitized form data
    $formData = [
        'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
        'username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
    ];
    
    // Validate inputs with early returns for better performance
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Attempt registration
        if (register($name, $email, $username, $password)) {
            $success = true;
            $formData = []; // Clear form on success
        } else {
            $error = 'Registration failed. Username or email may already exist.';
        }
    }
}

// Start HTML output with optimized structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - File Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
 <style>
:root {
    --primary-color: #4361ee;
    --primary-hover: #3a56d4;
    --error-color: #e63946;
    --success-color: #4cc9f0;
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

/* Register Container */
.register-container {
    background-color: var(--card-bg);
    border-radius: 16px;
    box-shadow: var(--shadow);
    width: 100%;
    max-width: 450px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
}

.register-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 8px;
    background: linear-gradient(90deg, var(--primary-color), var(--success-color));
}

.register-container h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
    color: var(--primary-color);
}

/* Alerts */
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

.alert.success {
    background-color: rgba(76, 201, 240, 0.1);
    color: var(--success-color);
    border-left: 4px solid var(--success-color);
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

.input-wrapper {
    position: relative;
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
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    cursor: pointer;
    background: none;
    border: none;
    font-size: 1rem;
}

.toggle-password:hover {
    color: var(--primary-color);
}

/* Password Requirements */
.requirements {
    font-size: 0.8rem;
    color: var(--text-light);
    margin-top: 0.5rem;
    padding-left: 0.5rem;
}

.requirement {
    display: flex;
    align-items: center;
    margin-bottom: 0.3rem;
}

.requirement i {
    margin-right: 0.5rem;
    font-size: 0.7rem;
}

.requirement.valid {
    color: var(--success-color);
}

.requirement.invalid {
    color: var(--text-light);
}

.password-strength {
    height: 4px;
    background-color: #eee;
    border-radius: 2px;
    margin-top: 0.5rem;
    overflow: hidden;
}

.strength-meter {
    height: 100%;
    width: 0;
    transition: var(--transition);
}

.strength-0 { width: 20%; background-color: var(--error-color); }
.strength-1 { width: 40%; background-color: #ff6b6b; }
.strength-2 { width: 60%; background-color: #feca57; }
.strength-3 { width: 80%; background-color: #48dbfb; }
.strength-4 { width: 100%; background-color: var(--success-color); }

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
    margin-top: 1rem;
}

.btn:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
}

/* Login Link */
.login-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.95rem;
}

.login-link a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.login-link a:hover {
    text-decoration: underline;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .register-container {
        padding: 1.5rem;
    }
    
    .register-container h1 {
        font-size: 1.5rem;
    }
    
    .form-control {
        padding: 0.7rem 1rem 0.7rem 2.2rem;
    }
    
    .form-icon {
        left: 0.8rem;
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
    
    .requirements {
        color: var(--text-light);
    }
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
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Create Your Account</h1>
        
        <?php if ($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                Registration successful! You can now <a href="login.php">login</a>.
            </div>
        <?php else: ?>
            <form action="register.php" method="post" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user form-icon"></i>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?php echo $formData['name'] ?? ''; ?>" required
                               autocomplete="name">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope form-icon"></i>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?php echo $formData['email'] ?? ''; ?>" required
                               autocomplete="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-at form-icon"></i>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?php echo $formData['username'] ?? ''; ?>" required
                               minlength="4" autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" required
                               minlength="8" autocomplete="new-password">
                        <button type="button" class="toggle-password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter" id="strengthMeter"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                               autocomplete="new-password">
                        <button type="button" class="toggle-password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn" id="registerBtn">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                const isPassword = input.type === 'password';
                
                input.type = isPassword ? 'text' : 'password';
                icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
                this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });
        
        // Password strength meter
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthMeter = document.getElementById('strengthMeter');
                
                // Calculate strength (0-4)
                let strength = 0;
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                // Update meter
                strengthMeter.className = 'strength-meter strength-' + strength;
            });
        }
        
        // Form submission handler
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                } else {
                    const btn = document.getElementById('registerBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
                }
            });
        }
    });
    </script>
</body>
</html>
<?php
// Flush output buffer
ob_end_flush();
?>