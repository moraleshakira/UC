<?php
session_start();
require '../config/config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if a file_id is passed
if (isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    // Prepare the query to fetch the file name and path
    $query = "SELECT `fileName`, `filePath` FROM `itl_extracted_data` WHERE `id` = ?";
    $stmt = $con->prepare($query);

    // Check if the prepare was successful
    if (!$stmt) {
        die("Error preparing the query: " . $con->error);
    }

    // Bind parameters and execute the query
    $stmt->bind_param('i', $file_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($fileName, $filePath);

    // Check if the file exists in the database
    if ($stmt->fetch()) {
        // Define the full file path (using absolute path)
        $uploadsDir = realpath('../../uploads/');
        $fullFilePath = $uploadsDir . DIRECTORY_SEPARATOR . $filePath;

        // Debugging: Show the resolved full file path
        echo "Resolved Full File Path: " . $fullFilePath . "<br>";

        // Check if the file exists at the specified location
        if (file_exists($fullFilePath)) {
            // Set the appropriate headers to force the download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // Correct MIME type for .xlsx files
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            header('Content-Length: ' . filesize($fullFilePath));

            // Read the file and send it to the output buffer
            readfile($fullFilePath);
            exit;
        } else {
            // If file doesn't exist, show an error message
            $_SESSION['status'] = "File not found at: " . $fullFilePath;
            $_SESSION['status_code'] = "error";
            header('Location: ../itl.php');
            exit;
        }
    } else {
        // If no file found in the database, show an error message
        $_SESSION['status'] = "Invalid file ID";
        $_SESSION['status_code'] = "error";
        header('Location: ../itl.php');
        exit;
    }
} else {
    // If no valid file_id is provided, redirect back with an error message
    $_SESSION['status'] = "Invalid request";
    $_SESSION['status_code'] = "error";
    header('Location: ../itl.php');
    exit;
}
