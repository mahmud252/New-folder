<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/functions.php';

$userId = $_SESSION['user_id'];
$files = getUserFiles($userId);
$totalFiles = count($files);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="icon" href="../assets/logo1.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
    --shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
    --gold-accent: #fdcb6e;
    --gold-dark: #e17055;
    --transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    --gradient: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', system-ui, sans-serif;
}

body {
    background-color: #f9f9f9;
    color: var(--text-color);
    overflow-x: hidden;
}

/* Navbar */
.navbar {
    display: none;
    position: fixed;
    top: 0;
    width: 100%;
    padding: 15px 20px;
    background: var(--gradient);
    color: #fff;
    z-index: 1000;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.navbar.scrolled {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
    background: var(--sidebar-bg);
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
    font-size: 22px;
    cursor: pointer;
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
}

.toggle-btn:hover {
    transform: scale(1.1);
    background: rgba(255, 255, 255, 0.3);
}

/* Layout */
.dashboard-container {
    display: flex;
    min-height: 100vh;
    padding-top: 60px;
}

.sidebar {
    width: 280px;
    position: fixed;
    top: 60px;
    left: 0;
    height: calc(100vh - 60px);
    background-color: var(--sidebar-bg);
    color: var(--sidebar-text);
    overflow-y: auto;
    z-index: 999;
    transition: transform var(--transition);
    box-shadow: 4px 0 30px rgba(0, 0, 0, 0.15);
    background-image: 
        radial-gradient(circle at 10% 20%, rgba(253, 203, 110, 0.1) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(108, 92, 231, 0.1) 0%, transparent 20%);
}

.sidebar h2 {
    text-align: center;
    padding: 25px 0;
    font-size: 22px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    position: relative;
    margin: 0 20px;
    font-weight: 600;
    letter-spacing: 1px;
    color: var(--gold-accent);
}

.sidebar h2::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 25%;
    width: 50%;
    height: 2px;
    background: var(--gold-accent);
    transform: scaleX(0);
    transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.sidebar:hover h2::after {
    transform: scaleX(1);
}

.sidebar ul {
    list-style: none;
    padding: 15px 0;
}

.sidebar ul li {
    position: relative;
    transition: all 0.3s ease;
    margin: 5px 15px;
    border-radius: 8px;
    overflow: hidden;
}

.sidebar ul li::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
    transition: left 0.8s ease;
}

.sidebar ul li:hover::before {
    left: 100%;
}

.sidebar ul li a {
    display: flex;
    align-items: center;
    padding: 16px 25px;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: var(--transition);
    position: relative;
    z-index: 1;
    font-weight: 500;
}

.sidebar ul li a i {
    margin-right: 15px;
    font-size: 18px;
    transition: var(--transition);
    width: 24px;
    text-align: center;
}

.sidebar ul li.active,
.sidebar ul li:hover {
    background-color: rgba(255, 255, 255, 0.08);
}

.sidebar ul li.active a,
.sidebar ul li:hover a {
    color: var(--gold-accent);
    transform: translateX(8px);
}

.sidebar ul li.active a i,
.sidebar ul li:hover a i {
    color: var(--gold-accent);
    transform: scale(1.2);
}

/* Main Content */
.main-content {
    flex-grow: 1;
    margin-left: 280px;
    padding: 40px 50px;
    transition: margin-left var(--transition);
    background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9InJnYmEoMTA4LDkyLDIzMSwwLjAzKSIgZmlsbC1ydWxlPSJub256ZXJvIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC0xLjggNC00IDQtNC0xLjgtNC00eiIvPjwvZz48L2c+PC9zdmc+');
}

.main-content h1 {
    font-size: 32px;
    margin-bottom: 40px;
    color: var(--primary-dark);
    position: relative;
    display: inline-block;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.main-content h1::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 0;
    width: 60px;
    height: 4px;
    background: var(--gold-accent);
    border-radius: 4px;
    animation: underlineExpand 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.stat-card {
    background: var(--card-bg);
    padding: 35px 30px;
    border-radius: 16px;
    box-shadow: var(--shadow);
    text-align: center;
    transition: var(--transition);
    border: none;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.85);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient);
    transition: all 0.8s ease;
}

.stat-card::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(108, 92, 231, 0.1), transparent 70%);
    transform: scale(0);
    transition: transform 0.8s ease;
}

.stat-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    background: rgba(255, 255, 255, 0.95);
}

.stat-card:hover::before {
    height: 8px;
}

.stat-card:hover::after {
    transform: scale(1);
}

.stat-card h3 {
    margin-bottom: 15px;
    font-size: 16px;
    text-transform: uppercase;
    color: var(--primary);
    letter-spacing: 1.5px;
    position: relative;
    display: inline-block;
    font-weight: 600;
}

.stat-card h3::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 2px;
    background: var(--gold-accent);
    transition: all 0.4s ease;
}

.stat-card:hover h3::after {
    width: 50px;
    background: var(--gold-dark);
}

.stat-card p {
    font-size: 42px;
    font-weight: bold;
    color: var(--text-color);
    margin-top: 20px;
    position: relative;
    font-weight: 700;
    background: var(--gradient);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.stat-card p::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 3px;
    background: var(--primary-light);
    border-radius: 3px;
    transition: all 0.4s ease;
}

.stat-card:hover p::after {
    width: 60px;
    background: var(--gold-accent);
}

/* Floating Icons Animation */
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
    opacity: 0.08;
    font-size: 24px;
    color: var(--primary);
    animation: float 15s linear infinite;
    user-select: none;
}

