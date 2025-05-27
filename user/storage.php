<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$files = getUserFiles($userId);
$currentPage = basename($_SERVER['PHP_SELF']);

// Utility fallback
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Storage metrics
$totalFiles = count($files);
$totalSize = array_reduce($files, fn($carry, $file) => $carry + ($file['filesize'] ?? 0), 0);
$lastUpload = !empty($files) ? date('M d, Y', strtotime(end($files)['upload_date'] ?? '')) : 'Never';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Storage</title>
  <link rel="icon" href="../assets/logo1.png" type="image/png"/>
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --primary-color: #4a90e2;
      --primary-dark: #357ABD;
      --danger-color: #e74c3c;
      --danger-dark: #c0392b;
      --success-color: #2ecc71;
      --warning-color: #f39c12;
      --dark-color: #1e1e2f;
      --sidebar-color: #2c2f48;
      --sidebar-hover: #3e4161;
      --text-light: #f8f9fa;
      --text-dark: #333;
      --text-muted: #777;
      --bg-light: #f4f7fa;
      --card-bg: #fff;
      --border-radius: 8px;
      --box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg-light);
      color: var(--text-dark);
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Floating elements */
    .particles, .floating-icons {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: -1;
    }

    .particle {
      position: absolute;
      background-color: var(--primary-color);
      border-radius: 50%;
      opacity: 0.6;
      animation: float linear infinite;
    }

    .floating-icon {
      position: absolute;
      opacity: 0.1;
      animation: float linear infinite;
    }

    @keyframes float {
      0% {
        transform: translateY(0) translateX(0) rotate(0deg);
      }
      50% {
        transform: translateY(-100px) translateX(50px) rotate(180deg);
      }
      100% {
        transform: translateY(0) translateX(100px) rotate(360deg);
      }
    }

    /* Navigation */
    .top-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 20px;
      background-color: var(--dark-color);
      color: var(--text-light);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      transition: var(--transition);
    }

    .top-nav.scrolled {
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .mobile-menu-btn {
      background: none;
      border: none;
      color: var(--text-light);
      font-size: 24px;
      cursor: pointer;
      padding: 5px;
      border-radius: 4px;
      transition: var(--transition);
    }

    .mobile-menu-btn:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    .user-info .user-name {
      font-weight: 500;
      font-size: 16px;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background-color: var(--sidebar-color);
      color: var(--text-light);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      padding-top: 60px;
      transition: transform 0.3s ease;
      z-index: 90;
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.show {
        transform: translateX(0);
      }
    }

    .sidebar-header {
      text-align: center;
      padding: 20px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .menu-item {
      padding: 12px 20px;
      display: flex;
      align-items: center;
      transition: var(--transition);
    }

    .menu-item a {
      color: var(--text-light);
      text-decoration: none;
      display: flex;
      align-items: center;
      width: 100%;
    }

    .menu-item i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }

    .menu-item:hover, .menu-item.active {
      background-color: var(--sidebar-hover);
    }

    .menu-divider {
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      margin: 10px 0;
    }

    /* Main Content */
    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      margin-left: 240px;
      padding: 30px;
      flex: 1;
      margin-top: 60px;
    }

    @media (max-width: 992px) {
      .main-content {
        margin-left: 0;
      }
    }

    /* Page Header */
    .page-header {
      display: flex;
      align-items: center;
      margin-bottom: 30px;
    }

    .page-icon {
      font-size: 36px;
      margin-right: 15px;
      color: var(--primary-color);
    }

    .page-title h1 {
      margin: 0;
      font-size: 28px;
      font-weight: 600;
    }

    .page-title p {
      color: var(--text-muted);
      margin-top: 5px;
    }

    /* Storage Info Cards */
    .storage-info {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 30px;
    }

    .storage-card {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      padding: 20px;
      flex: 1 1 30%;
      min-width: 240px;
      transition: var(--transition);
    }

    .storage-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
      font-size: 32px;
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    .stat-details p {
      margin: 0;
      color: var(--text-muted);
      font-size: 14px;
    }

    .stat-value {
      font-size: 22px;
      font-weight: 600;
      margin-top: 5px;
    }

    /* File Grid */
    .media-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 20px;
    }

    .media-item {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      padding: 15px;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      display: flex;
      flex-direction: column;
    }

    .media-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .media-preview {
      position: relative;
      overflow: hidden;
      border-radius: var(--border-radius);
      margin-bottom: 10px;
      cursor: pointer;
    }

    .media-preview img, 
    .media-preview video {
      width: 100%;
      height: auto;
      border-radius: var(--border-radius);
      display: block;
      transition: var(--transition);
    }

    .media-preview:hover img,
    .media-preview:hover video {
      transform: scale(1.05);
    }

    .file-icon {
      text-align: center;
      font-size: 40px;
      color: #ccc;
      padding: 20px 0;
    }

    .file-name {
      font-size: 14px;
      margin-top: 5px;
      color: var(--text-dark);
      word-break: break-word;
    }

    .media-info {
      margin-top: 10px;
      font-size: 14px;
      flex-grow: 1;
    }

    .media-info p {
      margin-bottom: 5px;
      display: flex;
      align-items: center;
    }

    .media-info i {
      margin-right: 8px;
      color: var(--text-muted);
      width: 16px;
    }

    .file-actions {
      margin-top: 15px;
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .btn {
      padding: 8px 12px;
      border: none;
      background-color: var(--primary-color);
      color: white;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
      transition: var(--transition);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 1;
    }

    .btn i {
      margin-right: 5px;
    }

    .btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
    }

    .btn-danger {
      background-color: var(--danger-color);
    }

    .btn-danger:hover {
      background-color: var(--danger-dark);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      margin-top: 40px;
      color: var(--text-muted);
      padding: 40px 20px;
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
    }

    .empty-state i {
      font-size: 60px;
      margin-bottom: 10px;
      color: #ddd;
    }

    .empty-state h2 {
      margin-bottom: 10px;
      font-weight: 500;
    }

    /* Modals */
    .modal,
    .confirm-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.8);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content,
    .confirm-dialog {
      background: var(--card-bg);
      padding: 20px;
      border-radius: var(--border-radius);
      max-width: 90%;
      max-height: 90%;
      overflow: auto;
      position: relative;
      box-shadow: 0 5px 30px rgba(0,0,0,0.3);
    }

    .modal-content {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 0;
      background: transparent;
    }

    .modal-content img,
    .modal-content video {
      max-width: 100%;
      max-height: 80vh;
      border-radius: var(--border-radius);
      box-shadow: 0 5px 30px rgba(0,0,0,0.5);
    }

    .close {
      position: absolute;
      right: 25px;
      top: 25px;
      font-size: 30px;
      color: white;
      background: rgba(0,0,0,0.5);
      border: none;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1001;
      transition: var(--transition);
    }

    .close:hover {
      background: rgba(0,0,0,0.8);
      transform: rotate(90deg);
    }

    /* Confirm Modal */
    .confirm-dialog {
      max-width: 500px;
      width: 90%;
    }

    .confirm-dialog h3 {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      color: var(--danger-color);
    }

    .confirm-dialog h3 i {
      margin-right: 10px;
    }

    .confirm-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn-primary {
      background-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
    }

    /* Loading spinner */
    .loading {
      border: 2px solid #f3f3f3;
      border-top: 2px solid var(--primary-color);
      border-radius: 50%;
      width: 14px;
      height: 14px;
      animation: spin 1s linear infinite;
      display: inline-block;
      margin-left: 5px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Ripple effect */
    .ripple-effect {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.7);
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

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .storage-info {
        flex-direction: column;
      }
      .media-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      }
    }

    /* Animation classes */
    .animate__animated {
      animation-duration: 0.5s;
    }
  </style>
