<?php


// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$searchQuery = '';
$searchResults = [];
$error = '';
$searchPerformed = false;

// Process search form submission
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
    $searchQuery = trim($_GET['query']);
    $searchPerformed = true;
    
    if (!empty($searchQuery)) {
        try {
            // Prepare SQL query with LIKE for partial matches
            $stmt = $pdo->prepare("
                SELECT f.id, f.filename, f.filetype, f.filesize, f.upload_date, 
                       u.username as uploader, f.description, f.tags
                FROM files f
                JOIN users u ON f.user_id = u.id
                WHERE (f.filename LIKE :query 
                      OR f.description LIKE :query 
                      OR f.tags LIKE :query)
                      AND (f.visibility = 'public' OR f.user_id = :user_id)
                ORDER BY f.upload_date DESC
            ");
            
            $searchParam = "%$searchQuery%";
            $stmt->bindParam(':query', $searchParam);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($searchResults)) {
                $error = 'No results found for your search query.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter a search term.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Files - File Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            --border-radius: 10px;
            --border-radius-sm: 6px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --box-shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .search-container {
            margin-bottom: 2.5rem;
            animation: fadeIn 0.5s ease-out;
        }

        .search-form {
            display: flex;
            gap: 0.75rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 0.9rem 1.25rem;
            font-size: 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            background-color: white;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .search-btn {
            padding: 0 1.75rem;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--box-shadow);
        }

        .search-btn:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: var(--box-shadow-lg);
        }

        .search-results {
            animation: fadeIn 0.6s ease-out;
        }

        .results-count {
            margin-bottom: 1.5rem;
            color: var(--medium-gray);
            font-size: 0.95rem;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .file-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--light-gray);
        }

        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-lg);
            border-color: var(--primary-light);
        }

        .file-preview {
            height: 180px;
            background-color: var(--lighter-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .file-preview i {
            font-size: 3rem;
            color: var(--medium-gray);
        }

        .file-info {
            padding: 1.25rem;
        }

        .file-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: var(--medium-gray);
        }

        .file-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .file-desc {
            color: var(--dark-color);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .file-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .tag {
            background-color: var(--lighter-gray);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            color: var(--dark-color);
        }

        .file-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--light-gray);
        }

        .action-btn {
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .download-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .download-btn:hover {
            background-color: var(--secondary-color);
        }

        .view-btn {
            background-color: var(--light-gray);
            color: var(--dark-color);
        }

        .view-btn:hover {
            background-color: var(--medium-gray);
            color: white;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--medium-gray);
            animation: fadeIn 0.5s ease-out;
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--light-gray);
        }

        .error-message {
            color: var(--danger-color);
            background-color: rgba(239, 35, 60, 0.1);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Dark Mode */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: var(--darker-color);
                color: #e0e0e0;
            }

            .search-input,
            .file-card {
                background-color: #252525;
                border-color: #333;
            }

            .file-name,
            .file-desc {
                color: #ffffff;
            }

            .file-preview {
                background-color: #333;
            }

            .file-preview i {
                color: #555;
            }

            .view-btn {
                background-color: #333;
                color: #e0e0e0;
            }

            .tag {
                background-color: #333;
                color: #e0e0e0;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .search-form {
                flex-direction: column;
                gap: 0.75rem;
            }

            .search-btn {
                padding: 0.75rem;
                justify-content: center;
            }

            .results-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1rem;
            }

            .results-grid {
                grid-template-columns: 1fr;
            }

            .file-preview {
                height: 160px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1 class="header-title">Search Files</h1>
            <nav>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </nav>
        </header>

        <div class="search-container">
            <form action="search.php" method="GET" class="search-form">
                <input 
                    type="text" 
                    name="query" 
                    class="search-input" 
                    placeholder="Search by filename, description, or tags..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                    required
                >
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($searchPerformed && empty($error)): ?>
            <div class="search-results">
                <div class="results-count">
                    Found <?php echo count($searchResults); ?> results for "<?php echo htmlspecialchars($searchQuery); ?>"
                </div>

                <?php if (!empty($searchResults)): ?>
                    <div class="results-grid">
                        <?php foreach ($searchResults as $file): ?>
                            <div class="file-card">
                                <div class="file-preview">
                                    <?php if (strpos($file['filetype'], 'image/') === 0): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($file['filename']); ?>" alt="<?php echo htmlspecialchars($file['filename']); ?>">
                                    <?php elseif (strpos($file['filetype'], 'video/') === 0): ?>
                                        <i class="fas fa-film"></i>
                                    <?php elseif (strpos($file['filetype'], 'audio/') === 0): ?>
                                        <i class="fas fa-music"></i>
                                    <?php elseif (strpos($file['filetype'], 'application/pdf') === 0): ?>
                                        <i class="fas fa-file-pdf"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file-alt"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="file-info">
                                    <h3 class="file-name" title="<?php echo htmlspecialchars($file['filename']); ?>">
                                        <?php echo htmlspecialchars($file['filename']); ?>
                                    </h3>
                                    <div class="file-meta">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($file['uploader']); ?></span>
                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($file['upload_date'])); ?></span>
                                        <span><i class="fas fa-file"></i> <?php echo formatFileSize($file['filesize']); ?></span>
                                    </div>
                                    <?php if (!empty($file['description'])): ?>
                                        <p class="file-desc"><?php echo htmlspecialchars($file['description']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($file['tags'])): ?>
                                        <div class="file-tags">
                                            <?php 
                                                $tags = explode(',', $file['tags']);
                                                foreach ($tags as $tag): 
                                                    if (!empty(trim($tag))):
                                            ?>
                                                <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                            <?php 
                                                    endif;
                                                endforeach; 
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="file-actions">
                                        <a href="view.php?id=<?php echo $file['id']; ?>" class="action-btn view-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="download.php?id=<?php echo $file['id']; ?>" class="action-btn download-btn">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No files found</h3>
                        <p>Try different search terms or check your spelling</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (!$searchPerformed): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Search for files</h3>
                <p>Enter keywords in the search box above to find your files</p>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Helper function to format file size
    function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }
    ?>
</body>
</html>