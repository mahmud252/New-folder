<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ){
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$data = json_decode(file_get_contents('php://input'), true);

// Verify CSRF token
if (!verifyCSRFToken($data['csrf_token'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
}

$action = $data['action'];
$userIds = array_map('intval', $data['users']);
$affected = 0;

try {
    switch ($action) {
        case 'activate':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected += $stmt->affected_rows;
            }
            break;
            
        case 'disable':
            $stmt = $conn->prepare("UPDATE users SET status = 'disabled' WHERE id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected += $stmt->affected_rows;
            }
            break;
            
        case 'delete':
            // First delete user files
            $stmt = $conn->prepare("DELETE FROM user_files WHERE user_id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }
            
            // Then delete users
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected += $stmt->affected_rows;
            }
            break;
            
        default:
            die(json_encode(['success' => false, 'message' => 'Invalid action']));
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully $action $affected user(s)"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?><?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ){
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$data = json_decode(file_get_contents('php://input'), true);

// Verify CSRF token
if (!verifyCSRFToken($data['csrf_token'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
}

$action = $data['action'];
$userIds = array_map('intval', $data['users']);
$affected = 0;

try {
    switch ($action) {
        case 'activate':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected += $stmt->affected_rows;
            }
            break;
            
        case 'disable':
            $stmt = $conn->prepare("UPDATE users SET status = 'disabled' WHERE id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected += $stmt->affected_rows;
            }
            break;
            
        case 'delete':
            // First delete user files
            $stmt = $conn->prepare("DELETE FROM user_files WHERE user_id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }
            
            // Then delete users
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            foreach ($userIds as $id) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $affected += $stmt->affected_rows;
            }
            break;
            
        default:
            die(json_encode(['success' => false, 'message' => 'Invalid action']));
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully $action $affected user(s)"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>