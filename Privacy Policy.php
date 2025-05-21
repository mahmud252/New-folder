<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - File Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --medium-gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: var(--light-color);
        }

        .privacy-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .privacy-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .privacy-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .privacy-header p {
            color: var(--medium-gray);
            font-size: 1.1rem;
        }

        .privacy-content {
            margin-bottom: 2rem;
        }

        .privacy-section {
            margin-bottom: 2.5rem;
        }

        .privacy-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .privacy-section h3 {
            font-size: 1.2rem;
            margin: 1.5rem 0 0.8rem;
            color: var(--dark-color);
        }

        .privacy-section p, .privacy-section ul {
            margin-bottom: 1rem;
            color: var(--dark-color);
            line-height: 1.7;
        }

        .privacy-section ul {
            padding-left: 2rem;
        }

        .privacy-section li {
            margin-bottom: 0.5rem;
        }

        .contact-info {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
        }

        .last-updated {
            text-align: right;
            font-style: italic;
            color: var(--medium-gray);
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .privacy-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .privacy-header h1 {
                font-size: 2rem;
            }
        }

        /* Dark Mode */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #121212;
                color: #e0e0e0;
            }

            .privacy-container {
                background-color: #1e1e1e;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }

            .privacy-header h1 {
                color: var(--accent-color);
            }

            .privacy-section h2, .privacy-section h3 {
                color: #ffffff;
            }

            .privacy-section p, .privacy-section ul, .privacy-section li {
                color: #b0b0b0;
            }

            .contact-info {
                background-color: #252525;
            }

            .privacy-header p, .last-updated {
                color: #a0a0a0;
            }
        }

        
        /* Footer */
        .footer {
            margin-top: 4rem;
            color: var(--medium-gray);
            font-size: 0.95rem;
            width: 100%;
            padding-top: 2rem;
            border-top: 1px solid var(--light-gray);
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .footer-links a {
            color: var(--medium-gray);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="privacy-container">
        <div class="privacy-header">
            <h1><i class="fas fa-shield-alt"></i> Privacy Policy</h1>
            <p>Last Updated: <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="privacy-content">
            <div class="privacy-section">
                <h2>1. Introduction</h2>
                <p>Welcome to the File Management System ("we," "our," or "us"). We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our file management services.</p>
                <p>By accessing or using our service, you agree to the collection and use of information in accordance with this policy.</p>
            </div>

            <div class="privacy-section">
                <h2>2. Information We Collect</h2>
                
                <h3>Personal Information</h3>
                <p>When you register an account or use our services, we may collect:</p>
                <ul>
                    <li>Name, email address, and contact details</li>
                    <li>Username and password</li>
                    <li>Payment information (for premium services)</li>
                    <li>Profile picture (if uploaded)</li>
                </ul>

                <h3>Usage Data</h3>
                <p>We automatically collect information about how you interact with our services:</p>
                <ul>
                    <li>IP address and device information</li>
                    <li>Browser type and version</li>
                    <li>Pages visited and time spent on our service</li>
                    <li>File access and modification activities</li>
                </ul>

                <h3>File Content</h3>
                <p>While we store your files, we do not access their content except when:</p>
                <ul>
                    <li>Required to provide the service (e.g., generating previews)</li>
                    <li>Necessary for security or legal compliance</li>
                    <li>You explicitly grant permission for support purposes</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>3. How We Use Your Information</h2>
                <p>We use the collected information for various purposes:</p>
                <ul>
                    <li>To provide and maintain our service</li>
                    <li>To authenticate users and secure accounts</li>
                    <li>To process transactions (for premium services)</li>
                    <li>To improve our services and develop new features</li>
                    <li>To monitor usage patterns and detect abuse</li>
                    <li>To communicate with you about updates and security alerts</li>
                    <li>To comply with legal obligations</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>4. Data Storage and Security</h2>
                <p>We implement industry-standard security measures to protect your data:</p>
                <ul>
                    <li>All file transfers use SSL/TLS encryption</li>
                    <li>Passwords are hashed using bcrypt algorithm</li>
                    <li>Regular security audits and vulnerability testing</li>
                    <li>Access controls and authentication protocols</li>
                </ul>
                <p>Despite these measures, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p>
            </div>

            <div class="privacy-section">
                <h2>5. Data Retention</h2>
                <p>We retain your personal information only for as long as necessary:</p>
                <ul>
                    <li>Account data: Until you request deletion</li>
                    <li>File content: Until deleted by user or after account termination</li>
                    <li>Activity logs: 90 days for security monitoring</li>
                    <li>Backup data: Encrypted backups retained for 30 days</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>6. Your Data Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Access and receive a copy of your personal data</li>
                    <li>Request correction of inaccurate information</li>
                    <li>Request deletion of your personal data</li>
                    <li>Object to or restrict processing of your data</li>
                    <li>Withdraw consent (where applicable)</li>
                    <li>Lodge a complaint with a data protection authority</li>
                </ul>
                <p>To exercise these rights, please contact us using the information below.</p>
            </div>

            <div class="privacy-section">
                <h2>7. Third-Party Services</h2>
                <p>We may employ third-party companies for:</p>
                <ul>
                    <li>Payment processing (Stripe, PayPal)</li>
                    <li>Cloud storage infrastructure (AWS, Google Cloud)</li>
                    <li>Analytics and performance monitoring</li>
                    <li>Customer support services</li>
                </ul>
                <p>These third parties have access only to the information needed to perform their functions and are obligated to maintain confidentiality.</p>
            </div>

            <div class="privacy-section">
                <h2>8. Cookies and Tracking</h2>
                <p>We use cookies and similar tracking technologies to:</p>
                <ul>
                    <li>Maintain user sessions</li>
                    <li>Remember preferences</li>
                    <li>Analyze service usage</li>
                </ul>
                <p>You can instruct your browser to refuse all cookies or indicate when a cookie is being sent.</p>
            </div>

            <div class="privacy-section">
                <h2>9. Children's Privacy</h2>
                <p>Our service is not intended for users under 13 years of age. We do not knowingly collect personal information from children under 13. If we become aware of such collection, we will take steps to remove that information.</p>
            </div>

            <div class="privacy-section">
                <h2>10. Changes to This Policy</h2>
                <p>We may update our Privacy Policy periodically. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.</p>
                <p>You are advised to review this Privacy Policy regularly for any changes.</p>
            </div>

            <div class="footer">
            <p>© <?php echo date('Y'); ?> File Management System. All rights reserved.</p>
            <div class="footer-links">
                <a href="index.php">HOme Page</a>
                <a href="Terms of Service.php">Terms of Service</a>
                <a href="#">Contact Us</a>
                <a href="#">Support</a>
        </div>
    </div>
</body>
</html>