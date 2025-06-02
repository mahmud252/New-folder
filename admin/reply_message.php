<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: contact_messages.php');
    exit();
}

// Fetch message
$stmt = $conn->prepare("SELECT name, email, subject, message, status, admin_reply FROM contact_messages WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();
$stmt->close();

if (!$message) {
    header('Location: contact_messages.php');
    exit();
}

$errors = [];
$success = false;
$replyBody = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $replyBody = trim($_POST['reply_body'] ?? '');

    if (empty($replyBody)) {
        $errors[] = "Reply message body is required.";
    }

    if (empty($errors)) {
        $update = $conn->prepare("UPDATE contact_messages SET status = 'replied', admin_reply = ? WHERE id = ?");
        $update->bind_param('si', $replyBody, $id);
        $update->execute();
        $update->close();
        $success = true;
        $message['admin_reply'] = $replyBody;
        $message['status'] = 'replied';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message #<?php echo $id; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 750px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-out;
        }

        h2 {
            color: #333;
            margin-top: 0;
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
        }

        label {
            font-weight: bold;
            margin-top: 20px;
            display: block;
            font-size: 1.1rem;
        }

        textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
        }

        button {
            margin-top: 15px;
            padding: 12px 18px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            animation: slideIn 0.5s ease-out;
        }

        .error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            animation: slideIn 0.5s ease-out;
        }

        .message-box {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.95rem;
            color: #555;
        }

        a {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            font-size: 1rem;
            transition: color 0.3s;
        }

        a:hover {
            color: #0056b3;
        }

        ul {
            margin: 0;
            padding-left: 20px;
        }

        /* Keyframes for Animations */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideIn {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Reply to Message</h2>

    <p><strong>From:</strong> <?php echo htmlspecialchars($message['name']) . " &lt;" . htmlspecialchars($message['email']) . "&gt;"; ?></p>
    <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?></p>
    <p><strong>Message:</strong></p>
    <div class="message-box">
        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
    </div>

    <?php if ($success): ?>
        <div class="success">Reply saved successfully.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($message['status'] !== 'replied'): ?>
        <form method="post">
            <label for="reply_body">Reply Message</label>
            <textarea name="reply_body" id="reply_body" rows="6" required><?php echo htmlspecialchars($replyBody); ?></textarea>
            <button type="submit">Save Reply</button>
        </form>
    <?php else: ?>
        <h3>Admin Reply:</h3>
        <div class="message-box">
            <?php echo nl2br(htmlspecialchars($message['admin_reply'])); ?>
        </div>
    <?php endif; ?>

    <a href="admin-contact.php">← Back to messages</a>
</div>

</body>
</html>

<?php $conn->close(); ?>
