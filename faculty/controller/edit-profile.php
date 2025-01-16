<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['auth_user']['userId'];

    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $middleName = isset($_POST['middleName']) ? trim($_POST['middleName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $phoneNumber = isset($_POST['phoneNumber']) ? trim($_POST['phoneNumber']) : '';
    $emailAddress = isset($_POST['emailAddress']) ? trim($_POST['emailAddress']) : '';

    $profilePicture = null;
    $isNewImageUploaded = false;

    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profilePicture']['tmp_name'];
        $fileType = mime_content_type($fileTmpPath);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($fileType, $allowedTypes)) {
            $profilePicture = file_get_contents($fileTmpPath);
            $isNewImageUploaded = true;
        } else {
            $_SESSION['status'] = 'Invalid file type. Please upload a valid image.';
            $_SESSION['status_code'] = 'error';
            header('Location: ../profile.php');
            exit;
        }
    }

    if ($isNewImageUploaded) {
        $query = "
            UPDATE `employee` 
            SET 
                `firstName` = ?, 
                `middleName` = ?, 
                `lastName` = ?, 
                `phoneNumber` = ?, 
                `emailAddress` = ?, 
                `profilePicture` = ?
            WHERE 
                `userId` = ?
        ";

        $stmt = $con->prepare($query);
        $stmt->bind_param(
            'ssssssi',
            $firstName,
            $middleName,
            $lastName,
            $phoneNumber,
            $emailAddress,
            $profilePicture,
            $userId
        );
    } else {
        $query = "
            UPDATE `employee` 
            SET 
                `firstName` = ?, 
                `middleName` = ?, 
                `lastName` = ?, 
                `phoneNumber` = ?, 
                `emailAddress` = ?
            WHERE 
                `userId` = ?
        ";

        $stmt = $con->prepare($query);
        $stmt->bind_param(
            'sssssi',
            $firstName,
            $middleName,
            $lastName,
            $phoneNumber,
            $emailAddress,
            $userId
        );
    }

    if ($stmt->execute()) {
        $_SESSION['status'] = 'Profile updated successfully!';
        $_SESSION['status_code'] = 'success';
    } else {
        $_SESSION['status'] = 'Error updating profile: ' . $stmt->error;
        $_SESSION['status_code'] = 'error';
    }

    $stmt->close();
    $con->close();

    header('Location: ../profile.php');
    exit;
} else {
    $_SESSION['status'] = 'Invalid request.';
    $_SESSION['status_code'] = 'error';
    header('Location: ../profile.php');
    exit;
}
