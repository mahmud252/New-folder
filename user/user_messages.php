<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Under Construction</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>🚧 Page Under Construction 🚧</h1>
            <p>We are working hard to bring this page to life. It will be available soon.</p>
            <p>Please check back later. Thank you for your patience!</p>
            <div class="spinner"></div>
        </div>
    </div>
</body>
</html>
<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f7f7f7;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    text-align: center;
    max-width: 800px;
    width: 100%;
    padding: 20px;
}

h1 {
    font-size: 2.5em;
    color: #333;
    margin-bottom: 20px;
}

p {
    font-size: 1.2em;
    color: #666;
    margin-bottom: 20px;
}

.spinner {
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin-top: 30px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</style>