<?php
include('./includes/authentication.php');
include('./config/config.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userId'])) {
    $userId = $_POST['userId'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $department = isset($_POST['department']) ? $_POST['department'] : null;
    $status = $_POST['status']; 

    $updateQuery = "UPDATE employee 
                    SET firstName = ?, middleName = ?, lastName = ?, emailAddress = ?, 
                        phoneNumber = ?, status = ? 
                    WHERE userId = ?";

    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param('ssssssi', $firstName, $middleName, $lastName, $email, $phone, $status, $userId);

    if ($stmt->execute()) {

        $updateRoleQuery = "DELETE FROM employee_role WHERE userId = ?";
        $stmtRole = $con->prepare($updateRoleQuery);
        $stmtRole->bind_param('i', $userId);
        $stmtRole->execute();

        $insertRoleQuery = "INSERT INTO employee_role (userId, role_id) VALUES (?, ?)";
        $stmtRoleInsert = $con->prepare($insertRoleQuery);
        $stmtRoleInsert->bind_param('ii', $userId, $role);
        $stmtRoleInsert->execute();

        if ($role == '2' && $department) { 
            $updateDepartmentQuery = "UPDATE employee SET department = ? WHERE userId = ?";
            $stmtDept = $con->prepare($updateDepartmentQuery);
            $stmtDept->bind_param('ii', $department, $userId);
            $stmtDept->execute();
        }

        $_SESSION['status'] = 'User updated successfully.';
        $_SESSION['status_code'] = 'success';

        header("Location: user.php");
        exit;
    } else {

        $_SESSION['status'] = 'Failed to update user.';
        $_SESSION['status_code'] = 'error';

        header("Location: user.php");
        exit;
    }
}
?>
