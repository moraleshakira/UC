<?php
session_start();
require '../config/config.php';

// Enable error reporting for debugging (Disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Validate `file_id`
if (isset($_GET['file_id']) && is_numeric($_GET['file_id'])) {
    $file_id = intval($_GET['file_id']);

    // Query to fetch file path
    $query = "SELECT `filePath` FROM `itl_extracted_data` WHERE `id` = ?";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        die("Error preparing the query: " . $con->error);
    }

    $stmt->bind_param('i', $file_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($filePath);

    if ($stmt->fetch()) {
        $stmt->close();

        // Ensure the uploads directory path is resolved correctly
        $uploadsDir = realpath('../../uploads/');
        $fullFilePath = $uploadsDir . DIRECTORY_SEPARATOR . basename($filePath);

        if (file_exists($fullFilePath) && strpos($fullFilePath, $uploadsDir) === 0) {
            // Set headers for file download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($fullFilePath));
            header('Pragma: public');

            // Read the file and output its contents
            readfile($fullFilePath);
            exit;
        } else {
            // File not found or invalid path
            $_SESSION['status'] = "File not found or invalid file path.";
            $_SESSION['status_code'] = "error";
        }
    } else {
        // File ID not found in the database
        $_SESSION['status'] = "Invalid file ID.";
        $_SESSION['status_code'] = "error";
        $stmt->close();
    }
} else {
    // Missing or invalid file ID
    $_SESSION['status'] = "Missing or invalid file ID parameter.";
    $_SESSION['status_code'] = "error";
}

// Redirect to the previous page with error message
header('Location: ../f_itl.php');
exit;
