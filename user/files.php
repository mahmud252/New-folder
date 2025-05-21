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
    <link rel="icon" href="../assets/logo1.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    --success: #00c853;
    --error: #ff3d00;
    --gold-accent: #ffd700;
    --gold-dark: #ffab00;
    --shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    --transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
    --gradient: linear-gradient(135deg, #8a2be2 0%, #4a00e0 100%);
    --glass: rgba(255, 255, 255, 0.15);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Montserrat', sans-serif;
}

body {
    background-color: #f8f9fa;
    color: var(--text-color);
    overflow-x: hidden;
    background-image: 
        radial-gradient(circle at 10% 20%, rgba(138, 43, 226, 0.05) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(74, 0, 224, 0.05) 0%, transparent 20%);
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

/* Top Navigation Bar */
.navbar {
    display: none;
    background: var(--gradient);
    color: white;
    padding: 15px 25px;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow);
    transition: var(--transition);
    backdrop-filter: blur(10px);
}

.navbar.scrolled {
    background: rgba(26, 26, 46, 0.9);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.navbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toggle-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 22px;
    cursor: pointer;
    transition: var(--transition);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.toggle-btn:hover {
    transform: scale(1.1) rotate(90deg);
    background: rgba(255, 255, 255, 0.3);
}

/* Dashboard Layout */
.dashboard-container {
    display: flex;
    min-height: 100vh;
    padding-top: 70px;
}

/* Sidebar */
.sidebar {
    width: 300px;
    background: var(--sidebar-bg);
    color: var(--sidebar-text);
    position: fixed;
    height: calc(100vh - 70px);
    overflow-y: auto;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 999;
    box-shadow: 5px 0 30px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(255, 215, 0, 0.1) 0%, transparent 30%),
        radial-gradient(circle at 80% 70%, rgba(138, 43, 226, 0.1) 0%, transparent 30%);
}

.sidebar h2 {
    text-align: center;
    padding: 30px 0;
    font-size: 24px;
    font-weight: 600;
    letter-spacing: 1px;
    color: var(--gold-accent);
    position: relative;
    margin: 0 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.sidebar h2::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 25%;
    width: 50%;
    height: 3px;
    background: var(--gold-accent);
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.sidebar:hover h2::after {
    transform: scaleX(1);
}

.sidebar ul {
    list-style: none;
    padding: 20px 0;
}

.sidebar ul li {
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    margin: 8px 20px;
    border-radius: 8px;
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
    padding: 18px 25px;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: var(--transition);
    position: relative;
    z-index: 1;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.sidebar ul li a i {
    margin-right: 15px;
    width: 24px;
    text-align: center;
    font-size: 20px;
    transition: var(--transition);
}

.sidebar ul li.active,
.sidebar ul li:hover {
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.sidebar ul li.active a,
.sidebar ul li:hover a {
    color: var(--gold-accent);
    transform: translateX(10px);
}

.sidebar ul li.active a i,
.sidebar ul li:hover a i {
    transform: scale(1.2);
    color: var(--gold-accent);
}

/* Main Content */
.main-content {
    margin-left: 300px;
    padding: 40px 50px;
    flex-grow: 1;
    transition: margin-left 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9InJnYmEoMTM4LDQzLDIyNiwwLjAzKSIgZmlsbC1ydWxlPSJub256ZXJvIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIgMS44LTQgNC00czQgMS44IDQgNC0xLjggNC00IDQtNC0xLjgtNC00eiIvPjwvZz48L2c+PC9zdmc+');
}

.main-content h1 {
    font-size: 36px;
    margin-bottom: 40px;
    color: var(--primary-dark);
    position: relative;
    display: inline-block;
    font-weight: 700;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.main-content h1::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 0;
    width: 70px;
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
        width: 70px;
        opacity: 1;
    }
}

/* Upload Form */
.upload-form {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 16px;
    box-shadow: var(--shadow);
    margin-bottom: 40px;
    transition: var(--transition);
    border: 1px solid rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.85);
}

.upload-form:hover {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}

.upload-form h2 {
    margin-bottom: 25px;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 24px;
    font-weight: 600;
}

.upload-form h2 i {
    color: var(--gold-accent);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Drag and drop area */
.dropzone {
    border: 2px dashed var(--primary-light);
    border-radius: 16px;
    padding: 50px;
    text-align: center;
    margin-bottom: 25px;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.5);
}

.dropzone::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(138, 43, 226, 0.1) 0%, transparent 70%);
    transform: scale(0);
    transition: transform 0.8s ease;
}

.dropzone:hover::before {
    transform: scale(1);
}

.dropzone.active {
    border-color: var(--primary);
    background-color: rgba(138, 43, 226, 0.05);
    animation: borderPulse 2s infinite;
}

