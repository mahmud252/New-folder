<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect to appropriate dashboard
    header("Location: " . (isAdmin() ? "admin/dashboard.php" : "user/dashboard.php"));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure file management system for organizing and sharing your documents, images, and videos">
    <title>Welcome to File Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        /* Modern CSS Reset and Base Styles */
        :root {
            --primary-color: #4361ee;
            --primary-light: #5e72e4;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --lighter-gray: #e9ecef;
            --light-gray: #dee2e6;
            --medium-gray: #adb5bd;
            --dark-color: #212529;
            --darker-color: #1a1a1a;
            --success-color: #4bb543;
            --warning-color: #fca311;
            --danger-color: #ef233c;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --box-shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --transition-fast: all 0.15s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: var(--light-color);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Landing Page Container */
        .landing-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 2rem 1rem;
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .logo {
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            margin-bottom: 1rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            animation: pulse 2s infinite;
        }

        .logo-icon:hover {
            transform: scale(1.1);
            animation: none;
        }

        .logo-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .landing-container h1 {
            font-size: 2.75rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            animation: fadeInUp 0.8s ease-out;
        }

        .landing-container .subtitle {
            font-size: 1.25rem;
            color: var(--medium-gray);
            margin-bottom: 2.5rem;
            max-width: 700px;
            line-height: 1.8;
            font-weight: 400;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        /* Feature Cards */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
            width: 100%;
            max-width: 1200px;
        }

        .feature-card {
            background-color: white;
            padding: 2.5rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            text-align: center;
            border: 1px solid var(--light-gray);
            position: relative;
            overflow: hidden;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .feature-card:hover {
            transform: translateY(-10px) !important;
            box-shadow: var(--box-shadow-lg);
            border-color: var(--primary-light);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: var(--transition);
        }

        .feature-card:hover i {
            transform: scale(1.1);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }

        .feature-card p {
            font-size: 1.05rem;
            color: var(--medium-gray);
            margin-bottom: 0;
            line-height: 1.7;
        }

        /* Media Preview Section */
        .media-section {
            width: 100%;
            max-width: 1200px;
            margin: 4rem 0;
            text-align: center;
        }

        .section-header {
            margin-bottom: 2.5rem;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-header p {
            font-size: 1.1rem;
            color: var(--medium-gray);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .media-preview {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            border: 1px solid var(--light-gray);
        }

        .media-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .media-item {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            position: relative;
            transition: var(--transition);
            border: 1px solid var(--light-gray);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .media-item:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            border-color: var(--primary-light);
        }

        .media-item img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            transition: var(--transition);
        }

        .media-item:hover img {
            transform: scale(1.03);
        }

        .media-item video {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            background-color: #f0f0f0;
        }

        .media-info {
            padding: 1.25rem;
            text-align: left;
            background: white;
        }

        .media-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }

        .media-item:hover .media-info h4 {
            color: var(--primary-color);
        }

        .media-info p {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Testimonials */
        .testimonials {
            width: 100%;
            max-width: 1200px;
            margin: 4rem 0;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid var(--light-gray);
            text-align: left;
            transition: var(--transition);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .testimonial-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: var(--box-shadow-lg);
        }

        .testimonial-content {
            font-style: italic;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            line-height: 1.7;
            position: relative;
        }

        .testimonial-content::before {
            content: '"';
            font-size: 4rem;
            color: rgba(67, 97, 238, 0.1);
            position: absolute;
            top: -1.5rem;
            left: -1rem;
            font-family: serif;
            line-height: 1;
            z-index: 0;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-gray);
            transition: var(--transition);
        }

        .testimonial-card:hover .author-avatar {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }

        .author-info h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .author-info p {
            font-size: 0.85rem;
            color: var(--medium-gray);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            margin: 3rem 0;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .btn {
            padding: 0.9rem 2.5rem;
            font-size: 1.05rem;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            min-width: 180px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            transition: var(--transition);
        }

        .btn-primary:hover::after {
            left: 100%;
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Footer */
        .footer {
            margin-top: 4rem;
            color: var(--medium-gray);
            font-size: 0.95rem;
            width: 100%;
            padding-top: 2rem;
            border-top: 1px solid var(--light-gray);
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--medium-gray);
            text-decoration: none;
            transition: var(--transition);
            padding: 0.5rem;
            position: relative;
        }

        .footer-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        .footer-links a:hover::after {
            width: 100%;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Staggered animations */
        .feature-card:nth-child(1) { animation-delay: 0.4s; }
        .feature-card:nth-child(2) { animation-delay: 0.6s; }
        .feature-card:nth-child(3) { animation-delay: 0.8s; }
        .media-item:nth-child(1) { animation-delay: 0.5s; }
        .media-item:nth-child(2) { animation-delay: 0.7s; }
        .media-item:nth-child(3) { animation-delay: 0.9s; }
        .testimonial-card:nth-child(1) { animation-delay: 0.6s; }
        .testimonial-card:nth-child(2) { animation-delay: 0.8s; }
        .testimonial-card:nth-child(3) { animation-delay: 1s; }

        /* Floating animation for logo */
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .logo-icon {
            animation: logoFloat 4s ease-in-out infinite;
        }

        /* Mobile Responsive Design - Enhanced */
        @media (max-width: 768px) {
            .landing-container {
                padding: 1.25rem 0.75rem;
            }

            .logo-icon {
                width: 70px;
                height: 70px;
            }

            .logo-icon i {
                font-size: 2rem;
            }

            .landing-container h1 {
                font-size: 2rem;
                line-height: 1.3;
                padding: 0 0.5rem;
            }

            .landing-container .subtitle {
                font-size: 1rem;
                margin-bottom: 1.75rem;
                padding: 0 0.5rem;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 1.25rem;
                margin-bottom: 3rem;
            }

            .feature-card {
                padding: 1.75rem 1.25rem;
                margin: 0 0.5rem;
            }

            .feature-card i {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            .feature-card h3 {
                font-size: 1.3rem;
            }

            .feature-card p {
                font-size: 0.95rem;
            }

            .media-section,
            .testimonials {
                margin: 3rem 0;
            }

            .section-header {
                margin-bottom: 1.75rem;
                padding: 0 0.5rem;
            }

            .section-header h2 {
                font-size: 1.5rem;
                margin-bottom: 0.75rem;
            }

            .section-header p {
                font-size: 0.95rem;
            }

            .media-content {
                grid-template-columns: 1fr;
                gap: 1.25rem;
                padding: 1.25rem;
            }

            .media-item {
                margin: 0 0.5rem;
            }

            .media-item img,
            .media-item video {
                height: 180px;
            }

            .media-info {
                padding: 1rem;
            }

            .testimonial-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
                padding: 0 0.5rem;
            }

            .testimonial-card {
                padding: 1.5rem;
                margin: 0 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 1rem;
                margin: 2.5rem 0;
                width: 100%;
                padding: 0 0.5rem;
            }

            .btn {
                width: 100%;
                padding: 0.85rem;
                font-size: 1rem;
                min-height: 44px;
            }

            .footer {
                margin-top: 3rem;
                padding: 1.5rem 0.5rem 0;
            }

            .footer-links {
                flex-wrap: wrap;
                gap: 1rem;
                padding: 0 0.5rem;
            }

            /* Adjust dark mode for mobile */
            @media (prefers-color-scheme: dark) {
                .feature-card,
                .media-preview,
                .testimonial-card {
                    background-color: #2a2a2a;
                }
                
                .media-item video {
                    background-color: #3a3a3a;
                }
            }
        }

        /* Very small devices (phones, 400px and down) */
        @media (max-width: 400px) {
            .landing-container h1 {
                font-size: 1.75rem;
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
            }
            
            .feature-card {
                padding: 1.5rem 1rem;
            }
            
            .media-item img,
            .media-item video {
                height: 160px;
            }
            
            .footer-links {
                gap: 0.75rem;
            }
            
            .footer-links a {
                font-size: 0.85rem;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: var(--darker-color);
                color: #e0e0e0;
            }

            .landing-container {
                background: linear-gradient(135deg, #121212 0%, #1e1e1e 100%);
            }

            .landing-container h1 {
                color: #ffffff;
            }

            .landing-container .subtitle,
            .section-header p {
                color: #a0a0a0;
            }

            .feature-card,
            .media-preview,
            .testimonial-card {
                background-color: #252525;
                border-color: #333;
            }

            .feature-card h3,
            .media-info h4,
            .testimonial-content {
                color: #ffffff;
            }

            .feature-card p,
            .media-info p {
                color: #a0a0a0;
            }

            .btn-secondary {
                background-color: #252525;
                color: var(--accent-color);
                border-color: var(--accent-color);
            }

            .media-item video {
                background-color: #333;
            }

            .footer {
                border-color: #333;
            }
        }

        /* Scroll reveal animation */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h1>File Management System</h1>
        </div>
        
        <p class="subtitle">A secure and intuitive platform to manage, organize, and share your files. Perfect for individuals and teams to collaborate efficiently.</p>
        
        <div class="action-buttons">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Get Started
            </a>
            <a href="register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </div>

        <div class="features">
            <div class="feature-card">
                <i class="fas fa-folder-open"></i>
                <h3>Smart File Storage</h3>
                <p>Organize files with tags, categories, and smart search. Automatic file type detection with customizable storage options.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-images"></i>
                <h3>Advanced Media Gallery</h3>
                <p>Beautiful gallery view for images with EXIF data display. Create albums, slideshows, and share with privacy controls.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-film"></i>
                <h3>Video Streaming</h3>
                <p>Upload and stream videos in 4K quality with adaptive bitrate. Supports subtitles, chapters, and playback speed control.</p>
            </div>
        </div>

        <!-- Media Preview Section -->
        <div class="media-section">
            <div class="section-header">
                <h2><i class="fas fa-photo-video"></i> Media Management</h2>
                <p>Experience seamless media handling with previews, metadata extraction, and powerful organization tools.</p>
            </div>
            
            <div class="media-preview">
                <div class="media-content">
                    <div class="media-item">
                        <img src="https://source.unsplash.com/random/800x600?nature" alt="Nature Image" loading="lazy">
                        <div class="media-info">
                            <h4>Nature Photography</h4>
                            <p><i class="fas fa-image"></i> JPEG - 2.4MB</p>
                        </div>
                    </div>
                    <div class="media-item">
                        <video controls poster="https://source.unsplash.com/random/800x600?water" preload="none">
                            <source src="https://samplelib.com/lib/preview/mp4/sample-5s.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="media-info">
                            <h4>Sample Video</h4>
                            <p><i class="fas fa-video"></i> MP4 - 15.2MB</p>
                        </div>
                    </div>
                    <div class="media-item">
                        <img src="https://source.unsplash.com/random/800x600?architecture" alt="City Image" loading="lazy">
                        <div class="media-info">
                            <h4>Urban Architecture</h4>
                            <p><i class="fas fa-image"></i> PNG - 3.1MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials Section -->
        <div class="testimonials">
            <div class="section-header">
                <h2><i class="fas fa-quote-left"></i> Trusted by Thousands</h2>
                <p>What our users say about our file management system</p>
            </div>
            
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "This system has transformed how our team collaborates. The media previews and organization features save us hours every week."
                    </div>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson" class="author-avatar" loading="lazy">
                        <div class="author-info">
                            <h5>Sarah Johnson</h5>
                            <p>Marketing Director</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "As a photographer, I need reliable storage with great previews. This system delivers on all fronts with excellent performance."
                    </div>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Michael Chen" class="author-avatar" loading="lazy">
                        <div class="author-info">
                            <h5>Michael Chen</h5>
                            <p>Professional Photographer</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "The video streaming capabilities are impressive. Our training videos load quickly and play smoothly on all devices."
                    </div>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Emma Rodriguez" class="author-avatar" loading="lazy">
                        <div class="author-info">
                            <h5>Emma Rodriguez</h5>
                            <p>HR Manager</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>© <?php echo date('Y'); ?> File Management System. All rights reserved.</p>
            <div class="footer-links">
                <a href="Privacy Policy.php">Privacy Policy</a>
                <a href="Terms of Service.php">Terms of Service</a>
                <a href="contact-us.php">Contact Us</a>
                <a href="support.php">Support</a>
            </div>
        </div>
    </div>

    <script>
        // Scroll reveal animation
        function revealOnScroll() {
            const reveals = document.querySelectorAll('.reveal');
            
            for (let i = 0; i < reveals.length; i++) {
                const windowHeight = window.innerHeight;
                const elementTop = reveals[i].getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add('active');
                } else {
                    reveals[i].classList.remove('active');
                }
            }
        }

        // Add scroll event listener
        window.addEventListener('scroll', revealOnScroll);

        // Initialize scroll reveal on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add reveal class to all animated elements
            const animatedElements = document.querySelectorAll('.feature-card, .media-item, .testimonial-card');
            animatedElements.forEach(el => {
                el.classList.add('reveal');
            });

            // Trigger initial check
            revealOnScroll();

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>