<?php
session_start();
require '../config/config.php';

if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    $deleteQuery = "UPDATE employee SET status = 'Archived' WHERE userId = ?";

    if ($stmt = $con->prepare($deleteQuery)) {
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $_SESSION['status'] = "User archived successfully!";
            $_SESSION['status_code'] = "warning";
        } else {
            $_SESSION['status'] = "Error archiving user: " . $stmt->error;
            $_SESSION['status_code'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['status'] = "Error preparing query.";
        $_SESSION['status_code'] = "error";
    }
} else {
    $_SESSION['status'] = "No user selected for archiving.";
    $_SESSION['status_code'] = "error";
}

// Redirect back to user.php
header("Location: ../user.php");
exit();
?>