</head>
<body>
  <!-- Floating Particles and Icons -->
  <div id="particles" class="particles"></div>
  <div id="floatingIcons" class="floating-icons"></div>

  <!-- Navigation -->
  <nav class="top-nav" id="topNav">
    <div class="nav-left">
      <button id="mobileMenuBtn" class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    </div>
    <div class="nav-right">
      <div class="user-info">
        <span class="user-name"><?= e($_SESSION['username']); ?></span>
      </div>
    </div>
  </nav>

  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="user-info">
          <img src="../assets/logo1.png" alt="User" class="user-avatar"/>
          <div class="user-name"><?= e($_SESSION['username']); ?></div>
        </div>
      </div>
      <ul class="sidebar-menu">
        <li class="menu-item <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
          <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        </li>
        <li class="menu-item <?= $currentPage == 'files.php' ? 'active' : '' ?>">
          <a href="files.php"><i class="fas fa-file-upload"></i> <span>My Files</span></a>
        </li>
        <li class="menu-item <?= $currentPage == 'storage.php' ? 'active' : '' ?>">
          <a href="storage.php"><i class="fas fa-database"></i> <span>Storage</span></a>
        </li>
        <li class="menu-item <?= $currentPage == 'history.php' ? 'active' : '' ?>">
          <a href="history.php"><i class="fas fa-history"></i> <span>Login History</span></a>
        </li>
        <li class="menu-divider"></li>
        <li class="menu-item">
          <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="page-header">
        <div class="page-icon"><i class="fas fa-database"></i></div>
        <div class="page-title">
          <h1>My Storage</h1>
          <p>Manage your uploaded files and monitor storage usage</p>
        </div>
      </div>

      <div class="storage-info">
        <div class="storage-card animate__animated animate__fadeInUp">
          <div class="storage-stat">
            <div class="stat-icon"><i class="fas fa-file"></i></div>
            <div class="stat-details">
              <p>Total Files</p>
              <div class="stat-value"><?= $totalFiles; ?></div>
            </div>
          </div>
        </div>
        <div class="storage-card animate__animated animate__fadeInUp animate__delay-1s">
          <div class="storage-stat">
            <div class="stat-icon"><i class="fas fa-hdd"></i></div>
            <div class="stat-details">
              <p>Space Used</p>
              <div class="stat-value"><?= function_exists('formatFileSize') ? formatFileSize($totalSize) : $totalSize . ' bytes'; ?></div>
            </div>
          </div>
        </div>
        <div class="storage-card animate__animated animate__fadeInUp animate__delay-2s">
          <div class="storage-stat">
            <div class="stat-icon"><i class="fas fa-upload"></i></div>
            <div class="stat-details">
              <p>Last Upload</p>
              <div class="stat-value"><?= $lastUpload; ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- File List Section -->
      <section aria-label="User files">
        <?php if (empty($files)): ?>
          <div class="empty-state animate__animated animate__fadeIn">
            <i class="fas fa-cloud-upload-alt"></i>
            <h2>No Files Uploaded Yet</h2>
            <p>Upload your first file to get started</p>
            <a href="files.php" class="btn"><i class="fas fa-upload"></i> Upload Files</a>
          </div>
        <?php else: ?>
          <div class="media-grid">
            <?php foreach ($files as $file):
              $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
              $fileId = (int)$file['id'];
              $filePath = e($file['filepath']);
              $fileName = e($file['filename']);
              $fileSize = function_exists('formatFileSize') ? formatFileSize($file['filesize']) : ($file['filesize'] . ' bytes');
              $uploadedAt = date('M d, Y H:i', strtotime($file['uploaded_at']));
            ?>
            <article class="media-item animate__animated" data-id="<?= $fileId ?>">
              <div class="media-preview" onclick="openModal('<?= $filePath ?>', '<?= $ext ?>')">
                <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                  <img src="<?= $filePath ?>" alt="<?= $fileName ?>" loading="lazy"/>
                <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                  <video>
                    <source src="<?= $filePath ?>" type="video/<?= $ext ?>">
                  </video>
                  <i class="fas fa-play-circle" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; text-shadow: 0 0 10px rgba(0,0,0,0.5);"></i>
                <?php else: ?>
                  <div class="file-icon">
                    <i class="fas fa-file-alt"></i>
                    <p class="file-name"><?= $fileName ?></p>
                  </div>
                <?php endif; ?>
              </div>
              <div class="media-info">
                <p><i class="fas fa-file"></i> <?= $fileName ?></p>
                <p><i class="fas fa-hdd"></i> <?= $fileSize ?></p>
                <p><i class="fas fa-calendar-alt"></i> <?= $uploadedAt ?></p>
              </div>
              <div class="file-actions">
                <a href="<?= $filePath ?>" download="<?= $fileName ?>" class="btn"><i class="fas fa-download"></i> Download</a>
                <button class="btn btn-danger" onclick="confirmDelete(<?= $fileId ?>)"><i class="fas fa-trash-alt"></i> Delete</button>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <!-- Preview Modal -->
  <div id="previewModal" class="modal" role="dialog" aria-modal="true">
    <button class="close" onclick="closeModal()">&times;</button>
    <div class="modal-content">
      <!-- Content will be inserted here by JavaScript -->
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="confirm-modal" role="dialog">
    <div class="confirm-dialog">
      <h3><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h3>
      <p>Are you sure you want to delete this file? This action cannot be undone.</p>
      <div class="confirm-buttons">
        <button onclick="closeDeleteModal()" class="btn btn-primary">Cancel</button>
        <button id="confirmDeleteBtn" class="btn btn-danger">
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
        particle.style.opacity = Math.random() * 0.4 + 0.2;
        
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
        const top = Math.random() * 100;
        icon.style.left = `${left}%`;
        icon.style.top = `${top}%`;
        
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
        icon.style.opacity = Math.random() * 0.2 + 0.1;
        
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
    
    // Initialize the page
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
        createRippleEffect(e, this);
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
      document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
          createRippleEffect(e, this);
        });
      });
      
      // Animate media items on scroll
      const animateOnScroll = function() {
        const mediaItems = document.querySelectorAll('.media-item:not(.animate__fadeIn)');
        
        mediaItems.forEach((item, index) => {
          const itemPosition = item.getBoundingClientRect().top;
          const screenPosition = window.innerHeight / 1.3;
          
          if (itemPosition < screenPosition) {
            // Stagger the animations
            setTimeout(() => {
              item.classList.add('animate__fadeIn');
            }, index * 100);
          }
        });
      };
      
      window.addEventListener('scroll', animateOnScroll);
      // Initial check in case elements are already visible
      animateOnScroll();
    });
    
    // Create ripple effect
    function createRippleEffect(event, element) {
      const rect = element.getBoundingClientRect();
      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;
      
      const ripple = document.createElement('span');
      ripple.classList.add('ripple-effect');
      ripple.style.left = `${x}px`;
      ripple.style.top = `${y}px`;
      element.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
    }
    
    // Preview modal functionality
    function openModal(src, ext) {
      const modal = document.getElementById('previewModal');
      const modalContent = modal.querySelector('.modal-content');
      
      // Clear previous content
      modalContent.innerHTML = '';
      
      // Create appropriate content based on file type
      if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(ext)) {
        const img = document.createElement('img');
        img.src = src;
        img.alt = 'Preview';
        modalContent.appendChild(img);
      } else if (['mp4', 'webm', 'ogg'].includes(ext)) {
        const video = document.createElement('video');
        video.controls = true;
        video.autoplay = true;
        
        const source = document.createElement('source');
        source.src = src;
        source.type = `video/${ext}`;
        video.appendChild(source);
        
        modalContent.appendChild(video);
      } else {
        // For other file types, show a download button
        const downloadBox = document.createElement('div');
        downloadBox.style.background = 'white';
        downloadBox.style.padding = '40px';
        downloadBox.style.borderRadius = '8px';
        downloadBox.style.textAlign = 'center';
        
        const fileIcon = document.createElement('i');
        fileIcon.className = 'fas fa-file-alt';
        fileIcon.style.fontSize = '60px';
        fileIcon.style.color = '#4a90e2';
        fileIcon.style.marginBottom = '20px';
        
        const fileName = document.createElement('p');
        fileName.textContent = src.split('/').pop();
        fileName.style.wordBreak = 'break-all';
        fileName.style.marginBottom = '20px';
        
        const downloadBtn = document.createElement('a');
        downloadBtn.href = src;
        downloadBtn.download = src.split('/').pop();
        downloadBtn.className = 'btn';
        downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download File';
        
        downloadBox.appendChild(fileIcon);
        downloadBox.appendChild(fileName);
        downloadBox.appendChild(downloadBtn);
        modalContent.appendChild(downloadBox);
      }
      
      modal.style.display = "flex";
      document.body.style.overflow = "hidden";
    }
    
    function closeModal() {
      document.getElementById('previewModal').style.display = "none";
      document.body.style.overflow = "auto";
      
      // Pause any videos when closing modal
      const videos = document.querySelectorAll('#previewModal video');
      videos.forEach(video => {
        video.pause();
      });
    }
    
    // Close modal when pressing Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal();
        closeDeleteModal();
      }
    });
    
    // Delete confirmation functionality
    let fileToDelete = null;
    
    function confirmDelete(fileId) {
      fileToDelete = fileId;
      document.getElementById('deleteModal').style.display = 'flex';
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
      
      // Here you would make an actual AJAX call to delete the file
      // For demonstration, we'll simulate it with a timeout
      setTimeout(() => {
        // This would be your fetch/AJAX call in a real implementation
        /*
        fetch(`delete_file.php?id=${fileToDelete}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ confirm: true })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Remove the deleted file from the UI
            document.querySelector(`.media-item[data-id="${fileToDelete}"]`).remove();
            showToast('File deleted successfully', 'success');
          } else {
            showToast('Error deleting file', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error deleting file', 'error');
        })
        .finally(() => {
          btnText.style.display = 'inline';
          spinner.style.display = 'none';
          closeDeleteModal();
        });
        */
        
        // For demo purposes, we'll just show an alert
        btnText.style.display = 'inline';
        spinner.style.display = 'none';
        closeDeleteModal();
        alert('File delete functionality would be implemented here. File ID: ' + fileToDelete);
        
        // In a real implementation, you would remove the file element here
        // document.querySelector(`.media-item[data-id="${fileToDelete}"]`).remove();
      }, 1500);
    });
    
    // Show toast notification (you can implement this if needed)
    function showToast(message, type = 'info') {
      // Implement toast notification if desired
      console.log(`${type}: ${message}`);
    }
  </script>
</body>
</html>