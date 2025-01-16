<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['auth_user']['userId'])) {
    $_SESSION['status'] = "Unauthorized access!";
    $_SESSION['status_code'] = 'error';
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['auth_user']['userId'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['status'] = "All fields are required.";
        $_SESSION['status_code'] = 'error';
        header("Location: ../profile.php");
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['status'] = "New passwords do not match.";
        $_SESSION['status_code'] = 'error';
        header("Location: ../profile.php");
        exit();
    }

    $passwordPattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*(),.?\":{}|<>])[A-Za-z\d!@#$%^&*(),.?\":{}|<>]{8,}$/";
    if (!preg_match($passwordPattern, $newPassword)) {
        $_SESSION['status'] = "Password does not meet the criteria.";
        $_SESSION['status_code'] = 'error';
        header("Location: ../profile.php");
        exit();
    }

    $query = "SELECT password FROM employee WHERE userId = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $storedPassword = $user['password'];

        if ($currentPassword !== $storedPassword) {
            $_SESSION['status'] = "Current password is incorrect.";
            $_SESSION['status_code'] = 'error';
            header("Location: ../profile.php");
            exit();
        }

        $updateQuery = "UPDATE employee SET password = ? WHERE userId = ?";
        $updateStmt = $con->prepare($updateQuery);
        $updateStmt->bind_param("si", $newPassword, $userId);

        if ($updateStmt->execute()) {
            $_SESSION['status'] = "Password changed successfully.";
            $_SESSION['status_code'] = 'success';
            header("Location: ../profile.php");
            exit();
        } else {
            $_SESSION['status'] = "Failed to update password.";
            $_SESSION['status_code'] = 'error';
            header("Location: ../profile.php");
            exit();
        }
    } else {
        $_SESSION['status'] = "User not found.";
        $_SESSION['status_code'] = 'error';
        header("Location: ../profile.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request.";
    $_SESSION['status_code'] = 'error';
    header("Location: ../profile.php");
    exit();
}
?>
