<?php
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
    $allowedRoles = ['Admin'];
    $userRoles = isset($_SESSION['roles']) ? $_SESSION['roles'] : [];

    $hasAccess = false;
    foreach ($userRoles as $role) {
        if (in_array($role, $allowedRoles)) {
            $hasAccess = true;
            break;
        }
    }
    if (!$hasAccess) {
        $_SESSION['status'] = "You are not authorized to access this page!";
        $_SESSION['status_code'] = "warning";
        header("Location: ../index.php");
        exit(0);
    }
}
?>
