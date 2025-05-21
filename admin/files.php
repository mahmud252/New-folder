<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();

$files = getAllFiles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Uploaded Files</title>
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

        /* Stats Bar */
        .stats-bar {
            background: var(--card-bg);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Media Container */
        .media-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .media-item {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            position: relative;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.5s ease forwards;
        }

        .media-item:nth-child(1) { animation-delay: 0.1s; }
        .media-item:nth-child(2) { animation-delay: 0.2s; }
        .media-item:nth-child(3) { animation-delay: 0.3s; }
        .media-item:nth-child(4) { animation-delay: 0.4s; }
        .media-item:nth-child(5) { animation-delay: 0.5s; }
        .media-item:nth-child(n+6) { animation-delay: 0.6s; }

        .media-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary);
        }

        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .media-item img, .media-item video {
            width: 100%;
            height: 200px;
            object-fit: cover;
            cursor: pointer;
            background: #f0f0f0;
            transition: var(--transition);
        }

        .media-item:hover img, .media-item:hover video {
            transform: scale(1.02);
        }

        .media-item video {
            background: #000;
        }

        .media-info {
            padding: 20px;
        }

        .media-info p {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .media-info p strong {
            color: var(--primary);
        }

        .file-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .file-actions a {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .file-actions a:first-child {
            background: var(--primary);
            color: white;
        }

        .file-actions a:first-child:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .file-actions a.delete-btn {
            background: var(--danger);
            color: white;
        }

        .file-actions a.delete-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        /* No Files Message */
        .no-files {
            text-align: center;
            padding: 50px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            animation: fadeIn 0.5s ease;
        }

        .no-files p {
            font-size: 18px;
            color: var(--text-color);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
            animation: scaleUp 0.3s ease;
        }

        @keyframes scaleUp {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-content img, .modal-content video {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .modal-content video {
            width: 80vw;
            height: auto;
        }

        .modal-actions {
            position: absolute;
            bottom: -60px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .modal-actions a {
            padding: 12px 25px;
            background: var(--primary);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-actions a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .modal-actions a.delete-btn {
            background: var(--danger);
        }

        .close-modal {
            position: absolute;
            top: -50px;
            right: 0;
            color: white;
            font-size: 40px;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            transform: rotate(90deg);
            color: var(--primary-light);
        }

        /* Loading Animation */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 3000;
            align-items: center;
            justify-content: center;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .media-container {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

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
            .media-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .modal-content video {
                width: 90vw;
            }
            
            .modal-actions {
                bottom: -80px;
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
        }

        @media (max-width: 576px) {
            .media-container {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .stats-bar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles background -->
    <div class="particles" id="particles"></div>

    <!-- Loading spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>
    </div>

    <!-- Top Navigation Bar (Mobile) -->
    <nav class="navbar">
        <div class="navbar-content">
            <button class="toggle-btn" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h2>User Uploaded Files</h2>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> User Management</a></li>
                <li class="active"><a href="files.php"><i class="fas fa-file-upload"></i> All Files</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> User Search</a></li>
                <li><a href="user_panel.php"><i class="fas fa-cog"></i> Control Panel</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content" id="mainContent">
            <h1>All Uploaded Files</h1>
            
            <?php if (empty($files)): ?>
                <div class="no-files">
                    <p>No files have been uploaded yet.</p>
                </div>
            <?php else: ?>
                <div class="stats-bar">
                    <p>Total Files: <?php echo count($files); ?></p>
                    <div class="file-types">
                        <span><i class="fas fa-image" style="color: var(--primary);"></i> Images: <?php echo count(array_filter($files, fn($f) => $f['filetype'] == 'image')); ?></span>
                        <span><i class="fas fa-video" style="color: var(--info);"></i> Videos: <?php echo count(array_filter($files, fn($f) => $f['filetype'] == 'video')); ?></span>
                    </div>
                </div>
                
                <div class="media-container">
                    <?php foreach ($files as $file): ?>
                        <div class="media-item" data-id="<?php echo $file['id']; ?>">
                            <?php if ($file['filetype'] == 'image'): ?>
                                <img src="../user/<?php echo $file['filepath']; ?>" 
                                     alt="<?php echo htmlspecialchars($file['filename']); ?>"
                                     loading="lazy"
                                     data-src="../user/<?php echo $file['filepath']; ?>">
                            <?php else: ?>
                                <video preload="metadata" data-src="../user/<?php echo $file['filepath']; ?>">
                                    <source src="../user/<?php echo $file['filepath']; ?>" 
                                            type="video/<?php echo pathinfo($file['filename'], PATHINFO_EXTENSION); ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                            
                            <div class="media-info">
                                <p><strong><i class="fas fa-user"></i> User:</strong> <?php echo htmlspecialchars($file['username']); ?></p>
                                <p><strong><i class="fas fa-file"></i> Filename:</strong> <?php echo htmlspecialchars($file['filename']); ?></p>
                                <p><strong><i class="fas fa-calendar"></i> Uploaded:</strong> <?php echo date('M d, Y H:i', strtotime($file['uploaded_at'])); ?></p>
                                <div class="file-actions">
                                    <a href="../user/<?php echo $file['filepath']; ?>" download="<?php echo $file['filename']; ?>">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="delete_file.php?id=<?php echo $file['id']; ?>" 
                                       class="delete-btn" 
                                       onclick="return confirmDelete(event)">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal will be injected when needed -->
    <div id="modalPlaceholder"></div>

    <!-- Load critical JS immediately -->
    <script>
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

        // Initial sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
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

            // Confirm delete with loading animation
            function confirmDelete(event) {
                event.preventDefault();
                const deleteUrl = event.currentTarget.href;
                
                if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                    const loadingSpinner = document.getElementById('loadingSpinner');
                    loadingSpinner.style.display = 'flex';
                    
                    fetch(deleteUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Animate removal of the file card
                            const fileCard = event.target.closest('.media-item');
                            fileCard.style.animation = 'fadeOut 0.3s ease forwards';
                            
                            setTimeout(() => {
                                fileCard.remove();
                                loadingSpinner.style.display = 'none';
                                
                                // Show success notification
                                showNotification('File deleted successfully', 'success');
                            }, 300);
                        } else {
                            loadingSpinner.style.display = 'none';
                            showNotification('Error deleting file: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        loadingSpinner.style.display = 'none';
                        showNotification('Error deleting file', 'error');
                        console.error('Error:', error);
                    });
                }
                
                return false;
            }

            // Show notification function
            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                }, 10);
                
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }

            // Add notification styles dynamically
            const notificationStyles = document.createElement('style');
            notificationStyles.textContent = `
                .notification {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    padding: 15px 25px;
                    border-radius: 8px;
                    color: white;
                    font-weight: 500;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    transform: translateY(100px);
                    opacity: 0;
                    transition: all 0.3s ease;
                    z-index: 9999;
                }
                .notification.show {
                    transform: translateY(0);
                    opacity: 1;
                }
                .notification.success {
                    background: var(--success);
                }
                .notification.error {
                    background: var(--danger);
                }
                @keyframes fadeOut {
                    to { opacity: 0; transform: scale(0.9); }
                }
            `;
            document.head.appendChild(notificationStyles);
        });
    </script>

    <!-- Load non-critical JS after page load -->
    <script defer>
        document.addEventListener('DOMContentLoaded', function() {
            // Lazy load images that are in viewport
            const lazyLoadImages = () => {
                const images = document.querySelectorAll('.media-item img[loading="lazy"]');
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.onload = () => {
                                img.style.opacity = 1;
                            };
                            observer.unobserve(img);
                        }
                    });
                }, {
                    rootMargin: '100px 0px'
                });

                images.forEach(img => {
                    img.style.opacity = 0;
                    img.style.transition = 'opacity 0.5s ease';
                    observer.observe(img);
                });
            };

            // Load modal functionality only when needed
            const loadModalScript = () => {
                const modalHTML = `
                    <div class="modal" id="mediaModal">
                        <div class="modal-content">
                            <span class="close-modal" id="closeModal">&times;</span>
                            <div id="modalMediaContainer"></div>
                            <div class="modal-actions">
                                <a href="#" id="modalDownload" download><i class="fas fa-download"></i> Download</a>
                                <a href="#" id="modalDelete" class="delete-btn"><i class="fas fa-trash"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('modalPlaceholder').innerHTML = modalHTML;
                
                // Modal functionality
                const mediaItems = document.querySelectorAll('.media-item img, .media-item video');
                const mediaModal = document.getElementById('mediaModal');
                const closeModal = document.getElementById('closeModal');
                const modalMediaContainer = document.getElementById('modalMediaContainer');
                const modalDownload = document.getElementById('modalDownload');
                const modalDelete = document.getElementById('modalDelete');
                
                mediaItems.forEach(media => {
                    media.addEventListener('click', function() {
                        const mediaSrc = this.getAttribute('data-src') || this.getAttribute('src');
                        const mediaType = this.tagName.toLowerCase();
                        const parentItem = this.closest('.media-item');
                        const fileId = parentItem.getAttribute('data-id');
                        
                        modalMediaContainer.innerHTML = '';
                        if (mediaType === 'img') {
                            const img = document.createElement('img');
                            img.src = mediaSrc;
                            modalMediaContainer.appendChild(img);
                        } else {
                            const video = document.createElement('video');
                            video.controls = true;
                            video.autoplay = true;
                            const source = document.createElement('source');
                            source.src = mediaSrc;
                            source.type = this.querySelector('source').type;
                            video.appendChild(source);
                            modalMediaContainer.appendChild(video);
                        }
                        
                        modalDownload.setAttribute('href', mediaSrc);
                        modalDownload.setAttribute('download', mediaSrc.split('/').pop());
                        modalDelete.setAttribute('href', `delete_file.php?id=${fileId}`);
                        modalDelete.onclick = function(e) {
                            e.preventDefault();
                            if (confirm('Are you sure you want to delete this file?')) {
                                mediaModal.style.display = 'none';
                                document.body.style.overflow = 'auto';
                                const deleteEvent = new Event('click');
                                parentItem.querySelector('.delete-btn').dispatchEvent(deleteEvent);
                            }
                        };
                        
                        mediaModal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    });
                });
                
                closeModal.addEventListener('click', function() {
                    mediaModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
                
                mediaModal.addEventListener('click', function(e) {
                    if (e.target === mediaModal) {
                        mediaModal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
                
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        mediaModal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            };

            // Initialize lazy loading and modal when user interacts
            document.addEventListener('mousemove', function init() {
                lazyLoadImages();
                loadModalScript();
                document.removeEventListener('mousemove', init);
            }, { once: true });
        });
    </script>
</body>
</html>