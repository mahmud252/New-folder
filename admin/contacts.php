<?php
// Start session and check admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Database connection (same as before)
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);

// Handle actions (mark as read, replied, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['contact_id'])) {
        $contact_id = (int)$_POST['contact_id'];
        $action = $_POST['action'];
        
        $valid_actions = ['read', 'replied', 'spam', 'delete'];
        if (in_array($action, $valid_actions)) {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
                $stmt->execute([$contact_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
                $stmt->execute([$action, $contact_id]);
            }
        }
        
        if (isset($_POST['admin_notes'])) {
            $notes = filter_input(INPUT_POST, 'admin_notes', FILTER_SANITIZE_STRING);
            $stmt = $pdo->prepare("UPDATE contacts SET admin_notes = ? WHERE id = ?");
            $stmt->execute([$notes, $contact_id]);
        }
    }
}

// Get all contacts
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Contact Messages</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-new { background-color: #ffe6e6; }
        .status-read { background-color: #e6ffe6; }
        .status-replied { background-color: #e6f7ff; }
        .status-spam { background-color: #f0f0f0; }
        .action-form { display: inline; }
        .notes { font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <h1>Contact Messages</h1>
    <p><a href="logout.php">Logout</a></p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $contact): ?>
            <tr class="status-<?php echo $contact['status']; ?>">
                <td><?php echo htmlspecialchars($contact['id']); ?></td>
                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                <td>
                    <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                    <?php if (!empty($contact['admin_notes'])): ?>
                        <div class="notes">
                            <strong>Admin Notes:</strong> <?php echo htmlspecialchars($contact['admin_notes']); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?php echo date('M j, Y g:i a', strtotime($contact['created_at'])); ?></td>
                <td><?php echo ucfirst($contact['status']); ?></td>
                <td>
                    <form class="action-form" method="post">
                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                        <select name="action" onchange="this.form.submit()">
                            <option value="">-- Change Status --</option>
                            <option value="read">Mark as Read</option>
                            <option value="replied">Mark as Replied</option>
                            <option value="spam">Mark as Spam</option>
                            <option value="delete">Delete</option>
                        </select>
                    </form>
                    
                    <form class="action-form" method="post">
                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                        <input type="text" name="admin_notes" placeholder="Add notes..." 
                               value="<?php echo htmlspecialchars($contact['admin_notes'] ?? ''); ?>">
                        <button type="submit">Save Notes</button>
                    </form>
                    
                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>?subject=Re: <?php echo htmlspecialchars($contact['subject']); ?>">
                        Reply
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>