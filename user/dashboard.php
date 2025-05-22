<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/functions.php';

$userId = $_SESSION['user_id'];
$files = getUserFiles($userId);
$totalFiles = count($files);

// Calculate storage used
$totalSize = 0;
foreach ($files as $file) {
    $totalSize += $file['filesize'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | <?php echo htmlspecialchars($_SESSION['username']); ?></title>
    <link rel="icon" href="../assets/logo1.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
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
    --success: #00b894;
    --warning: #f39c12;
    --danger: #e74c3c;
    --transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    --gradient: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
    --sidebar-width: 280px;
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
    line-height: 1.6;
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
    backdrop-filter: blur(5px);
}

.navbar.scrolled {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
    background: rgba(45, 52, 54, 0.95);
}

.navbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
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

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: var(--gold-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sidebar-bg);
    font-weight: bold;
    text-transform: uppercase;
}

/* Layout */
.dashboard-container {
    display: flex;
    min-height: 100vh;
    padding-top: 60px;
}

.sidebar {
    width: var(--sidebar-width);
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
    margin-left: var(--sidebar-width);
    padding: 40px 50px;
    transition: margin-left var(--transition);
    background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9InJnYmEoMTA4LDkyLDIzMSwwLjAzKSIgZmlsbC1ydWxlPSJub256ZXJvIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC0xLjggNC00IDQtNC0xLjgtNC00eiIvPjwvZz48L2c+PC9zdmc+');
    min-height: calc(100vh - 60px);
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

.welcome-message {
    margin-bottom: 30px;
    font-size: 18px;
    color: var(--text-color);
    opacity: 0.9;
    max-width: 800px;
    line-height: 1.7;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 30px;
    margin-top: 30px;
    margin-bottom: 40px;
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
    cursor: pointer;
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

.stat-card .stat-description {
    font-size: 14px;
    margin-top: 20px;
    color: var(--text-color);
    opacity: 0.8;
    font-weight: 400;
    transition: var(--transition);
}

.stat-card:hover .stat-description {
    opacity: 1;
    transform: translateY(5px);
}

/* Recent Activity Section */
.recent-activity {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 16px;
    box-shadow: var(--shadow);
    margin-top: 40px;
    transition: var(--transition);
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.85);
}

.recent-activity h2 {
    font-size: 24px;
    margin-bottom: 25px;
    color: var(--primary-dark);
    position: relative;
    padding-bottom: 10px;
}

.recent-activity h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--gradient);
    border-radius: 3px;
}

.activity-list {
    list-style: none;
}

.activity-item {
    padding: 15px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    transition: var(--transition);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    transform: translateX(10px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.activity-content {
    flex-grow: 1;
}

.activity-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-color);
}

.activity-time {
    font-size: 13px;
    color: var(--text-color);
    opacity: 0.7;
}

/* Progress Bar */
.progress-container {
    width: 100%;
    height: 8px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    margin-top: 10px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--gradient);
    border-radius: 4px;
    transition: width 1.5s ease;
    position: relative;
}

.progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: progressShine 2s infinite;
}

