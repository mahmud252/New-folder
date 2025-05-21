<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$files = getUserFiles($userId);
$currentPage = basename($_SERVER['PHP_SELF']);

// Calculate storage metrics
$totalFiles = count($files);
$totalSize = array_reduce($files, fn($carry, $file) => $carry + ($file['filesize'] ?? 0), 0);
$lastUpload = !empty($files) ? date('M d, Y', strtotime(end($files)['upload_date'] ?? '')) : 'Never';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Storage</title>
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
            --danger: #ff3d00;
            --success: #00c853;
            --warning: #ffab00;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            --gold-accent: #ffd700;
            --transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            --gradient: linear-gradient(135deg, #8a2be2 0%, #4a00e0 100%);
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

        .mobile-menu-btn {
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

        .mobile-menu-btn:hover {
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
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9InJnYmEoMTM4LDQzLDIyNiwwLjAzKSIgZmlsbC1ydWxlPSJub256ZXJvIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC0xLjggNC00IDQtNC0xLjgtNC00eiIvPjwvZz48L2c+PC9zdmc+');
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

        /* Storage Info Cards */
        .storage-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .storage-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .storage-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
            transition: height 0.5s ease;
        }

        .storage-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .storage-card:hover::before {
            height: 8px;
        }

        .storage-stat {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            font-size: 1.5rem;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(138, 43, 226, 0.1);
            color: var(--primary);
            transition: var(--transition);
        }

        .storage-card:hover .stat-icon {
            transform: scale(1.1);
            background: var(--gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
        }

        .stat-details p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--text-light);
            transition: var(--transition);
        }

        .storage-card:hover .stat-details p {
            color: var(--text-color);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 5px;
            background: var(--gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            transition: var(--transition);
        }

        .storage-card:hover .stat-value {
            transform: translateY(-3px);
        }

        /* Media Container */
        .media-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .media-item {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            transform: translateY(0);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .media-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(138, 43, 226, 0.05) 0%, transparent 100%);
            opacity: 0;
            transition: var(--transition);
        }

        .media-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .media-item:hover::before {
            opacity: 1;
        }

        .media-preview {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .media-preview img,
        .media-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: var(--transition);
        }

        .media-item:hover img,
        .media-item:hover video {
            transform: scale(1.08);
        }

        .file-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-light);
            background: rgba(138, 43, 226, 0.05);
            height: 100%;
            transition: var(--transition);
        }

        .media-item:hover .file-icon {
            background: rgba(138, 43, 226, 0.1);
            color: var(--primary);
        }

        .media-actions {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            border-top: 1px solid rgba(138, 43, 226, 0.1);
        }

        .media-info {
            overflow: hidden;
            flex-grow: 1;
        }

        .filename {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 5px;
            transition: var(--transition);
        }

        .media-item:hover .filename {
            color: var(--primary);
        }

        .filesize {
            font-size: 0.85rem;
            color: var(--text-light);
            transition: var(--transition);
        }

        .media-item:hover .filesize {
            color: var(--primary-light);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            border: none;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient);
            color: white;
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(138, 43, 226, 0.4);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 61, 0, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 61, 0, 0.4);
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 0.85rem;
            min-width: 36px;
            justify-content: center;
        }

        /* Empty State */
        .no-files {
            text-align: center;
            padding: 60px;
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            backdrop-filter: blur(5px);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .no-files:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--primary-light);
            margin-bottom: 25px;
            transition: var(--transition);
        }

        .no-files:hover .empty-icon {
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

        /* Modal for preview */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            overflow: auto;
            animation: fadeIn 0.3s;
            backdrop-filter: blur(5px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            display: block;
            margin: 60px auto;
            max-width: 90%;
            max-height: calc(100vh - 120px);
            animation: zoomIn 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-radius: 8px;
        }

        @keyframes zoomIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .close {
            position: absolute;
            top: 25px;
            right: 35px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        .close:hover {
            color: var(--danger);
            transform: rotate(90deg) scale(1.1);
        }

        /* Delete Confirmation Modal */
        .confirm-modal {
            display: none;
            position: fixed;
            z-index: 1002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            animation: fadeIn 0.3s;
        }

        .confirm-dialog {
            background: var(--card-bg);
            margin: 15% auto;
            padding: 30px;
            border-radius: 16px;
            max-width: 500px;
            box-shadow: var(--shadow);
            animation: slideDown 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .confirm-dialog h3 {
            margin-bottom: 20px;
            color: var(--text-color);
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .confirm-dialog h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--danger);
            border-radius: 3px;
        }

        .confirm-dialog p {
            margin-bottom: 25px;
            color: var(--text-light);
            line-height: 1.6;
        }

        .confirm-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        /* Loading spinner */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

            .storage-info {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }
            
            .media-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .user-name {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            .media-container {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .page-title h1 {
                font-size: 1.6rem;
            }
            
            .empty-icon {
                font-size: 3rem;
            }
            
            .empty-title {
                font-size: 1.3rem;
            }

            .confirm-dialog {
                margin: 20% 15px;
                width: calc(100% - 30px);
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
    <nav class="top-nav" id="topNav">
        <div class="nav-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
           
            </div>
        </div>
      
        <div class="nav-right">
            <div class="user-info">
                
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </div>
    </nav>

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
                    <i class="fas fa-database"></i>
                </div>
                <div class="page-title">
                    <h1>My Storage</h1>
                    <p>Manage your uploaded files and monitor storage usage</p>
                </div>
            </div>
            
            <div class="storage-info">
                <div class="storage-card animate__animated animate__fadeInUp">
                    <div class="storage-stat">
                        <div class="stat-icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="stat-details">
                            <p>Total Files</p>
                            <div class="stat-value"><?php echo $totalFiles; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="storage-card animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="storage-stat">
                        <div class="stat-icon">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div class="stat-details">
                            <p>Space Used</p>
                            <div class="stat-value">
                                <?php echo function_exists('formatFileSize') ? formatFileSize($totalSize) : $totalSize . ' bytes'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="storage-card animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="storage-stat">
                        <div class="stat-icon">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="stat-details">
                            <p>Last Upload</p>
                            <div class="stat-value"><?php echo $lastUpload; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="media-container">
                <?php if (empty($files)): ?>
                    <div class="no-files animate__animated animate__fadeIn">
                        <div class="empty-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3 class="empty-title">No Files Uploaded Yet</h3>
                        <p class="empty-text">You haven't uploaded any files yet. Get started by uploading your first file.</p>
                        <a href="files.php" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Files
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($files as $index => $file): ?>
                        <div class="media-item animate__animated animate__fadeInUp" style="animation-delay: <?php echo $index * 0.05; ?>s">
                            <div class="media-preview">
                                <?php if (strpos($file['filetype'] ?? '', 'image') !== false): ?>
                                    <img src="<?php echo htmlspecialchars($file['filepath']); ?>" 
                                         alt="<?php echo htmlspecialchars($file['filename']); ?>"
                                         onclick="openModal('<?php echo htmlspecialchars($file['filepath']); ?>')">
                                <?php elseif (strpos($file['filetype'] ?? '', 'video') !== false): ?>
                                    <video controls>
                                        <source src="<?php echo htmlspecialchars($file['filepath']); ?>" 
                                                type="<?php echo htmlspecialchars($file['filetype'] ?? 'video/mp4'); ?>">
                                        Your browser doesn't support videos
                                    </video>
                                <?php else: ?>
                                    <div class="file-icon">
                                        <?php
                                        $fileExt = pathinfo($file['filename'], PATHINFO_EXTENSION);
                                        $icon = 'fa-file';
                                        switch(strtolower($fileExt)) {
                                            case 'pdf': $icon = 'fa-file-pdf'; break;
                                            case 'doc':
                                            case 'docx': $icon = 'fa-file-word'; break;
                                            case 'xls':
                                            case 'xlsx': $icon = 'fa-file-excel'; break;
                                            case 'ppt':
                                            case 'pptx': $icon = 'fa-file-powerpoint'; break;
                                            case 'zip':
                                            case 'rar': $icon = 'fa-file-archive'; break;
                                            case 'mp3':
                                            case 'wav': $icon = 'fa-file-audio'; break;
                                            case 'txt': $icon = 'fa-file-alt'; break;
                                            case 'html':
                                            case 'css':
                                            case 'js': $icon = 'fa-file-code'; break;
                                        }
                                        ?>
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="media-actions">
                                <div class="media-info">
                                    <div class="filename" title="<?php echo htmlspecialchars($file['filename']); ?>">
                                        <?php echo htmlspecialchars($file['filename']); ?>
                                    </div>
                                    <div class="filesize">
                                        <?php echo function_exists('formatFileSize') ? formatFileSize($file['filesize'] ?? 0) : $file['filesize'] . ' bytes'; ?>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <a href="<?php echo htmlspecialchars($file['filepath']); ?>" 
                                       class="btn btn-primary btn-sm ripple"
                                       download="<?php echo htmlspecialchars($file['filename']); ?>">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm ripple" onclick="confirmDelete('<?php echo $file['id']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalPreview">
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="confirm-modal">
        <div class="confirm-dialog">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this file? This action cannot be undone and the file will be permanently removed from your storage.</p>
            <div class="confirm-buttons">
                <button onclick="closeDeleteModal()" class="btn btn-primary ripple">Cancel</button>
                <button id="confirmDeleteBtn" class="btn btn-danger ripple">
                    <span id="deleteBtnText">Delete</span>
                    <span id="deleteSpinner" class="loading" style="display: none;"></span>
                </button>
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
            const icons = ['fa-file', 'fa-hdd', 'fa-database', 'fa-cloud', 
                          'fa-save', 'fa-file-upload', 'fa-file-download'];
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
            
            // Add ripple effect to all buttons with ripple class
            document.querySelectorAll('.ripple').forEach(button => {
                button.addEventListener('click', function(e) {
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
            
            // Animate media items on scroll
            const animateOnScroll = function() {
                const mediaItems = document.querySelectorAll('.media-item:not(.animated)');
                
                mediaItems.forEach(item => {
                    const itemPosition = item.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;
                    
                    if (itemPosition < screenPosition) {
                        item.classList.add('animated');
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            // Initial check in case elements are already visible
            animateOnScroll();
        });
        
        // Preview modal functionality
        function openModal(src) {
            const modal = document.getElementById('previewModal');
            const modalImg = document.getElementById('modalPreview');
            modal.style.display = "block";
            modalImg.src = src;
            document.body.style.overflow = "hidden";
        }
        
        function closeModal() {
            document.getElementById('previewModal').style.display = "none";
            document.body.style.overflow = "auto";
        }
        
        // Close modal when clicking outside the image
        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Delete confirmation functionality
        let fileToDelete = null;
        
        function confirmDelete(fileId) {
            fileToDelete = fileId;
            document.getElementById('deleteModal').style.display = 'block';
            document.body.style.overflow = "hidden";
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = "auto";
            fileToDelete = null;
        }
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const btnText = document.getElementById('deleteBtnText');
            const spinner = document.getElementById('deleteSpinner');
            
            btnText.style.display = 'none';
            spinner.style.display = 'inline-block';
            
            // Simulate delete request (replace with actual AJAX call)
            setTimeout(() => {
                // Here you would make an AJAX call to delete the file
                // For example:
                /*
                fetch(`delete_file.php?id=${fileToDelete}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the file from the UI
                        document.querySelector(`.media-item [data-id="${fileToDelete}"]`).closest('.media-item').remove();
                        closeDeleteModal();
                        // Show success message
                        alert('File deleted successfully');
                    } else {
                        alert('Error deleting file');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting file');
                })
                .finally(() => {
                    btnText.style.display = 'inline';
                    spinner.style.display = 'none';
                });
                */
                
                // For demo purposes, we'll just close the modal
                btnText.style.display = 'inline';
                spinner.style.display = 'none';
                closeDeleteModal();
                alert('File delete functionality would be implemented here');
            }, 1000);
        });
    </script>
</body>
</html>