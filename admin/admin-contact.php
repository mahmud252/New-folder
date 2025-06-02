<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Strict admin access control
if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// CSRF protection for actions
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET']) && !empty($_GET['action'])) {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Invalid security token. Please try again.'
        ];
        header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle message actions
if (isset($_GET['action'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    
    if (!$id) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Invalid message ID'
        ];
        header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
    }

    switch ($_GET['action']) {
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => "Message #{$id} has been deleted successfully"
                ];
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => "Failed to delete message #{$id}"
                ];
            }
            $stmt->close();
            break;

        case 'close':
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'closed' WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => "Message #{$id} has been marked as closed"
                ];
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => "Failed to update message #{$id}"
                ];
            }
            $stmt->close();
            break;

        case 'reopen':
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'new' WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => "Message #{$id} has been reopened"
                ];
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => "Failed to update message #{$id}"
                ];
            }
            $stmt->close();
            break;

        default:
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Invalid action'
            ];
    }

    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Pagination setup
$perPage = 15;
$currentPage = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]));
$offset = ($currentPage - 1) * $perPage;

// Status filter
$statusFilter = '';
if (isset($_GET['status']) && in_array($_GET['status'], ['new', 'replied', 'closed'])) {
    $statusFilter = "WHERE status = '" . $conn->real_escape_string($_GET['status']) . "'";
}

// Count messages
$countQuery = "SELECT 
    COUNT(*) AS total,
    SUM(status = 'new') AS new_count,
    SUM(status = 'replied') AS replied_count,
    SUM(status = 'closed') AS closed_count
    FROM contact_messages $statusFilter";

$countResult = $conn->query($countQuery);
$counts = $countResult->fetch_assoc();
$countResult->free();

$totalMessages = (int)($counts['total'] ?? 0);
$newMessages = (int)($counts['new_count'] ?? 0);
$repliedMessages = (int)($counts['replied_count'] ?? 0);
$closedMessages = (int)($counts['closed_count'] ?? 0);

$totalPages = max(1, ceil($totalMessages / $perPage));

