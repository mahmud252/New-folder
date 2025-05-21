<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user data for confirmation
$user = [];
$sql = "SELECT id, name, email, username FROM users WHERE id = ?";
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
    // Check if confirmation was received
    if (isset($_POST['confirm_delete'])) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // First delete permissions
            $delete_perms = "DELETE FROM permissions WHERE user_id = ?";
            $stmt_perms = $conn->prepare($delete_perms);
            $stmt_perms->bind_param("i", $user_id);
            $stmt_perms->execute();
            
            // Then delete user
            $delete_user = "DELETE FROM users WHERE id = ?";
            $stmt_user = $conn->prepare($delete_user);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            
            // Commit transaction
            $conn->commit();
            
            header("Location: users.php?success=User deleted successfully");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            header("Location: users.php?error=Error deleting user: " . urlencode($e->getMessage()));
            exit();
        }
    } else {
        // User cancelled the deletion
        header("Location: users.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User</title>
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
            background: rgba(214, 48, 49, 0.2);
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
            background: var(--danger);
            border-radius: 2px;
        }

        /* Confirmation Container */
        .confirmation-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            animation: slideUp 0.5s ease;
            border-top: 4px solid var(--danger);
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .confirmation-icon {
            text-align: center;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .confirmation-icon i {
            font-size: 60px;
            color: var(--danger);
        }

        .confirmation-message {
            text-align: center;
            margin-bottom: 30px;
        }

        .confirmation-message h2 {
            color: var(--danger);
            margin-bottom: 15px;
        }

        .confirmation-message p {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .user-details {
            background: rgba(214, 48, 49, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid var(--danger);
        }

        .user-details p {
            margin-bottom: 8px;
        }

        .user-details p strong {
            color: var(--danger);
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

        .btn-danger {
            background: var(--danger);
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(214, 48, 49, 0.2);
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

        .confirmation-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
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
            .confirmation-container {
                padding: 20px;
            }
            
            .confirmation-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .confirmation-container {
                padding: 15px;
            }
            
            .confirmation-icon i {
                font-size: 50px;
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
            <h2>Delete User</h2>
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
            <h1><i class="fas fa-user-slash"></i> Delete User</h1>
            
            <div class="confirmation-container">
                <div class="confirmation-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                
                <div class="confirmation-message">
                    <h2>Are you sure?</h2>
                    <p>This action cannot be undone. This will permanently delete the user account and all associated data.</p>
                </div>
                
                <div class="user-details">
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                </div>
                
                <form method="POST">
                    <div class="confirmation-actions">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Permanently
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

            // Add loading animation to delete button
            const deleteForm = document.querySelector('form');
            deleteForm.addEventListener('submit', function(e) {
                const deleteBtn = this.querySelector('button[type="submit"]');
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                deleteBtn.disabled = true;
            });
        });
    </script>
</body>
</html>