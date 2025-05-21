<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin();

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user data with permissions
$user = [];
$sql = "SELECT u.*, p.can_view, p.can_edit, p.can_delete, p.can_upload 
        FROM users u
        LEFT JOIN permissions p ON u.id = p.user_id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    header("Location: users.php?error=User not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Permission checkboxes
    $can_view = isset($_POST['can_view']) ? 1 : 0;
    $can_edit = isset($_POST['can_edit']) ? 1 : 0;
    $can_delete = isset($_POST['can_delete']) ? 1 : 0;
    $can_upload = isset($_POST['can_upload']) ? 1 : 0;

    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($username)) $errors[] = "Username is required";

    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update user table
            $update_sql = "UPDATE users SET name = ?, email = ?, username = ?, is_admin = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssii", $name, $email, $username, $is_admin, $user_id);
            $update_stmt->execute();
            
            // Update or insert permissions
            $perm_sql = "INSERT INTO permissions (user_id, can_view, can_edit, can_delete, can_upload)
                          VALUES (?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE 
                          can_view = VALUES(can_view),
                          can_edit = VALUES(can_edit),
                          can_delete = VALUES(can_delete),
                          can_upload = VALUES(can_upload)";
            $perm_stmt = $conn->prepare($perm_sql);
            $perm_stmt->bind_param("iiiii", $user_id, $can_view, $can_edit, $can_delete, $can_upload);
            $perm_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            header("Location: users.php?success=User updated successfully");
            exit();
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $errors[] = "Error updating user: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Previous head content remains the same -->
    <style>
        /* Previous styles remain the same */
        
        /* Permissions Section */
        .permissions-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            animation: fadeIn 0.8s ease;
        }
        
        .permissions-section h3 {
            margin-bottom: 15px;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        
        .permission-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .permission-item i {
            color: var(--primary);
            font-size: 18px;
        }
        
        /* Admin badge */
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: var(--primary);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .permissions-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .permissions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body oncontextmenu="return false;">
    <!-- Previous body content remains the same until the form -->
    
    <div class="form-container">
        <form method="POST" id="editUserForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="admin-toggle">
                <label for="is_admin">
                    Admin Privileges
                    <?php if ($user['is_admin']): ?>
                        <span class="admin-badge">
                            <i class="fas fa-shield-alt"></i> ADMIN
                        </span>
                    <?php endif; ?>
                </label>
                <label class="toggle-switch">
                    <input type="checkbox" id="is_admin" name="is_admin" 
                           <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            
            <!-- Permissions Section -->
            <div class="permissions-section">
                <h3><i class="fas fa-user-lock"></i> User Permissions</h3>
                <div class="permissions-grid">
                    <div class="permission-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="can_view" 
                                   <?php echo $user['can_view'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <div>
                            <i class="fas fa-eye"></i>
                            <span>View Content</span>
                        </div>
                    </div>
                    
                    <div class="permission-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="can_edit" 
                                   <?php echo $user['can_edit'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <div>
                            <i class="fas fa-edit"></i>
                            <span>Edit Content</span>
                        </div>
                    </div>
                    
                    <div class="permission-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="can_delete" 
                                   <?php echo $user['can_delete'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <div>
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete Content</span>
                        </div>
                    </div>
                    
                    <div class="permission-item">
                        <label class="toggle-switch">
                            <input type="checkbox" name="can_upload" 
                                   <?php echo $user['can_upload'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                        <div>
                            <i class="fas fa-upload"></i>
                            <span>Upload Files</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Previous JavaScript remains the same
            
            // Admin toggle affects permissions
            const adminToggle = document.getElementById('is_admin');
            const permissionCheckboxes = document.querySelectorAll('.permissions-section input[type="checkbox"]');
            
            function updatePermissionFields() {
                if (adminToggle.checked) {
                    // If admin, check all permissions and disable them
                    permissionCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                        checkbox.disabled = true;
                        checkbox.closest('.permission-item').style.opacity = '0.6';
                    });
                } else {
                    // If not admin, enable all permissions
                    permissionCheckboxes.forEach(checkbox => {
                        checkbox.disabled = false;
                        checkbox.closest('.permission-item').style.opacity = '1';
                    });
                }
            }
            
            // Initialize and add event listener
            updatePermissionFields();
            adminToggle.addEventListener('change', updatePermissionFields);
        });
    </script>
</body>
</html> 