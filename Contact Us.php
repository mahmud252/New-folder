<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$formData = [
    'user_id' => '',
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

// Fetch all users for dropdown
$users = [];
$result = $conn->query("SELECT id, CONCAT(name) AS name, email FROM users ORDER BY name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[$row['id']] = $row['name'] . " (" . $row['email'] . ")";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['user_id'] = intval($_POST['user_id'] ?? 0);
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['subject'] = trim($_POST['subject'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = "Invalid form submission. Please try again.";
        http_response_code(400);
    }
    elseif (!$formData['user_id'] || !$formData['name'] || !$formData['email'] || !$formData['subject'] || !$formData['message']) {
        $error = "Please fill in all required fields.";
    }
    elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    elseif (strlen($formData['message']) < 10 || strlen($formData['message']) > 2000) {
        $error = "Your message should be between 10 and 2000 characters.";
    }
    elseif (strlen($formData['name']) > 100) {
        $error = "Name is too long. Max 100 characters allowed.";
    } else {
        try {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'] ?? 
                         explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0] ?? 
                         $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

            $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message, submitted_at, ip_address) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param(
                'isssss',
                $formData['user_id'],
                $formData['name'],
                $formData['email'],
                $formData['subject'],
                $formData['message'],
                $ipAddress
            );

            if ($stmt->execute()) {
                $success = "Message submitted successfully.";
                $formData = ['user_id' => '', 'name' => '', 'email' => '', 'subject' => '', 'message' => ''];
            } else {
                $error = "Error submitting message.";
                error_log("DB error: " . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            $error = "System error occurred.";
            error_log("Exception: " . $e->getMessage());
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Contact our support team for assistance with our File Management System" />
    <title>Contact Us - File Management System</title>
    <style>
        :root {
            --primary-color: #4a6fa5;
            --primary-hover: #3a5a8a;
            --error-color: #dc3545;
            --success-color: #28a745;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --border-color: #ced4da;
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--light-gray);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            animation: slideIn 1s ease-in-out;
        }

        .form-group {
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-in-out;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .required-field::after {
            content: " *";
            color: var(--error-color);
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background-color var(--transition-speed);
            animation: buttonAppear 0.8s ease-out;
        }

        button[type="submit"]:hover {
            background-color: var(--primary-hover);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color var(--transition-speed);
        }

        .back-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .character-count {
            font-size: 0.8em;
            color: #666;
            text-align: right;
            margin-top: -15px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes buttonAppear {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            transition: border-color var(--transition-speed);
            background-color: #fff;
        }

        select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
        }

        option {
            padding: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Contact Us</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="form-group">
            <label for="user_id" class="required-field">Select User</label>
            <select id="user_id" name="user_id" required>
                <option value="">-- Select a User --</option>
                <?php foreach ($users as $id => $label): ?>
                    <option value="<?php echo $id; ?>" <?php echo ($formData['user_id'] == $id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="name" class="required-field">Your Name</label>
            <input type="text" id="name" name="name" required
                   maxlength="100"
                   value="<?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        
        <div class="form-group">
            <label for="email" class="required-field">Email Address</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        
        <div class="form-group">
            <label for="subject" class="required-field">Subject</label>
            <input type="text" id="subject" name="subject" required
                   maxlength="200"
                   value="<?php echo htmlspecialchars($formData['subject'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        
        <div class="form-group">
            <label for="message" class="required-field">Your Message</label>
            <textarea id="message" name="message" required><?php echo htmlspecialchars($formData['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="character-count" id="message-counter">0/2000 characters</div>
        </div>
        
        <div class="form-group">
            <button type="submit">Send Message</button>
        </div>
    </form>

    <a href="index.php" class="back-link">&larr; Back to Home</a>
</div>

<script>
    // Character counter for message textarea
    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('message');
        const counter = document.getElementById('message-counter');
        
        function updateCounter() {
            const length = messageInput.value.length;
            counter.textContent = `${length}/2000 characters`;
            counter.style.color = length > 1900 ? '#dc3545' : '#666';
        }
        
        messageInput.addEventListener('input', updateCounter);
        updateCounter(); // Initialize counter on page load
    });
</script>
</body>
</html>