// Get messages
$query = "SELECT 
    id, name, email, subject, message, submitted_at, status, ip_address 
    FROM contact_messages 
    $statusFilter 
    ORDER BY submitted_at DESC 
    LIMIT ?, ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $offset, $perPage);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --danger: #ef233c;
            --danger-dark: #d90429;
            --success: #4cc9f0;
            --success-dark: #4895ef;
            --warning: #f8961e;
            --warning-dark: #f3722c;
            --info: #577590;
            --dark: #2b2d42;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 0.375rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f5f7fa;
            padding: 20px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .admin-header {
            background-color: var(--primary);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            font-size: 1.75rem;
            margin: 0;
        }

        .back-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .admin-content {
            padding: 1.5rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1rem;
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: var(--gray);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .count {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card.total .count { color: var(--primary); }
        .stat-card.new .count { color: var(--warning); }
        .stat-card.replied .count { color: var(--success); }
        .stat-card.closed .count { color: var(--gray); }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            background: var(--gray-light);
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .filter-tab:hover {
            background: var(--gray);
            color: white;
        }

        .filter-tab.active {
            background: var(--primary);
            color: white;
        }

        .messages-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .messages-table th {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem;
            text-align: left;
        }

        .messages-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: top;
        }

        .messages-table tr:last-child td {
            border-bottom: none;
        }

        .messages-table tr:hover td {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-new {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning-dark);
        }

        .status-replied {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-dark);
        }

        .status-closed {
            background-color: rgba(108, 117, 125, 0.1);
            color: var(--gray);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: var(--danger-dark);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: var(--success-dark);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: var(--warning-dark);
        }

        .btn-info {
            background-color: var(--info);
            color: white;
        }

        .btn-info:hover {
            background-color: var(--dark);
        }

        .btn-light {
            background-color: var(--gray-light);
            color: var(--dark);
        }

        .message-details {
            display: none;
            padding: 1rem;
            margin-top: 0.5rem;
            background-color: var(--light);
            border-radius: var(--border-radius);
            animation: fadeIn 0.3s ease-out;
        }

        .message-details.visible {
            display: block;
        }

        .message-details p {
            margin-bottom: 0.5rem;
        }

        .message-details strong {
            display: inline-block;
            min-width: 100px;
            color: var(--gray);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notification {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            animation: fadePop 0.5s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification.success {
            background-color: var(--success);
        }

        .notification.error {
            background-color: var(--danger);
        }

        .notification .close-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.25rem;
            line-height: 1;
        }

        @keyframes fadePop {
            0% { opacity: 0; transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--primary);
            border: 1px solid var(--gray-light);
        }

        .pagination a:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .disabled {
            color: var(--gray);
            pointer-events: none;
        }

        .no-messages {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .admin-container {
                border-radius: 0;
            }
            
            .messages-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-envelope"></i> Contact Messages</h1>
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="admin-content">
        <?php if (!empty($_SESSION['notification'])): ?>
            <div class="notification <?php echo htmlspecialchars($_SESSION['notification']['type']); ?>">
                <span><?php echo htmlspecialchars($_SESSION['notification']['message']); ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        <div class="stats-container">
            <div class="stat-card total">
                <h3>Total Messages</h3>
                <div class="count"><?php echo number_format($totalMessages); ?></div>
            </div>
            <div class="stat-card new">
                <h3>New Messages</h3>
                <div class="count"><?php echo number_format($newMessages); ?></div>
            </div>
            <div class="stat-card replied">
                <h3>Replied Messages</h3>
                <div class="count"><?php echo number_format($repliedMessages); ?></div>
            </div>
            <div class="stat-card closed">
                <h3>Closed Messages</h3>
                <div class="count"><?php echo number_format($closedMessages); ?></div>
            </div>
        </div>

        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo empty($_GET['status']) || $_GET['status'] === 'all' ? 'active' : ''; ?>">
                All Messages
            </a>
            <a href="?status=new" class="filter-tab <?php echo ($_GET['status'] ?? '') === 'new' ? 'active' : ''; ?>">
                New
            </a>
            <a href="?status=replied" class="filter-tab <?php echo ($_GET['status'] ?? '') === 'replied' ? 'active' : ''; ?>">
                Replied
            </a>
            <a href="?status=closed" class="filter-tab <?php echo ($_GET['status'] ?? '') === 'closed' ? 'active' : ''; ?>">
                Closed
            </a>
        </div>

        <?php if ($totalMessages === 0): ?>
            <div class="no-messages">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No messages found</h3>
                <p>There are currently no contact messages matching your criteria.</p>
            </div>
        <?php else: ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo (int)$row['id']; ?></td>
                        <td><?php echo date('M j, Y g:i a', strtotime($row['submitted_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>">
                                <?php echo htmlspecialchars($row['email']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-sm" onclick="toggleMessage(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <a href="reply_message.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-reply"></i> Reply
                                </a>
                                <?php if ($row['status'] === 'closed'): ?>
                                    <button class="btn btn-warning btn-sm" 
                                        onclick="reopenMessage(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-undo"></i> Reopen
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-info btn-sm" 
                                        onclick="closeMessage(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-check"></i> Close
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-danger btn-sm" 
                                    onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                            <div id="message-<?php echo $row['id']; ?>" class="message-details">
                                <p><strong>IP Address:</strong> <?php echo htmlspecialchars($row['ip_address']); ?></p>
                                <p><strong>Message:</strong></p>
                                <div style="background: white; padding: 1rem; border-radius: var(--border-radius);">
                                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>&status=<?php echo $_GET['status'] ?? 'all'; ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-left"></i> Previous</span>
                <?php endif; ?>

                <?php 
                // Show page numbers (with ellipsis for many pages)
                $maxPagesToShow = 5;
                $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
                $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                
                if ($startPage > 1) {
                    echo '<a href="?page=1&status=' . ($_GET['status'] ?? 'all') . '">1</a>';
                    if ($startPage > 2) echo '<span>...</span>';
                }
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $_GET['status'] ?? 'all'; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; 
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<span>...</span>';
                    echo '<a href="?page=' . $totalPages . '&status=' . ($_GET['status'] ?? 'all') . '">' . $totalPages . '</a>';
                }
                ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>&status=<?php echo $_GET['status'] ?? 'all'; ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Toggle message details visibility
    function toggleMessage(id) {
        const details = document.getElementById('message-' + id);
        details.classList.toggle('visible');
    }

    // Confirm before deleting
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this message?\nThis action cannot be undone.')) {
            window.location.href = `?action=delete&id=${id}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`;
        }
    }

    // Close message
    function closeMessage(id) {
        if (confirm('Mark this message as closed?')) {
            window.location.href = `?action=close&id=${id}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`;
        }
    }

    // Reopen message
    function reopenMessage(id) {
        if (confirm('Reopen this message?')) {
            window.location.href = `?action=reopen&id=${id}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`;
        }
    }

    // Auto-close notifications after 5 seconds
    document.addEventListener('DOMContentLoaded', () => {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        });
    });
</script>
</body>
</html>

<?php
$result->free();
$stmt->close();
$conn->close();
?><script>
// Function to fetch and update messages
function fetchMessages() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'messages.php', true); // Fetch the messages from the PHP backend
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            const messages = JSON.parse(xhr.responseText);
            updateMessagesTable(messages);
        }
    };
    xhr.send();
}

// Function to update the messages table
function updateMessagesTable(messages) {
    const messagesContainer = document.getElementById('messages-container');
    const noMessagesDiv = document.getElementById('no-messages');
    if (messages.length > 0) {
        noMessagesDiv.style.display = 'none'; // Hide 'no messages' text
        let tableContent = '<table>';
        tableContent += '<thead><tr><th>Subject</th><th>Status</th><th>Your Message</th><th>Admin Reply</th></tr></thead><tbody>';

        messages.forEach(msg => {
            tableContent += `<tr>
                <td>${msg.subject}</td>
                <td><span class="status-badge status-${msg.status}">${msg.status}</span></td>
                <td>${msg.message}</td>
                <td>${msg.admin_reply ? msg.admin_reply : 'No reply yet'}</td>
            </tr>`;
        });

        tableContent += '</tbody></table>';
        messagesContainer.innerHTML = tableContent;
    } else {
        messagesContainer.innerHTML = '';
        noMessagesDiv.style.display = 'block'; // Show 'no messages' text
    }
}

// Initial fetch and set interval for live updates
fetchMessages();
setInterval(fetchMessages, 10000); // Fetch new messages every 10 seconds
</script>
