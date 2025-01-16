<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['userId'];
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $phoneNumber = $_POST['phoneNumber'];
    $status = $_POST['status'];
    $roles = isset($_POST['roles']) ? $_POST['roles'] : [];

   
    $query = "UPDATE employee 
              SET firstName = ?, middleName = ?, lastName = ?, emailAddress = ?, phoneNumber = ?, status = ?
              WHERE userId = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('ssssssi', $firstName, $middleName, $lastName, $emailAddress, $phoneNumber, $status, $userId);

    
    if ($stmt->execute()) {
        if (!empty($roles)) {
            // Delete current roles and insert new ones
            $deleteRolesQuery = "DELETE FROM employee_role WHERE userId = ?";
            $stmtDelete = $con->prepare($deleteRolesQuery);
            $stmtDelete->bind_param('i', $userId);
            $stmtDelete->execute();

            // Insert new roles
            $insertRolesQuery = "INSERT INTO employee_role (userId, role_id) VALUES (?, ?)";
            $stmtInsert = $con->prepare($insertRolesQuery);
            foreach ($roles as $role) {
                $stmtInsert->bind_param('ii', $userId, $role);
                $stmtInsert->execute();
            }
        }

        // additional staff role
        $staffRole = $_POST['staffRole'] ?? null;
        if ($staffRole) {
            $staffRoleId = 4; 
            $insertStaffRoleQuery = "INSERT INTO employee_role (userId, role_id) VALUES (?, ?)";
            $stmtStaffInsert = $con->prepare($insertStaffRoleQuery);
            $stmtStaffInsert->bind_param('ii', $userId, $staffRoleId);
            $stmtStaffInsert->execute();
            $stmtStaffInsert->close();
        }

        $_SESSION['status'] = 'User Updated!';
        $_SESSION['status_code'] = 'success';
        header('Location: ../user.php');
        exit;
    } else {
       
        header('Location: ../users.php?error=Failed to update user');
    }

    $stmt->close();
}
?>
