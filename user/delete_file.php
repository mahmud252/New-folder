<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$userId = $_SESSION['user_id'];
$fileId = $_POST['id'] ?? null;

if (!$fileId || !is_numeric($fileId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file ID']);
    exit;
}

// Check file ownership
$stmt = $pdo->prepare("SELECT filepath FROM files WHERE id = :id AND user_id = :user_id");
$stmt->execute([
    'id' => $fileId,
    'user_id' => $userId
]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found or access denied']);
    exit;
}

// Delete the physical file
if (file_exists($file['filepath'])) {
    @unlink($file['filepath']);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM files WHERE id = :id AND user_id = :user_id");
$stmt->execute([
    'id' => $fileId,
    'user_id' => $userId
]);

echo json_encode(['success' => true]);
