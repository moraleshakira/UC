<?php
session_start();
require '../config/config.php';

// Enable error reporting for debugging (Disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if a valid `file_id` is passed
if (isset($_GET['file_id']) && is_numeric($_GET['file_id'])) {
    $file_id = intval($_GET['file_id']);

    $query = "SELECT  `filePath` FROM `dtr_extracted_data` WHERE `id` = ?";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        die("Error preparing the query: " . $con->error);
    }

    $stmt->bind_param('i', $file_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($filePath);

    if ($stmt->fetch()) {
        $uploadsDir = realpath('../../uploads/');
        $fullFilePath = $uploadsDir . DIRECTORY_SEPARATOR . $filePath;

        if (file_exists($fullFilePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; ');
            header('Content-Length: ' . filesize($fullFilePath));
            readfile($fullFilePath);
            exit;
        } else {
            $_SESSION['status'] = "File not found on the server.";
            $_SESSION['status_code'] = "error";
        }
    } else {
        $_SESSION['status'] = "File ID not found in the database.";
        $_SESSION['status_code'] = "error";
    }
} else {
    $_SESSION['status'] = "Missing or invalid file ID parameter.";
    $_SESSION['status_code'] = "error";
}

header('Location: ../f_dtr.php');
exit;
