<?php
include("../config/config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employeeId'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $phoneNumber = $_POST['phoneNumber'];
    $emailAddress = $_POST['emailAddress'];
    $role = $_POST['role'];

    // Generate a default password based on employeeId and lastName
    $password = $employeeId . '@' . $lastName;

    // Hash the default password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO `employee` (`employeeId`, `firstName`, `middleName`, `lastName`, `password`, `emailAddress`, `role`, `phoneNumber`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $employeeId, $firstName, $middleName, $lastName, $hashedPassword, $emailAddress, $role, $phoneNumber);

    if ($stmt->execute()) {
        $_SESSION['status'] = "User has been added successfully!";
        $_SESSION['status_code'] = "success";
        header('Location: ../admin/user.php');
        exit(0);
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
