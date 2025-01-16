<?php
// Include the database configuration file
include '../config/config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $employeeId = $_POST['employeeId'];
    $emailAdress = filter_var(trim($_POST['emailAddress']), FILTER_SANITIZE_EMAIL);
    $roles = $_POST['roles']; // Assuming this is a comma-separated string
    $status = $_POST['status'];

    // Validate email format
    if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Prepare a statement to update user details
    $stmt = $conn->prepare("UPDATE users SET email = ?, role = ?, status = ? WHERE employee_id = ?");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("sssi", $email, $roles, $status, $employee_id);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        // Redirect to the user management page after successful update
        header("Location: ../user.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Invalid request.";
}

// Close the database connection
$conn->close();
?>
