<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/functions.php';

$userId = $_SESSION['user_id'];
$files = getUserFiles($userId);

// Handle file upload
$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    try {
        $uploadResults = [];
        $maxTotalSize = 600 * 1024 * 1024; // 600MB
        $totalSize = 0;
        $maxFiles = 120;

        // Calculate total size first
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $totalSize += $_FILES['files']['size'][$key];
        }

        if ($totalSize > $maxTotalSize) {
            throw new Exception("Total upload size exceeds 600MB limit");
        }

        if (count($_FILES['files']['tmp_name']) > $maxFiles) {
            throw new Exception("Maximum 120 files allowed per upload");
        }

        // Process each file
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            if (!empty($tmpName)) {
                $file = [
                    'name' => $_FILES['files']['name'][$key],
                    'type' => $_FILES['files']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['files']['error'][$key],
                    'size' => $_FILES['files']['size'][$key]
                ];

                $uploadResult = uploadFile($userId, $file);
                $uploadResults[] = $uploadResult;
            }
        }

        $successCount = count(array_filter($uploadResults, fn($r) => $r['success']));
        $uploadMessage = "Successfully uploaded {$successCount} files";

        if ($successCount > 0) {
            header("Location: files.php");
            exit();
        }
    } catch (Exception $e) {
        $uploadMessage = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Uploaded Files</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --sidebar-bg: #2c3e50;
            --sidebar-text: #ffffff;
            --card-bg: #ffffff;
            --text-color: #333333;
            --success: #4caf50;
            --error: #f44336;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--text-color);
        }

        /* Top Navigation Bar */
        .navbar {
            display: none;
            background-color: var(--sidebar-bg);
            color: white;
            padding: 15px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            padding-top: 60px;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            height: calc(100vh - 60px);
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 999;
        }

        .sidebar h2 {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            padding: 15px 20px;
            transition: all 0.2s;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--sidebar-text);
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar ul li.active,
        .sidebar ul li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            transition: margin 0.3s ease;
        }

        /* Upload Form */
        .upload-form {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .upload-form h2 {
            margin-bottom: 15px;
            color: var(--primary);
        }

        /* Drag and drop area */
        .dropzone {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .dropzone.active {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .dropzone i {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .dropzone p {
            margin-bottom: 10px;
        }

        .file-upload-info {
            font-size: 0.85rem;
            color: #666;
        }

        /* File preview container */
        .file-previews {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .file-preview {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 4px;
            overflow: hidden;
        }

        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-preview .file-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: #f0f0f0;
            color: #666;
        }

        .file-preview .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--error);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
        }

        /* Upload limits info */
        .upload-limits {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .upload-limits i {
            color: var(--primary);
            margin-right: 5px;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .btn:hover {
            background: var(--primary-dark);
        }

        .btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        /* Progress Bar */
        .progress-container {
            display: none;
            margin: 15px 0;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .progress {
            height: 100%;
            background: var(--primary);
            width: 0%;
            transition: width 0.3s;
        }

        /* Alerts */
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .alert.success {
            background: #e8f5e9;
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert.error {
            background: #ffebee;
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        /* Media Container */
        .media-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .media-item {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .media-item img,
        .media-item video {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .media-item video {
            background: #000;
        }

        .media-info {
            padding: 15px;
        }

        .media-info p {
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .media-info p strong {
            font-weight: 600;
        }

        /* File Download Link */
        .media-item a[download] {
            display: block;
            padding: 15px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .media-item a[download]:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .navbar {
                display: block;
            }

            .sidebar {
                transform: translateX(-250px);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .media-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .media-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
   <!-- Top Navigation Bar (Mobile) -->
   <nav class="navbar">
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
            <h1>All Uploaded Files</h1>

            <div class="upload-form">
                <h2><i class="fas fa-cloud-upload-alt"></i> Upload New Files</h2>
                <?php if ($uploadMessage): ?>
                    <div
                        class="alert <?php echo strpos($uploadMessage, 'Successfully') !== false ? 'success' : 'error'; ?>">
                        <i
                            class="fas <?php echo strpos($uploadMessage, 'Successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $uploadMessage; ?>
                    </div>
                <?php endif; ?>

                <div class="upload-limits">
                    <p><i class="fas fa-info-circle"></i> You can upload up to 120 files at once (max 600MB total)</p>
                    <p><i class="fas fa-check-circle"></i> Supported formats: JPG, PNG, GIF, MP4, WEBM, and more</p>
                </div>

                <form action="files.php" method="post" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <div class="dropzone" id="dropzone">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag & drop files here or click to browse</p>
                            <p class="file-upload-info">(Max 120 files, 600MB total)</p>
                            <input type="file" id="files" name="files[]" multiple accept="image/*,video/*"
                                style="display: none;">
                        </div>
                        <div class="file-previews" id="filePreviews"></div>
                    </div>

                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress" id="progressBar"></div>
                        </div>
                        <div id="progressText">0%</div>
                        <div id="fileCount">0 files selected</div>
                        <div id="totalSize">0 MB</div>
                    </div>

                    <button type="submit" class="btn" id="uploadBtn" disabled>
                        <i class="fas fa-upload"></i> Upload Files
                    </button>
                </form>
            </div>

            <div class="media-container">
                <?php if (empty($files)): ?>
                    <div class="no-files">
                        <p><i class="fas fa-folder-open"></i> No files uploaded yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <div class="media-item">
                            <?php
                            $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                                <img src="<?php echo $file['filepath']; ?>"
                                    alt="<?php echo htmlspecialchars($file['filename']); ?>">
                            <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                                <video controls>
                                    <source src="<?php echo $file['filepath']; ?>" type="video/<?php echo $ext; ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <a href="<?php echo $file['filepath']; ?>" download>
                                    <i class="fas fa-file-download"></i> <?php echo htmlspecialchars($file['filename']); ?>
                                </a>
                            <?php endif; ?>
                            <div class="media-info">
                                <p><strong><i class="fas fa-file"></i> Filename:</strong>
                                    <?php echo htmlspecialchars($file['filename']); ?></p>
                                <p><strong><i class="fas fa-hdd"></i> Size:</strong>
                                    <?php echo formatFileSize($file['filesize']); ?></p>
                                <p><strong><i class="fas fa-calendar-alt"></i> Uploaded:</strong>
                                    <?php echo date('M d, Y H:i', strtotime($file['uploaded_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function (event) {
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

            // Adjust layout on window resize
            window.addEventListener('resize', function () {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('show');
                }
            });

            // File upload functionality
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('files');
            const filePreviews = document.getElementById('filePreviews');
            const uploadBtn = document.getElementById('uploadBtn');
            const fileCountDisplay = document.getElementById('fileCount');
            const totalSizeDisplay = document.getElementById('totalSize');
            const MAX_FILES = 120;
            const MAX_SIZE = 600; // 600MB

            // Click on dropzone triggers file input
            dropzone.addEventListener('click', () => fileInput.click());

            // Handle file selection
            fileInput.addEventListener('change', handleFiles);

            // Drag and drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropzone.classList.add('active');
            }

            function unhighlight() {
                dropzone.classList.remove('active');
            }

            dropzone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                handleFiles({ target: fileInput });
            }

            function handleFiles(e) {
                const files = e.target.files;
                let totalSize = 0;

                // Clear previous previews
                filePreviews.innerHTML = '';

                // Validate files
                if (files.length > MAX_FILES) {
                    alert(`You can upload maximum ${MAX_FILES} files at once`);
                    return;
                }

                // Calculate total size and create previews
                Array.from(files).forEach(file => {
                    totalSize += file.size;

                    // Create preview
                    const preview = document.createElement('div');
                    preview.className = 'file-preview';

                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        preview.appendChild(img);
                    } else {
                        const icon = document.createElement('div');
                        icon.className = 'file-icon';
                        icon.innerHTML = `<i class="fas fa-file"></i>`;
                        preview.appendChild(icon);
                    }

                    // Add remove button
                    const removeBtn = document.createElement('div');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '×';
                    removeBtn.addEventListener('click', () => {
                        preview.remove();
                        updateFileInfo();
                    });
                    preview.appendChild(removeBtn);

                    filePreviews.appendChild(preview);
                });

                // Check total size
                const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);
                if (totalSizeMB > MAX_SIZE) {
                    alert(`Total size (${totalSizeMB}MB) exceeds ${MAX_SIZE}MB limit`);
                    fileInput.value = '';
                    filePreviews.innerHTML = '';
                    return;
                }

                updateFileInfo();
            }

            function updateFileInfo() {
                const files = fileInput.files;
                let totalSize = 0;
                let fileCount = 0;

                Array.from(files).forEach(file => {
                    totalSize += file.size;
                    fileCount++;
                });

                const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);

                fileCountDisplay.textContent = `${fileCount} file${fileCount !== 1 ? 's' : ''} selected`;
                totalSizeDisplay.textContent = `${totalSizeMB} MB`;

                // Enable/disable upload button
                uploadBtn.disabled = fileCount === 0;
            }

            // File upload progress
            document.getElementById('uploadForm').addEventListener('submit', function (e) {
                const progressContainer = document.querySelector('.progress-container');
                const progressBar = document.getElementById('progressBar');
                const progressText = document.getElementById('progressText');

                progressContainer.style.display = 'block';
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 10;
                    if (progress > 90) clearInterval(interval);
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.round(progress) + '%';
                }, 300);
            });

            // Format file size
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
    </script>
</body>

</html>