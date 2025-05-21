<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user data
$user = [];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    header("Location: users.php?error=User not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($username)) $errors[] = "Username is required";

    if (empty($errors)) {
        $update_sql = "UPDATE users SET name = ?, email = ?, username = ?, is_admin = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssii", $name, $email, $username, $is_admin, $user_id);
        
        if ($update_stmt->execute()) {
            header("Location: users.php?success=User updated successfully");
            exit();
        } else {
            $errors[] = "Error updating user: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5649c0;
            --primary-light: #a29bfe;
            --sidebar-bg: #2d3436;
            --sidebar-text: #ffffff;
            --card-bg: #ffffff;
            --text-color: #2d3436;
            --success: #00b894;
            --danger: #d63031;
            --warning: #fdcb6e;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', system-ui, sans-serif;
        }

        body {
            background-color: #f5f6fa;
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Floating particles animation */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: rgba(108, 92, 231, 0.2);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; }
        }

        /* Top Navigation Bar */
        .navbar {
            display: none;
            background: linear-gradient(135deg, var(--sidebar-bg) 0%, #1e272e 100%);
            color: white;
            padding: 15px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            transition: var(--transition);
        }

        .toggle-btn:hover {
            transform: scale(1.1);
            color: var(--primary-light);
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            padding-top: 60px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--sidebar-bg) 0%, #1e272e 100%);
            color: var(--sidebar-text);
            position: fixed;
            height: calc(100vh - 60px);
            overflow-y: auto;
            transition: var(--transition);
            z-index: 999;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            text-align: center;
            padding: 25px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-weight: 600;
            letter-spacing: 1px;
            background: rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0 15px;
        }

        .sidebar ul li {
            padding: 15px 20px;
            transition: var(--transition);
            border-radius: 8px;
            margin-bottom: 5px;
            position: relative;
            overflow: hidden;
        }

        .sidebar ul li::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: all 0.6s ease;
        }

        .sidebar ul li:hover::before {
            left: 100%;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--sidebar-text);
            display: flex;
            align-items: center;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .sidebar ul li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .sidebar ul li.active {
            background: rgba(108, 92, 231, 0.2);
            box-shadow: inset 4px 0 0 var(--primary);
        }

        .sidebar ul li.active a {
            color: var(--primary-light);
        }

        .sidebar ul li:hover {
            background: rgba(255,255,255,0.05);
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            flex-grow: 1;
            transition: var(--transition);
        }

        .main-content h1 {
            margin-bottom: 30px;
            color: var(--sidebar-bg);
            font-weight: 700;
            position: relative;
            display: inline-block;
        }

        .main-content h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        /* Form Container */
        .form-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 30px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
            outline: none;
        }

        /* Admin Toggle */
        .admin-toggle {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 25px 0;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: var(--transition);
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: var(--transition);
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--primary);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            gap: 8px;
        }

        .btn i {
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(108, 92, 231, 0.2);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: var(--text-color);
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-3px);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.5s ease;
            box-shadow: var(--shadow);
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert-danger {
            background: rgba(214, 48, 49, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert .close {
            cursor: pointer;
            font-size: 20px;
            transition: var(--transition);
        }

        .alert .close:hover {
            transform: scale(1.2);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .navbar {
                display: block;
            }
            .sidebar {
                transform: translateX(-280px);
                box-shadow: 5px 0 25px rgba(0, 0, 0, 0.2);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .form-container {
                padding: 15px;
            }
            
            .admin-toggle {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body oncontextmenu="return false;">
    <!-- Floating particles background -->
    <div class="particles" id="particles"></div>

    <!-- Top Navigation Bar (Mobile) -->
    <nav class="navbar">
        <div class="navbar-content">
            <button class="toggle-btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Edit User</h2>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> User Management</a></li>
                <li><a href="files.php"><i class="fas fa-file-upload"></i> File Management</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content" id="mainContent">
            <h1><i class="fas fa-user-edit"></i> Edit User</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <div>
                        <i class="fas fa-exclamation-circle"></i> 
                        <?php foreach ($errors as $error): ?>
                            <?php echo htmlspecialchars($error); ?><br>
                        <?php endforeach; ?>
                    </div>
                    <span class="close" onclick="this.parentElement.style.display='none'">&times;</span>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" id="editUserForm">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="password">password</label>
                        <input type="text" id="password" name="password" class="form-control" 
                               value="<?php echo htmlspecialchars($user['password']); ?>" required>
                    </div>

                    <div class="admin-toggle">
                        <label for="is_admin">Admin Privileges</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="is_admin" name="is_admin" 
                                   <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create floating particles
            function createParticles() {
                const particlesContainer = document.getElementById('particles');
                const particleCount = window.innerWidth < 768 ? 15 : 30;
                
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.classList.add('particle');
                    
                    // Random size between 5px and 15px
                    const size = Math.random() * 10 + 5;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    
                    // Random position
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.top = `${Math.random() * 100}%`;
                    
                    // Random animation duration between 10s and 20s
                    const duration = Math.random() * 10 + 10;
                    particle.style.animationDuration = `${duration}s`;
                    
                    // Random delay
                    particle.style.animationDelay = `${Math.random() * 5}s`;
                    
                    particlesContainer.appendChild(particle);
                }
            }

            createParticles();
            
            // Initial sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebarToggle.innerHTML = sidebar.classList.contains('show') ? 
                    '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggleBtn = event.target === sidebarToggle || 
                                              sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggleBtn) {
                        sidebar.classList.remove('show');
                        sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }
            });

            // Form submission with loading animation
            const editUserForm = document.getElementById('editUserForm');
            editUserForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitBtn.disabled = true;
            });

            // Add animation to admin toggle switch
            const adminToggle = document.getElementById('is_admin');
            adminToggle.addEventListener('change', function() {
                const slider = this.nextElementSibling;
                slider.style.transform = this.checked ? 'scale(1.05)' : 'scale(1)';
                setTimeout(() => {
                    slider.style.transform = '';
                }, 300);
            });
        });
    </script>
</body>
</html>