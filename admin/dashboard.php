<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/functions.php';

$stats = getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="../assets/logo1.png" type="image/png">
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
            --info: #0984e3;
            --warning: #fdcb6e;
            --danger: #d63031;
            --gold: #f9ca24;
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
            padding-top: 60px; /* Space for navbar */
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

        .sidebar.collapsed {
            transform: translateX(-280px);
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

        .main-content.expanded {
            margin-left: 0;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: none;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.3),
                rgba(255, 255, 255, 0)
            );
            transform: rotate(30deg);
            transition: var(--transition);
        }

        .stat-card:hover::before {
            animation: shine 1.5s ease;
        }

        @keyframes shine {
            0% { transform: rotate(30deg) translate(-30%, -30%); }
            100% { transform: rotate(30deg) translate(30%, 30%); }
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            margin-bottom: 15px;
            color: var(--primary);
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-card p {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
            margin: 15px 0;
            position: relative;
            display: inline-block;
        }

        .stat-card p::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .stat-card i {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--primary);
            opacity: 0.8;
        }

        /* Specific card colors */
        .stat-card:nth-child(1) { border-top: 4px solid var(--primary); }
        .stat-card:nth-child(2) { border-top: 4px solid var(--success); }
        .stat-card:nth-child(3) { border-top: 4px solid var(--info); }
        .stat-card:nth-child(4) { border-top: 4px solid var(--warning); }
        .stat-card:nth-child(5) { border-top: 4px solid var(--danger); }

        .stat-card:nth-child(1) h3 { color: var(--primary); }
        .stat-card:nth-child(2) h3 { color: var(--success); }
        .stat-card:nth-child(3) h3 { color: var(--info); }
        .stat-card:nth-child(4) h3 { color: var(--warning); }
        .stat-card:nth-child(5) h3 { color: var(--danger); }

        .stat-card:nth-child(1) i { color: var(--primary); }
        .stat-card:nth-child(2) i { color: var(--success); }
        .stat-card:nth-child(3) i { color: var(--info); }
        .stat-card:nth-child(4) i { color: var(--warning); }
        .stat-card:nth-child(5) i { color: var(--danger); }

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
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 15px;
            }
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
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles background -->
    <div class="particles" id="particles"></div>

    <!-- Top Navigation Bar (Mobile) -->
    <nav class="navbar">
        <div class="navbar-content">
            <button class="toggle-btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Admin Dashboard</h2>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> User Management</a></li>
                <li><a href="files.php"><i class="fas fa-file-upload"></i> All Files</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> User Search</a></li>
                <li><a href="user_panel.php"><i class="fas fa-cog"></i> Control Panel</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content" id="mainContent">
            <h1>Admin Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Users</h3>
                    <p><?php echo $stats['total_users']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <h3>Active Users</h3>
                    <p><?php echo $stats['active_users']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-file-alt"></i>
                    <h3>Total Files</h3>
                    <p><?php echo $stats['total_files']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-image"></i>
                    <h3>Images</h3>
                    <p><?php echo $stats['total_images']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-video"></i>
                    <h3>Videos</h3>
                    <p><?php echo $stats['total_videos']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
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
            
            // Toggle sidebar visibility with animation
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
            
            // Highlight current page in sidebar with animation
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.sidebar ul li a');
            
            menuItems.forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    item.parentElement.classList.add('active');
                } else {
                    item.parentElement.classList.remove('active');
                }
                
                // Add click animation
                item.addEventListener('click', function(e) {
                    if (!item.parentElement.classList.contains('active')) {
                        menuItems.forEach(i => i.parentElement.classList.remove('active'));
                        item.parentElement.classList.add('active');
                        
                        // Close sidebar on mobile after selection
                        if (window.innerWidth <= 992) {
                            setTimeout(() => {
                                sidebar.classList.remove('show');
                                sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                            }, 300);
                        }
                    }
                });
            });
            
            // Adjust layout on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('show');
                    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
            
            // Add hover effect to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>