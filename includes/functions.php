<?php
require_once 'config.php';

/**
 * Format bytes to human-readable format
 */
function formatFileSize($bytes) {
    $units = ['bytes', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

function getUserById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getAllUsers() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE is_admin = FALSE");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserFiles($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllFiles() {
    global $conn;
    $stmt = $conn->prepare("SELECT f.*, u.username FROM files f JOIN users u ON f.user_id = u.id ORDER BY f.uploaded_at DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getLoginHistory($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM login_history WHERE user_id = ? ORDER BY login_time DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function uploadFile($userId, $file) {
    global $conn;

    if (!file_exists(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
        return ['success' => false, 'message' => 'Failed to create upload directory'];
    }

    $targetDir = UPLOAD_DIR;
    $safeFileName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($file["name"]));
    $targetFile = $targetDir . uniqid() . '_' . $safeFileName;
    $fileType = strtolower(pathinfo($safeFileName, PATHINFO_EXTENSION));
    $fileSize = $file["size"];

    if ($fileSize > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File exceeds maximum size of ' . formatFileSize(MAX_FILE_SIZE)];
    }

    $type = 'other';
    if (defined('ALLOWED_IMAGE_TYPES') && in_array($fileType, ALLOWED_IMAGE_TYPES)) {
        $type = 'image';
    } elseif (defined('ALLOWED_VIDEO_TYPES') && in_array($fileType, ALLOWED_VIDEO_TYPES)) {
        $type = 'video';
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, filepath, filetype, filesize) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $userId, $safeFileName, $targetFile, $type, $fileSize);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'File uploaded successfully'];
        } else {
            @unlink($targetFile);
            return ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

function getStatistics() {
    global $conn;
    $stats = [];
    $queries = [
        'total_users' => "SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE",
        'active_users' => "SELECT COUNT(*) as count FROM users WHERE status = 'active' AND is_admin = FALSE",
        'total_files' => "SELECT COUNT(*) as count FROM files",
        'total_images' => "SELECT COUNT(*) as count FROM files WHERE filetype = 'image'",
        'total_videos' => "SELECT COUNT(*) as count FROM files WHERE filetype = 'video'"
    ];
    foreach ($queries as $key => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats[$key] = $result->fetch_assoc()['count'] ?? 0;
    }
    return $stats;
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
}

function updateUserStatus($userId, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $userId);
    return $stmt->execute();
}

function getUserActivityLog($userId) {
    return getLoginHistory($userId); // Reuse login history
}

function logAdminAction($adminId, $userId, $action, $reason = '') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, user_id, action, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $adminId, $userId, $action, $reason);
    return $stmt->execute();
}

function notifyUser($userId, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO user_notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $message);
    return $stmt->execute();
}

function deleteUser($userId) {
    global $conn;
    $user = getUserById($userId);
    if ($user) {
        $stmt = $conn->prepare("INSERT INTO deleted_users (original_id, name, email, username, status, created_at, deleted_at, deleted_by, reason) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param("isssssis", $user['id'], $user['name'], $user['email'], $user['username'], $user['status'], $user['created_at'], $_SESSION['user_id'], $_POST['reason']);
        $stmt->execute();
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}
?>