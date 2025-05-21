<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/functions.php';

$userId = $_SESSION['user_id'];
$history = getLoginHistory($userId);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Login History</title>
    <link rel="icon" href="../assets/logo1.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8a2be2;
            --primary-dark: #7b1fa2;
            --primary-light: #b388ff;
            --sidebar-bg: #1a1a2e;
            --sidebar-text: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-color: #2d3436;
            --text-light: #8d99ae;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            --gold-accent: #ffd700;
            --gold-dark: #ffab00;
            --success: #00c853;
            --warning: #ff3d00;
            --transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            --gradient: linear-gradient(135deg, #8a2be2 0%, #4a00e0 100%);
            --glass: rgba(255, 255, 255, 0.15);
        }

        /* Base Styles */
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: var(--text-color);
            min-height: 100vh;
            line-height: 1.6;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(138, 43, 226, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(74, 0, 224, 0.05) 0%, transparent 20%);
            overflow-x: hidden;
        }

        /* Floating Particles Background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: var(--primary-light);
            border-radius: 50%;
            animation: float 15s infinite linear;
            opacity: 0.6;
            filter: blur(1px);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg) scale(0.5);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100px) rotate(720deg) scale(1.2);
                opacity: 0;
            }
        }

        /* Navigation Bar */
        .top-nav {
            background: var(--gradient);
            color: white;
            padding: 0 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            height: 70px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .top-nav.scrolled {
            background: rgba(26, 26, 46, 0.9);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .nav-left, .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .toggle-sidebar {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .toggle-sidebar:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .brand-logo:hover {
            transform: translateY(-2px);
        }

        .brand-logo img {
            height: 30px;
            transition: var(--transition);
        }

        .brand-logo:hover img {
            transform: rotate(15deg);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
        }

        .user-info:hover {
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold-accent);
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }

        .user-info:hover .user-avatar {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(255, 215, 0, 0.4);
        }

        .user-name {
            font-weight: 500;
        }

        .mobile-menu-btn {
            display: none;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            height: calc(100vh - 70px);
            top: 70px;
            left: 0;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 999;
            overflow-y: auto;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255, 215, 0, 0.1) 0%, transparent 30%),
                radial-gradient(circle at 80% 70%, rgba(138, 43, 226, 0.1) 0%, transparent 30%);
        }

        .sidebar-header {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
            margin: 0;
        }

        .menu-item {
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            margin: 5px 15px;
            border-radius: 8px;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.8s ease;
        }

        .menu-item:hover::before {
            left: 100%;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            z-index: 1;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .menu-item i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .menu-item.active {
            background: rgba(0, 200, 83, 0.1);
            border-left: 3px solid var(--success);
        }

        .menu-item.active a {
            padding-left: 17px;
            color: var(--gold-accent);
        }

        .menu-item.active i {
            color: var(--gold-accent);
            transform: scale(1.1);
        }

        .menu-item:hover a {
            transform: translateX(8px);
            color: var(--gold-accent);
        }

        .menu-item:hover i {
            transform: scale(1.1);
            color: var(--gold-accent);
        }

        .menu-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.15);
            margin: 15px 20px;
        }

        /* Main Content */
        .dashboard-container {
            padding-top: 70px;
        }

        .main-content {
            margin-left: 280px;
            padding: 30px 40px;
            transition: margin 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Page Header */
        .page-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInDown 0.5s both;
        }

        .page-icon {
            background: var(--primary-light);
            color: var(--primary);
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 1.8rem;
            box-shadow: 0 5px 20px rgba(138, 43, 226, 0.2);
            transition: var(--transition);
        }

        .page-header:hover .page-icon {
            transform: translateY(-5px) rotate(10deg);
            box-shadow: 0 8px 25px rgba(138, 43, 226, 0.3);
        }

        .page-title h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            position: relative;
            display: inline-block;
        }

        .page-title h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--gold-accent);
            border-radius: 3px;
            animation: underlineExpand 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes underlineExpand {
            0% {
                width: 0;
                opacity: 0;
            }
            100% {
                width: 60px;
                opacity: 1;
            }
        }

        .page-title p {
            margin: 10px 0 0;
            color: var(--text-light);
            font-size: 1rem;
            max-width: 600px;
        }

        /* Table Container */
        .table-container {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: fadeInUp 0.5s both;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: var(--transition);
        }

        .table-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--primary-light);
        }

        th, td {
            padding: 18px 25px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        th {
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        tr {
            transition: var(--transition);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: rgba(138, 43, 226, 0.03);
        }

        .login-time {
            font-weight: 600;
            color: var(--text-color);
            transition: var(--transition);
        }

        tr:hover .login-time {
            color: var(--primary);
        }

        .login-date {
            display: block;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 5px;
            transition: var(--transition);
        }

        tr:hover .login-date {
            color: var(--primary-light);
        }

        .ip-address {
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        tr:hover .ip-address {
            color: var(--primary-dark);
        }

        .device-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .device-icon {
            font-size: 1.4rem;
            color: var(--primary);
            transition: var(--transition);
        }

        tr:hover .device-icon {
            transform: scale(1.2);
            color: var(--gold-accent);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .status-success {
            background: rgba(0, 200, 83, 0.1);
            color: var(--success);
            box-shadow: 0 3px 10px rgba(0, 200, 83, 0.1);
        }

        .status-failed {
            background: rgba(255, 61, 0, 0.1);
            color: var(--warning);
            box-shadow: 0 3px 10px rgba(255, 61, 0, 0.1);
        }

        tr:hover .status-badge {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            transition: var(--transition);
        }

        .empty-state:hover {
            transform: translateY(-5px);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--primary-light);
            margin-bottom: 25px;
            transition: var(--transition);
        }

        .empty-state:hover .empty-icon {
            color: var(--primary);
            transform: scale(1.1);
        }

        .empty-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .empty-text {
            color: var(--text-light);
            max-width: 400px;
            margin: 0 auto;
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .sidebar {
                width: 260px;
            }
            .main-content {
                margin-left: 260px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 300px;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 25px;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            
            .page-icon {
                margin-right: 0;
            }
            
            th, td {
                padding: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .user-name {
                display: none;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .page-title h1 {
                font-size: 1.6rem;
            }
            
            th, td {
                padding: 12px;
                font-size: 0.85rem;
            }
            
            .empty-icon {
                font-size: 3rem;
            }
            
            .empty-title {
                font-size: 1.3rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(138, 43, 226, 0.3);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* Floating Icons */
        .floating-icons {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .floating-icon {
            position: absolute;
            opacity: 0.1;
            font-size: 24px;
            color: var(--primary);
            animation: floatIcon 20s linear infinite;
            user-select: none;
            filter: drop-shadow(0 5px 10px rgba(138, 43, 226, 0.2));
        }

        @keyframes floatIcon {
            0% {
                transform: translateY(100vh) rotate(0deg) scale(0.8);
                opacity: 0;
            }
            10% {
                opacity: 0.1;
            }
            90% {
                opacity: 0.1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg) scale(1.2);
                opacity: 0;
            }
        }

        /* Ripple Effect */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Particles Background -->
    <div class="particles" id="particles"></div>
    
    <!-- Floating Icons Background -->
    <div class="floating-icons" id="floatingIcons"></div>
    
    <!-- Top Navigation Bar -->
    <div class="top-nav" id="topNav">
        <div class="nav-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <div class="brand-logo">
                <img src="../assets/logo1.png" alt="Logo">
            </div>
        </div>
      
        <div class="nav-right">
            <div class="user-info">
                <img src="../assets/logo1.png" alt="User" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <img src="../assets/logo1.png" alt="User" class="user-avatar">
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    </div>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-item <?php echo $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $currentPage == 'files.php' ? 'active' : '' ?>">
                    <a href="files.php">
                        <i class="fas fa-file-upload"></i>
                        <span>My Files</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $currentPage == 'storage.php' ? 'active' : '' ?>">
                    <a href="storage.php">
                        <i class="fas fa-database"></i>
                        <span>Storage</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $currentPage == 'history.php' ? 'active' : '' ?>">
                    <a href="history.php">
                        <i class="fas fa-history"></i>
                        <span>Login History</span>
                    </a>
                </li>
                
                <li class="menu-divider"></li>
                
                <li class="menu-item">
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-header">
                <div class="page-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="page-title">
                    <h1>Login History</h1>
                    <p>Review your account access history and security events</p>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Login Time</th>
                            <th>IP Address</th>
                            <th>Device</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state animate__animated animate__fadeIn">
                                        <div class="empty-icon">
                                            <i class="fas fa-history"></i>
                                        </div>
                                        <h3 class="empty-title">No Login History Found</h3>
                                        <p class="empty-text">Your login history will appear here once you start using the system.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($history as $index => $entry): ?>
                                <tr class="animate__animated animate__fadeIn" style="animation-delay: <?php echo $index * 0.05; ?>s">
                                    <td>
                                        <span class="login-time"><?php echo date('h:i A', strtotime($entry['login_time'])); ?></span>
                                        <span class="login-date"><?php echo date('M d, Y', strtotime($entry['login_time'])); ?></span>
                                    </td>
                                    <td>
                                        <span class="ip-address"><?php echo htmlspecialchars($entry['ip_address']); ?></span>
                                    </td>
                                    <td>
                                      
                                              
                                    <div><?php echo strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false ? 'Mobile' : 'Desktop'; ?></div>

                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-success">Success</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const particleCount = 25;
            const particlesContainer = document.getElementById('particles');
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 5px and 15px
                const size = Math.random() * 10 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.top = `${Math.random() * 100}vh`;
                
                // Random animation duration and delay
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                
                // Random color variation
                const hue = 270 + Math.random() * 20 - 10; // Purple hue range
                particle.style.background = `hsl(${hue}, 80%, 70%)`;
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Create floating icons
        function createFloatingIcons() {
            const icons = ['fa-history', 'fa-user-shield', 'fa-clock', 'fa-lock', 
                          'fa-sign-in-alt', 'fa-user-check', 'fa-shield-alt'];
            const iconCount = 15;
            const floatingIcons = document.getElementById('floatingIcons');
            
            for (let i = 0; i < iconCount; i++) {
                const icon = document.createElement('i');
                const randomIcon = icons[Math.floor(Math.random() * icons.length)];
                icon.classList.add('fas', randomIcon, 'floating-icon');
                
                // Random position
                const left = Math.random() * 100;
                icon.style.left = `${left}%`;
                
                // Random size
                const size = Math.random() * 20 + 15;
                icon.style.fontSize = `${size}px`;
                
                // Random animation duration and delay
                const duration = Math.random() * 20 + 15;
                const delay = Math.random() * 10;
                icon.style.animationDuration = `${duration}s`;
                icon.style.animationDelay = `${delay}s`;
                
                // Random color variation
                const hue = 270 + Math.random() * 20 - 10; // Purple hue range
                icon.style.color = `hsl(${hue}, 80%, 70%)`;
                
                floatingIcons.appendChild(icon);
            }
        }
        
        // Toggle sidebar function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
            
            // Toggle menu button icon
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const icon = mobileBtn.querySelector('i');
            
            if (sidebar.classList.contains('show')) {
                icon.classList.replace('fa-bars', 'fa-times');
            } else {
                icon.classList.replace('fa-times', 'fa-bars');
            }
        }
        
        // Setup event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const topNav = document.getElementById('topNav');
            
            // Initialize background elements
            createParticles();
            createFloatingIcons();
            
            // Toggle sidebar when button is clicked
            mobileBtn.addEventListener('click', function(e) {
                toggleSidebar();
                
                // Create ripple effect
                const rect = e.target.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.classList.add('ripple-effect');
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnMobileBtn = event.target === mobileBtn || mobileBtn.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnMobileBtn && sidebar.classList.contains('show')) {
                        toggleSidebar();
                    }
                }
            });
            
            // Navbar effect on scroll
            window.addEventListener('scroll', function() {
                if (window.scrollY > 10) {
                    topNav.classList.add('scrolled');
                } else {
                    topNav.classList.remove('scrolled');
                }
            });
            
            // Adjust layout on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992 && sidebar.classList.contains('show')) {
                    toggleSidebar();
                }
            });
            
            // Add ripple effect to menu items
            document.querySelectorAll('.menu-item a').forEach(item => {
                item.addEventListener('click', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple-effect');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Animate table rows on scroll
            const animateOnScroll = function() {
                const tableRows = document.querySelectorAll('tbody tr:not(.animated)');
                
                tableRows.forEach(row => {
                    const rowPosition = row.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;
                    
                    if (rowPosition < screenPosition) {
                        row.classList.add('animated');
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            // Initial check in case elements are already visible
            animateOnScroll();
        });
    </script>
</body>
</html>