@keyframes borderPulse {
    0% { box-shadow: 0 0 0 0 rgba(138, 43, 226, 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(138, 43, 226, 0); }
    100% { box-shadow: 0 0 0 0 rgba(138, 43, 226, 0); }
}

.dropzone i {
    font-size: 60px;
    color: var(--primary);
    margin-bottom: 20px;
    transition: var(--transition);
    text-shadow: 0 5px 15px rgba(138, 43, 226, 0.2);
}

.dropzone:hover i {
    transform: translateY(-10px) scale(1.1);
    color: var(--primary-dark);
}

.dropzone p {
    margin-bottom: 15px;
    transition: var(--transition);
    font-size: 18px;
    font-weight: 500;
}

.dropzone:hover p {
    color: var(--primary-dark);
}

.file-upload-info {
    font-size: 0.9rem;
    color: #666;
}

/* File preview container */
.file-previews {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 25px;
}

.file-preview {
    width: 120px;
    height: 120px;
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
    background: white;
}

.file-preview:hover {
    transform: translateY(-10px) scale(1.05);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}

.file-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.file-preview:hover img {
    transform: scale(1.1);
}

.file-preview .file-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
    color: var(--primary);
    transition: var(--transition);
    font-size: 36px;
}

.file-preview:hover .file-icon {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    color: white;
}

.file-preview .remove-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--error);
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    opacity: 0;
    transform: scale(0.8);
    transition: var(--transition);
    box-shadow: 0 2px 10px rgba(255, 61, 0, 0.3);
    z-index: 2;
}

.file-preview:hover .remove-btn {
    opacity: 1;
    transform: scale(1);
}

.file-preview .remove-btn:hover {
    transform: scale(1.2) !important;
    background: #ff5722;
}