@keyframes float {
    0% {
        transform: translateY(100vh) rotate(0deg) scale(0.8);
        opacity: 0;
    }
    10% {
        opacity: 0.08;
    }
    90% {
        opacity: 0.08;
    }
    100% {
        transform: translateY(-100px) rotate(360deg) scale(1.2);
        opacity: 0;
    }
}

/* Responsive */
@media (max-width: 992px) {
    .navbar {
        display: block;
    }

    .sidebar {
        transform: translateX(-100%);
        box-shadow: none;
    }

    .sidebar.show {
        transform: translateX(0);
        box-shadow: 4px 0 30px rgba(0, 0, 0, 0.2);
    }

    .main-content {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 30px 25px;
    }

    .stat-card {
        padding: 30px 25px;
    }

    .stat-card p {
        font-size: 36px;
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .main-content {
        padding: 25px 20px;
    }

    .sidebar {
        width: 260px;
    }
    
    .main-content h1 {
        font-size: 28px;
    }
}

/* Additional Animations */
@keyframes fadeInUpDelayed {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-delay-1 {
    animation-delay: 0.3s !important;
}

.animate-delay-2 {
    animation-delay: 0.6s !important;
}

.glow-text {
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from {
        text-shadow: 0 0 5px rgba(253, 203, 110, 0.5);
    }
    to {
        text-shadow: 0 0 10px rgba(253, 203, 110, 0.8), 0 0 15px rgba(253, 203, 110, 0.5);
    }
}
</style>
</head>
<body oncontextmenu="return false;">
    <!-- Floating Icons Background -->
    <div class="floating-icons" id="floatingIcons"></div>
    
    <!-- Top Navigation Bar (Mobile) -->
    <nav class="navbar" id="navbar">
        <div class="navbar-content">
            <button class="toggle-btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h2>User Dashboard</h2>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <h2>User Panel</h2>
            <ul>
                <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="files.php"><i class="fas fa-file-upload"></i> My Uploaded Files</a></li>
                <li><a href="storage.php"><i class="fas fa-database"></i> My Storage</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> My Login History</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main-content" id="mainContent">
            <h1 class="animate__animated animate__fadeIn">Welcome, <span class="glow-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h1>
            <div class="stats-grid">
                <div class="stat-card animate__animated animate__fadeInUp animate-delay-1">
                    <h3>Total Uploaded Files</h3>
                    <p><?php echo $totalFiles; ?></p>
                </div>
                <div class="stat-card animate__animated animate__fadeInUp animate-delay-2">
                    <h3>Storage Used</h3>
                    <p class="stat-value">
                            <?php 
                            $totalSize = 0;
                            foreach ($files as $file) {
                                $totalSize += $file['filesize'] ?? 0;
                            }
                            echo function_exists('formatFileSize') ? formatFileSize($totalSize) : $totalSize . ' bytes';
                            ?>
                        </p>

                        
                </div>
                
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const navbar = document.getElementById('navbar');
            const floatingIcons = document.getElementById('floatingIcons');
            
            // Create floating icons
            function createFloatingIcons() {
                const icons = ['fa-file', 'fa-database', 'fa-cloud', 'fa-upload', 'fa-download', 
                              'fa-folder', 'fa-image', 'fa-music', 'fa-video', 'fa-code'];
                const iconCount = 15;
                
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
                    const duration = Math.random() * 15 + 10;
                    const delay = Math.random() * 5;
                    icon.style.animationDuration = `${duration}s`;
                    icon.style.animationDelay = `${delay}s`;
                    
                    // Random color variation
                    const hue = 250 + Math.random() * 20 - 10; // Purple hue range
                    const saturation = 70 + Math.random() * 20;
                    const lightness = 60 + Math.random() * 20;
                    icon.style.color = `hsl(${hue}, ${saturation}%, ${lightness}%)`;
                    
                    floatingIcons.appendChild(icon);
                }
            }
            
            // Toggle sidebar visibility
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                this.classList.toggle('active');
                
                // Add animation class to sidebar
                if (sidebar.classList.contains('show')) {
                    sidebar.style.animation = 'none';
                    setTimeout(() => {
                        sidebar.style.animation = 'slideInLeft 0.5s ease-out';
                    }, 10);
                }
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggleBtn = event.target === sidebarToggle || 
                                              sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggleBtn) {
                        sidebar.classList.remove('show');
                    }
                }
            });
            
            // Highlight current page in sidebar
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.sidebar ul li a');
            
            menuItems.forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    item.parentElement.classList.add('active');
                } else {
                    item.parentElement.classList.remove('active');
                }
            });
            
            // Navbar effect on scroll
            window.addEventListener('scroll', function() {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                // Parallax effect for floating icons
                const scrollPosition = window.scrollY;
                floatingIcons.style.transform = `translateY(${scrollPosition * 0.2}px)`;
            });
            
            // Adjust layout on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('show');
                }
            });
            
            // Initialize floating icons
            createFloatingIcons();
            
            // Add scroll animations
            const animateOnScroll = function() {
                const statCards = document.querySelectorAll('.stat-card');
                
                statCards.forEach((card, index) => {
                    const cardPosition = card.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (cardPosition < screenPosition) {
                        card.style.animation = `fadeInUpDelayed 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) ${index * 0.2}s forwards`;
                        card.style.opacity = '0'; // Reset for animation
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            // Initial check in case elements are already visible
            animateOnScroll();
            
            // Add ripple effect to buttons
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple-effect');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 800);
                });
            });
        });
    </script>
</body>
</html>