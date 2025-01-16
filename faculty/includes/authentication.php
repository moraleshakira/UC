<?php
// Start the session only if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('./config/config.php');

if (!isset($_SESSION['auth'])) {
    $_SESSION['status'] = "Login to access the dashboard";
    $_SESSION['status_code'] = "warning";
    header("Location: ../index.php");
    exit(0);
} else {
    $allowedRoles = ['Faculty'];
    $userRoles = isset($_SESSION['roles']) ? $_SESSION['roles'] : [];

    // Check if the user has one of the allowed roles
    $hasAccess = !empty(array_intersect($userRoles, $allowedRoles));

    if (!$hasAccess) {
        $_SESSION['status'] = "You are not authorized to access this page!";
        $_SESSION['status_code'] = "warning";
        header("Location: ../index.php");
        exit(0);
    }
}
?>
