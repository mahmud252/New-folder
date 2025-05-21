<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/functions.php';

$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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
            --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.15);
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
            background: rgba(240, 238, 255, 0.2);
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

        /* Table Container */
        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 25px;
            overflow-x: auto;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Search and Filter Section */
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
        }

        .btn-refresh {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .btn-refresh:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            animation: fadeIn 0.8s ease;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        th {
            background-color: rgba(108, 92, 231, 0.1);
            font-weight: 600;
            color: var(--primary-dark);
            position: sticky;
            top: 0;
        }

        tr {
            transition: var(--transition);
        }

        tr:hover {
            background-color: rgba(108, 92, 231, 0.03);
            transform: translateX(5px);
        }

        tr:hover td {
            color: var(--primary-dark);
        }

        .status-active {
            color: var(--success);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }

        .status-active::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        .status-inactive {
            color: var(--danger);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }

        .status-inactive::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--danger);
            margin-right: 8px;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }

        .btn i {
            margin-right: 5px;
            font-size: 12px;
        }

        .btn-edit {
            background: rgba(41, 182, 246, 0.1);
            color: var(--info);
            border: 1px solid rgba(41, 182, 246, 0.2);
        }

        .btn-edit:hover {
            background: var(--info);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(41, 182, 246, 0.2);
        }

        .btn-delete {
            background: rgba(255, 255, 255, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.2);
        }

        /* Confirmation Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 500px;
            transform: translateY(20px);
            transition: var(--transition);
            opacity: 0;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
            opacity: 1;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-weight: 600;
            color: var(--danger);
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            background: var(--light-gray);
            color: var(--text-color);
        }

        .btn-cancel:hover {
            background: #d1d1d1;
        }

        .btn-confirm {
            background: var(--danger);
            color: white;
        }

        .btn-confirm:hover {
            background: #c0392b;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .table-container {
                padding: 15px;
            }
            
            th, td {
                padding: 12px 10px;
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
            .table-container {
                padding: 10px;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 14px;
            }
            
            .btn {
                padding: 6px 8px;
                font-size: 12px;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            
            .action-btns {
                flex-direction: column;
                gap: 5px;
            }

            .modal-footer {
                flex-direction: column;
            }

            .modal-footer .btn {
                width: 100%;
            }
        }

        /* Dark Mode */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #121212;
                color: #e0e0e0;
            }

            .table-container {
                background-color: #1e1e1e;
                color: #e0e0e0;
            }

            th {
                background-color: rgba(108, 92, 231, 0.2);
                color: #a29bfe;
            }

            tr:hover {
                background-color: rgba(108, 92, 231, 0.1);
            }

            .search-box input {
                background-color: #2d2d2d;
                border-color: #444;
                color: #e0e0e0;
            }

            .btn-edit {
                background: rgba(41, 182, 246, 0.2);
                border-color: rgba(41, 182, 246, 0.3);
            }

            .btn-delete {
                background: rgba(231, 76, 60, 0.2);
                border-color: rgba(231, 76, 60, 0.3);
            }

            .btn-cancel {
                background: #444;
                color: #e0e0e0;
            }

            .modal {
                background: #2d2d2d;
                color: #e0e0e0;
            }

            .modal-header {
                border-bottom-color: #444;
            }

            .modal-footer {
                border-top-color: #444;
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
            <h2>User Management</h2>
        </div>
    </nav>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirmationModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <button class="btn btn-cancel" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" id="cancelDelete">Cancel</button>
                <button class="btn btn-confirm" id="confirmDelete">Delete User</button>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar" id="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="users.php"><i class="fas fa-users"></i> User Management</a></li>
                <li><a href="files.php"><i class="fas fa-file-upload"></i> All Files</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> User Search</a></li>
                <li><a href="user_panel.php"><i class="fas fa-cog"></i> Control Panel</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content" id="mainContent">
            <h1>User Management</h1>
            
            <div class="table-container">
                <div class="table-header">
                    <div class="search-box">
                        <input type="text" placeholder="Search users..." id="searchInput">
                        <i class="fas fa-search"></i>
                    </div>
                    <button class="btn-refresh" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr data-id="<?php echo $user['id']; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <span class="status-active">
                                        Active
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-delete delete-user-btn" data-id="<?php echo $user['id']; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

            // Confirmation modal functionality
            const modal = document.getElementById('confirmationModal');
            const closeModalBtn = document.getElementById('closeModal');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const deleteButtons = document.querySelectorAll('.delete-user-btn');
            
            let currentDeleteId = null;
            let currentDeleteRow = null;

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    currentDeleteId = this.getAttribute('data-id');
                    currentDeleteRow = this.closest('tr');
                    modal.classList.add('active');
                });
            });

            closeModalBtn.addEventListener('click', closeModal);
            cancelDeleteBtn.addEventListener('click', closeModal);

            function closeModal() {
                modal.classList.remove('active');
                currentDeleteId = null;
                currentDeleteRow = null;
            }

            confirmDeleteBtn.addEventListener('click', function() {
                if (currentDeleteId) {
                    deleteUser(currentDeleteId, currentDeleteRow);
                }
                closeModal();
            });

            // Delete user function
            function deleteUser(userId, row) {
                // Add loading animation
                row.style.opacity = '0.5';
                row.style.transition = 'opacity 0.3s ease';
                
                fetch(`delete_user.php?id=${userId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animate row removal
                        row.style.animation = 'fadeOut 0.3s ease forwards';
                        setTimeout(() => {
                            row.remove();
                            showNotification('User deleted successfully', 'success');
                        }, 300);
                    } else {
                        row.style.opacity = '1';
                        showNotification('Error deleting user: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    row.style.opacity = '1';
                    showNotification('Error deleting user', 'error');
                    console.error('Error:', error);
                });
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

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const usersTable = document.getElementById('usersTable');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = usersTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });

            // Refresh button
            const refreshBtn = document.getElementById('refreshBtn');
            refreshBtn.addEventListener('click', function() {
                window.location.reload();
            });
        });
    </script>
</body>
</html>