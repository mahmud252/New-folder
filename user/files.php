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
        body { font-family: 'Poppins', Arial, sans-serif; background: #f4f7fa; margin: 0; }
        .navbar { background: #4a90e2; color: #fff; padding: 12px 0; }
        .navbar-content { display: flex; align-items: center; gap: 18px; padding-left: 24px; }
        .navbar h2 { margin: 0; font-size: 1.3rem; }
        .toggle-btn { background: none; border: none; color: #fff; font-size: 1.3rem; cursor: pointer; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { background: #232946; color: #fff; width: 220px; padding: 24px 0 0 0; min-height: 100vh; }
        .sidebar h2 { font-size: 1.2rem; text-align: center; margin-bottom: 18px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li { margin: 0; }
        .sidebar ul li a { display: block; color: #fff; text-decoration: none; padding: 13px 28px; transition: background 0.2s; }
        .sidebar ul li.active, .sidebar ul li a:hover { background: #4a90e2; }
        .main-content { flex: 1; padding: 32px 24px; }
        .upload-form { background: #fff; border-radius: 10px; padding: 24px; margin-bottom: 28px; box-shadow: 0 2px 12px rgba(0,0,0,0.04);}
        .upload-form h2 { margin-top: 0; font-size: 1.2rem; }
        .alert { padding: 10px 16px; border-radius: 6px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
        .alert.success { background: #e0f7e9; color: #1b7e3c; }
        .alert.error { background: #ffeaea; color: #c0392b; }
        .upload-limits { font-size: 0.97rem; color: #555; margin-bottom: 12px; }
        .upload-limits i { color: #4a90e2; margin-right: 6px; }
        .form-group { margin-bottom: 18px; }
        .dropzone { border: 2px dashed #4a90e2; border-radius: 8px; padding: 32px 0; text-align: center; color: #4a90e2; cursor: pointer; margin-bottom: 10px; transition: border 0.2s; }
        .dropzone.active { border-color: #232946; background: #f0f4ff; }
        .dropzone i { font-size: 2.2rem; }
        .file-previews { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .file-preview { position: relative; width: 60px; height: 60px; border-radius: 6px; overflow: hidden; background: #f0f4ff; display: flex; align-items: center; justify-content: center; }
        .file-preview img { width: 100%; height: 100%; object-fit: cover; }
        .file-icon { font-size: 2rem; color: #4a90e2; }
        .remove-btn { position: absolute; top: 2px; right: 4px; background: #fff; color: #c0392b; border-radius: 50%; width: 18px; height: 18px; font-size: 1rem; text-align: center; line-height: 17px; cursor: pointer; }
        .progress-container { display: none; margin: 15px 0; }
        .progress-bar { width: 100%; height: 10px; background: #e0e0e0; border-radius: 5px; overflow: hidden; margin-bottom: 5px; }
        .progress { height: 100%; background: #4a90e2; width: 0%; transition: width 0.3s; }
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
<body>
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
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="files.php"><i class="fas fa-file-upload"></i> My Uploaded Files</a></li>
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

                    <div class="progress-container" style="display:none;">
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
            const progressContainer = document.querySelector('.progress-container');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
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
                    fileInput.value = '';
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
                    } else if (file.type.startsWith('video/')) {
                        const icon = document.createElement('div');
                        icon.className = 'file-icon';
                        icon.innerHTML = `<i class="fas fa-film"></i>`;
                        preview.appendChild(icon);
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
                    updateFileInfo();
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

            // File upload progress (AJAX for speed, no page reload)
            document.getElementById('uploadForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const files = fileInput.files;
                if (!files.length) return;

                progressContainer.style.display = 'flex';
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

                const formData = new FormData();
                for (let i = 0; i < files.length; i++) {
                    formData.append('files[]', files[i]);
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'files.php', true);

                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressText.textContent = percent + '%';
                    }
                };

                xhr.onload = function () {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Files';
                    progressBar.style.width = '100%';
                    progressText.textContent = '100%';
                    if (xhr.status === 200) {
                        // Reload page to show new files
                        window.location.reload();
                    } else {
                        alert('Upload failed. Please try again.');
                    }
                };

                xhr.onerror = function () {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Files';
                    alert('Upload failed. Please try again.');
                };

                xhr.send(formData);
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