/* Upload limits info */
.upload-limits {
    background: rgba(255, 255, 255, 0.7);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 0.95rem;
    border-left: 4px solid var(--primary);
    transition: var(--transition);
    backdrop-filter: blur(5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.upload-limits:hover {
    background: rgba(138, 43, 226, 0.05);
    transform: translateX(10px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.upload-limits i {
    color: var(--primary);
    margin-right: 10px;
    transition: var(--transition);
    font-size: 18px;
}

.upload-limits:hover i {
    transform: scale(1.3);
    color: var(--gold-accent);
}

.upload-limits p {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.btn {
    background: var(--gradient);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 18px;
    font-weight: 600;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 5px 20px rgba(138, 43, 226, 0.3);
    letter-spacing: 0.5px;
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

.btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(138, 43, 226, 0.4);
}

.btn:hover::before {
    left: 100%;
}

.btn:disabled {
    background: #cccccc;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

.btn:disabled::before {
    display: none;
}

.btn i {
    transition: var(--transition);
}

.btn:hover i {
    transform: translateY(-3px);
}

/* Progress Bar */
.progress-container {
    display: none;
    margin: 25px 0;
    background: rgba(255, 255, 255, 0.8);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid var(--primary);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    backdrop-filter: blur(5px);
    transition: var(--transition);
}

.progress-container.active {
    display: block;
    animation: fadeInUp 0.5s both;
}

.progress-bar {
    width: 100%;
    height: 12px;
    background: #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 15px;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
}

.progress {
    height: 100%;
    background: var(--gradient);
    width: 0%;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.progress::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, 
        rgba(255,255,255,0) 0%, 
        rgba(255,255,255,0.4) 50%, 
        rgba(255,255,255,0) 100%);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: var(--text-color);
    font-weight: 500;
}

/* Alerts */
.alert {
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: var(--transition);
    opacity: 0;
    transform: translateY(-20px);
    animation: fadeIn 0.5s forwards;
    backdrop-filter: blur(5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert.success {
    background: rgba(0, 200, 83, 0.1);
    color: var(--success);
    border-left: 4px solid var(--success);
}

.alert.error {
    background: rgba(255, 61, 0, 0.1);
    color: var(--error);
    border-left: 4px solid var(--error);
}

.alert i {
    font-size: 24px;
}

/* Media Container */
.media-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
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

.media-item img,
.media-item video {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
    transition: var(--transition);
}

.media-item:hover img,
.media-item:hover video {
    transform: scale(1.08);
}

.media-item video {
    background: #000;
}

.media-info {
    padding: 25px;
}

.media-info p {
    margin-bottom: 12px;
    font-size: 0.95rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 10px;
}

.media-item:hover .media-info p {
    color: var(--primary-dark);
}

.media-info p strong {
    font-weight: 600;
    min-width: 80px;
    display: inline-block;
}

.media-info p i {
    color: var(--primary);
    font-size: 18px;
    width: 24px;
}

/* File Download Link */
.media-item a[download] {
    display: block;
    padding: 18px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    text-align: center;
    background: rgba(138, 43, 226, 0.05);
    border-top: 1px solid rgba(138, 43, 226, 0.1);
}

.media-item a[download]:hover {
    color: white;
    background: var(--gradient);
}

.media-item a[download] i {
    margin-right: 10px;
    transition: var(--transition);
}

.media-item a[download]:hover i {
    transform: translateY(-3px);
}

/* No files message */
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

.no-files i {
    font-size: 60px;
    color: var(--primary-light);
    margin-bottom: 20px;
    display: block;
    transition: var(--transition);
}

.no-files:hover i {
    color: var(--primary);
    transform: scale(1.1);
}

.no-files p {
    font-size: 20px;
    color: var(--text-color);
    font-weight: 500;
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

/* Responsive Design */
@media (max-width: 1200px) {
    .sidebar {
        width: 280px;
    }
    .main-content {
        margin-left: 280px;
    }
}

@media (max-width: 992px) {
    .navbar {
        display: block;
    }

    .sidebar {
        transform: translateX(-100%);
        box-shadow: none;
        width: 320px;
    }

    .sidebar.show {
        transform: translateX(0);
        box-shadow: 5px 0 30px rgba(0, 0, 0, 0.3);
    }

    .main-content {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .media-container {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .dropzone {
        padding: 40px 20px;
    }

    .file-preview {
        width: 100px;
        height: 100px;
    }

    .main-content {
        padding: 30px;
    }

    .upload-form {
        padding: 25px;
    }

    .upload-form h2 {
        font-size: 22px;
    }

    .dropzone i {
        font-size: 48px;
    }

    .dropzone p {
        font-size: 16px;
    }
}

@media (max-width: 576px) {
    .media-container {
        grid-template-columns: 1fr;
    }

    .main-content {
        padding: 25px 20px;
    }

    .upload-form {
        padding: 20px;
    }

    .dropzone {
        padding: 30px 15px;
    }

    .dropzone i {
        font-size: 36px;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }

    .no-files {
        padding: 40px 20px;
    }

    .no-files i {
        font-size: 48px;
    }

    .no-files p {
        font-size: 18px;
    }
}

/* Additional Animations */
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
        text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
    }
    to {
        text-shadow: 0 0 10px rgba(255, 215, 0, 0.8), 0 0 15px rgba(255, 215, 0, 0.5);
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

<body oncontextmenu="return false;">
    <!-- Floating Particles Background -->
    <div class="particles" id="particles"></div>
    
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
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="files.php"><i class="fas fa-file-upload"></i> My Uploaded Files</a></li>
                <li><a href="storage.php"><i class="fas fa-database"></i> My Storage</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> My Login History</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content" id="mainContent">
            <h1 class="animate__animated animate__fadeIn">All Uploaded Files <span class="glow-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h1>

            <div class="upload-form animate__animated animate__fadeInUp">
                <h2><i class="fas fa-cloud-upload-alt"></i> Upload New Files</h2>
                <?php if ($uploadMessage): ?>
                    <div class="alert <?php echo strpos($uploadMessage, 'Successfully') !== false ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo strpos($uploadMessage, 'Successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $uploadMessage; ?>
                    </div>
                <?php endif; ?>

                <div class="upload-limits">
                    <p><i class="fas fa-info-circle"></i> You can upload up to 120 files at once (max 600MB total)</p>
                    <p><i class="fas fa-check-circle"></i> Supported formats: JPG, PNG, GIF, MP4, WEBM, and more</p>
                </div>

                <form action="files.php" method="post" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <div class="dropzone ripple" id="dropzone">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drag & drop files here or click to browse</p>
                            <p class="file-upload-info">(Max 120 files, 600MB total)</p>
                            <input type="file" id="files" name="files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar" style="display: none;">
                        </div>
                        <div class="file-previews" id="filePreviews"></div>
                    </div>

                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress" id="progressBar"></div>
                        </div>
                        <div class="progress-info">
                            <span id="progressText">0%</span>
                            <span id="fileCount">0 files selected</span>
                            <span id="totalSize">0 MB</span>
                        </div>
                    </div>

                    <button type="submit" class="btn" id="uploadBtn" disabled>
                        <i class="fas fa-upload"></i> Upload Files
                    </button>
                </form>
            </div>

            <div class="media-container">
                <?php if (empty($files)): ?>
                    <div class="no-files animate__animated animate__fadeIn">
                        <i class="fas fa-folder-open"></i>
                        <p>No files uploaded yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <div class="media-item animate__animated animate__fadeInUp">
                            <?php
                            $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])): ?>
                                <img src="<?php echo $file['filepath']; ?>" alt="<?php echo htmlspecialchars($file['filename']); ?>">
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
                                <p><i class="fas fa-file"></i><strong>Filename:</strong> <?php echo htmlspecialchars($file['filename']); ?></p>
                                <p><i class="fas fa-hdd"></i><strong>Size:</strong> <?php echo formatFileSize($file['filesize']); ?></p>
                                <p><i class="fas fa-calendar-alt"></i><strong>Uploaded:</strong> <?php echo date('M d, Y H:i', strtotime($file['uploaded_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                const icons = ['fa-file', 'fa-file-image', 'fa-file-video', 'fa-file-pdf', 
                              'fa-file-word', 'fa-file-excel', 'fa-file-powerpoint', 
                              'fa-file-archive', 'fa-file-code', 'fa-file-audio'];
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
            
            // Initialize background elements
            createParticles();
            createFloatingIcons();

            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const navbar = document.getElementById('navbar');

            sidebarToggle.addEventListener('click', function (e) {
                sidebar.classList.toggle('show');
                this.classList.toggle('active');
                
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

            // Navbar effect on scroll
            window.addEventListener('scroll', function () {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
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
            const progressContainer = document.querySelector('.progress-container');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const fileCountDisplay = document.getElementById('fileCount');
            const totalSizeDisplay = document.getElementById('totalSize');
            const MAX_FILES = 120;
            const MAX_SIZE = 600; // 600MB

            // Click on dropzone triggers file input
            dropzone.addEventListener('click', (e) => {
                // Create ripple effect
                const rect = dropzone.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.classList.add('ripple-effect');
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                dropzone.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
                
                fileInput.click();
            });

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
                    showAlert(`You can upload maximum ${MAX_FILES} files at once`, 'error');
                    return;
                }

                // Calculate total size and create previews
                Array.from(files).forEach(file => {
                    totalSize += file.size;

                    // Create preview
                    const preview = document.createElement('div');
                    preview.className = 'file-preview animate__animated animate__fadeIn';

                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        preview.appendChild(img);
                    } else {
                        const icon = document.createElement('div');
                        icon.className = 'file-icon';
                        
                        // Set appropriate icon based on file type
                        if (file.type.startsWith('video/')) {
                            icon.innerHTML = `<i class="fas fa-file-video"></i>`;
                        } else if (file.type.includes('pdf')) {
                            icon.innerHTML = `<i class="fas fa-file-pdf"></i>`;
                        } else if (file.type.includes('word')) {
                            icon.innerHTML = `<i class="fas fa-file-word"></i>`;
                        } else if (file.type.includes('excel') || file.type.includes('spreadsheet')) {
                            icon.innerHTML = `<i class="fas fa-file-excel"></i>`;
                        } else if (file.type.includes('powerpoint') || file.type.includes('presentation')) {
                            icon.innerHTML = `<i class="fas fa-file-powerpoint"></i>`;
                        } else if (file.type.includes('zip') || file.type.includes('rar') || file.type.includes('archive')) {
                            icon.innerHTML = `<i class="fas fa-file-archive"></i>`;
                        } else {
                            icon.innerHTML = `<i class="fas fa-file"></i>`;
                        }
                        
                        preview.appendChild(icon);
                    }

                    // Add remove button
                    const removeBtn = document.createElement('div');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        preview.classList.add('animate__fadeOut');
                        setTimeout(() => {
                            preview.remove();
                            updateFileInfo();
                        }, 300);
                    });
                    preview.appendChild(removeBtn);

                    filePreviews.appendChild(preview);
                });

                // Check total size
                const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);
                if (totalSizeMB > MAX_SIZE) {
                    showAlert(`Total size (${totalSizeMB}MB) exceeds ${MAX_SIZE}MB limit`, 'error');
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
                e.preventDefault();
                
                progressContainer.classList.add('active');
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 10;
                    if (progress > 90) clearInterval(interval);
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.round(progress) + '%';
                    
                    if (progress >= 100) {
                        setTimeout(() => {
                            // Submit the form when progress completes
                            this.submit();
                        }, 500);
                    }
                }, 300);
            });

            // Show alert message
            function showAlert(message, type) {
                const alert = document.createElement('div');
                alert.className = `alert ${type}`;
                alert.innerHTML = `
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${message}
                `;
                
                const uploadForm = document.querySelector('.upload-form');
                uploadForm.insertBefore(alert, uploadForm.firstChild);
                
                setTimeout(() => {
                    alert.classList.add('animate__fadeOut');
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            }

            // Animate media items when they come into view
            const animateOnScroll = function() {
                const mediaItems = document.querySelectorAll('.media-item:not(.animated)');
                
                mediaItems.forEach(item => {
                    const itemPosition = item.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;
                    
                    if (itemPosition < screenPosition) {
                        item.classList.add('animate__fadeInUp', 'animated');
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            // Initial check in case elements are already visible
            animateOnScroll();
            
            // Add ripple effect to buttons
            document.querySelectorAll('.btn').forEach(button => {
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
        });

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>