@keyframes progressShine {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Storage Info */
.storage-info {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
    font-size: 13px;
    color: var(--text-color);
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

/* Tooltip */
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltip-text {
    visibility: hidden;
    width: 200px;
    background-color: var(--sidebar-bg);
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 10px;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 14px;
    font-weight: normal;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.tooltip .tooltip-text::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: var(--sidebar-bg) transparent transparent transparent;
}

.tooltip:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
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
    
    .recent-activity {
        padding: 25px 20px;
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
    
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-icon {
        margin-bottom: 10px;
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

.animate-delay-3 {
    animation-delay: 0.9s !important;
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

/* Ripple Effect */
.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.7);
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

/* Dark Mode Toggle */
.dark-mode-toggle {
    position: fixed;
    bottom: 30px;
    left: 30px;
    z-index: 1000;
    background: var(--gradient);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.dark-mode-toggle:hover {
    transform: scale(1.1);
}

.dark-mode-toggle i {
    color: white;
    font-size: 20px;
}
/* Default (Light Mode) */
body {
    background: #f7f7f7;
    color: #222;
    transition: background 0.3s, color 0.3s;
}

.dark-mode {
    background: #1a1a1a;
    color: #f0f0f0;
}

.dark-mode a { color: #ddd; }
.dark-mode .floating-icon { opacity: 0.7; }

/* Optional: Invert dark icons if needed */
.dark-mode .fas,
.dark-mode .floating-icon {
    filter: brightness(1.2);
}

/* Toggle button style */
#darkModeToggle {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    color: inherit;
    margin-left: auto;
}

</style>
</head>
<body oncontextmenu="return false;">
    <div class="floating-icons" id="floatingIcons"></div>
    <nav class="navbar" id="navbar">
        <div class="navbar-content">
            <button class="toggle-btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Dashboard</h2>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
            </div>
        </div>
    </nav>
    <div class="dashboard-container">
        <div class="sidebar" id="sidebar">
            <h2>User Panel</h2>
            <ul>
                <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="files.php"><i class="fas fa-file-upload"></i> My Files</a></li>
                <li><a href="storage.php"><i class="fas fa-database"></i> Storage</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> Activity</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <div class="sidebar-footer">
                <p>© <?php echo date('Y'); ?> Your Company</p>
                <p>v1.0.5</p>
            </div>
        </div>
        <div class="main-content" id="mainContent">
            <h1 class="animate__animated animate__fadeIn">Welcome back, <span class="glow-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h1>
            <div class="welcome-message animate__animated animate__fadeIn">
                <?php if(date('H') < 12): ?>Good morning! ☀️
                <?php elseif(date('H') < 18): ?>Good afternoon! 🌤️
                <?php else: ?>Good evening! 🌙
                <?php endif; ?>
                Here's what's happening with your account today.
            </div>
            <button id="darkModeToggle" title="Toggle dark mode"><i class="fas fa-moon"></i></button>
            <div class="stats-grid">
                <div class="stat-card" onclick="window.location.href='files.php'">
                    <h3>Total Files</h3>
                    <p><?php echo $totalFiles; ?></p>
                    <div class="stat-description">You've uploaded <?php echo $totalFiles; ?> files in total</div>
                </div>
                <div class="stat-card" onclick="window.location.href='storage.php'">
                    <h3>Storage Used</h3>
                    <p><?php echo formatFileSize($totalSize); ?></p>
                    <div class="stat-description">
                        <?php 
                        $percentage = ($totalSize / (100 * 1024 * 1024)) * 100;
                        echo round($percentage, 1); ?>% of your storage used
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?php echo min($percentage, 100); ?>%"></div>
                    </div>
                    <div class="storage-info">
                        <span><?php echo formatFileSize($totalSize); ?> used</span>
                        <span><?php echo formatFileSize(max(0, (100 * 1024 * 1024) - $totalSize)); ?> free</span>
                    </div>
                </div>
                <div class="stat-card" onclick="window.location.href='history.php'">
                    <h3>Last Active</h3>
                    <p class="tooltip" title="Soon...">Coming soon</p>
                    <div class="stat-description">Your most recent login activity</div>
                </div>
            </div>
            <div class="recent-activity animate__animated animate__fadeIn">
                <h2>Recent Activity</h2>
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon"><i class="fas fa-file-upload"></i></div>
                        <div class="activity-content">
                            <div class="activity-title">File Uploaded</div>
                            <div class="activity-time">Just now</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon"><i class="fas fa-sign-in-alt"></i></div>
                        <div class="activity-content">
                            <div class="activity-title">Logged in</div>
                            <div class="activity-time">Coming soon</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon"><i class="fas fa-user-edit"></i></div>
                        <div class="activity-content">
                            <div class="activity-title">Profile updated</div>
                            <div class="activity-time">2 days ago</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const floatingIcons = document.getElementById('floatingIcons');
    const darkModeToggle = document.getElementById('darkModeToggle');
    const icon = darkModeToggle.querySelector('i');

    // Floating icons
    const icons = [
        'fa-file', 'fa-database', 'fa-cloud', 'fa-upload', 'fa-download',
        'fa-folder', 'fa-image', 'fa-music', 'fa-video', 'fa-code'
    ];
    for (let i = 0; i < 15; i++) {
        const iconElem = document.createElement('i');
        iconElem.className = `fas ${icons[Math.floor(Math.random() * icons.length)]} floating-icon`;
        iconElem.style.left = `${Math.random() * 100}%`;
        iconElem.style.fontSize = `${Math.random() * 20 + 15}px`;
        iconElem.style.animationDuration = `${Math.random() * 15 + 10}s`;
        iconElem.style.animationDelay = `${Math.random() * 5}s`;
        iconElem.style.color = `hsl(${250 + Math.random() * 20 - 10}, ${70 + Math.random() * 20}%, ${60 + Math.random() * 20}%)`;
        floatingIcons.appendChild(iconElem);
    }

    // Sidebar toggle
    sidebarToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        sidebar.classList.toggle('show');
        this.querySelector('i').classList.toggle('fa-times');
        this.querySelector('i').classList.toggle('fa-bars');
    });

    document.addEventListener('click', function (event) {
        if (window.innerWidth <= 992 && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('show');
            sidebarToggle.querySelector('i').classList.remove('fa-times');
            sidebarToggle.querySelector('i').classList.add('fa-bars');
        }
    });

    // Dark mode toggle
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
    darkModeToggle.addEventListener('click', function () {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        icon.classList.toggle('fa-sun', isDark);
        icon.classList.toggle('fa-moon', !isDark);
    });
});
</script>
</body>
</html>