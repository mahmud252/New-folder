<?php
// upload.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $uploadDir = 'uploads/';  // Define where the files will be uploaded
        $uploaded = 0;

        // Ensure directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Loop through uploaded files
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $filePath = $uploadDir . $fileName;

            // Move the uploaded file to the desired location
            if (move_uploaded_file($tmpName, $filePath)) {
                $uploaded++;
            }
        }

        echo json_encode([
            'success' => true,
            'uploaded' => $uploaded
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
