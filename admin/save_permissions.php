<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_ids = $_POST['user_ids'] ?? [];
    $permissions = $_POST['permissions'] ?? [];

    foreach ($user_ids as $id) {
        $can_view = isset($permissions[$id]['can_view']) ? 1 : 0;
        $can_edit = isset($permissions[$id]['can_edit']) ? 1 : 0;
        $can_delete = isset($permissions[$id]['can_delete']) ? 1 : 0;
        $can_upload = isset($permissions[$id]['can_upload']) ? 1 : 0;

        // Check if permission row exists
        $check = $conn->query("SELECT id FROM permissions WHERE user_id = $id");
        if ($check && $check->num_rows > 0) {
            // Update
            $conn->query("UPDATE permissions SET can_view = $can_view, can_edit = $can_edit, can_delete = $can_delete, can_upload = $can_upload WHERE user_id = $id");
        } else {
            // Insert
            $conn->query("INSERT INTO permissions (user_id, can_view, can_edit, can_delete, can_upload) VALUES ($id, $can_view, $can_edit, $can_delete, $can_upload)");
        }
    }

    header("Location: user_panel.php?success=1");
    exit;
}
