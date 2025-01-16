<?php
session_start();
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $emailAddress = $_POST['emailAddress'];
    $password = $_POST['password'];

    // Use prepared statements for security
    $stmt = $con->prepare("
        SELECT
            employee.*,
            role.name AS roleName
        FROM
            employee
        INNER JOIN
            employee_role
            ON employee.userId = employee_role.userId
        INNER JOIN
            role
            ON employee_role.role_id = role.roleId
        WHERE
            employee.emailAddress = ?
    ");
    $stmt->bind_param("s", $emailAddress);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $roles = [];
        $userData = null;

        while ($row = $result->fetch_assoc()) {
            if (!$userData) {
                $userData = $row;
            }
            $roles[] = $row['roleName'];
        }

        if (password_verify($password, $userData['password'])) {
            $_SESSION['auth'] = true;
            $_SESSION['roles'] = $roles;
            $_SESSION['auth_user'] = [
                'userId' => $userData['userId'],
                'fullName' => $userData['firstName'] . ' ' . $userData['lastName'],
                'email' => $userData['emailAddress']
            ];

            if ($userData['status'] === 'Archived') {
                $_SESSION['status'] = "Your account is archived!";
                $_SESSION['status_code'] = "warning";
                header("Location: ../index.php");
                exit();
            
            } elseif ($userData['status'] === 'Active') {
                $_SESSION['status'] = "Welcome " . $userData['firstName'] . ' ' . $userData['lastName'] . "!";
                $_SESSION['status_code'] = "success";
            
                // NO MORE LOG IN AS 
                if (in_array('HR Staff', $roles)) {
                    header("Location: ../staff/s_dash.php"); 
                    exit();
                } elseif (in_array('Hr Personnel', $roles)) {
                    header("Location: ../hr/h_dash.php"); 
                    exit();
                } elseif (in_array('Faculty', $roles)) {
                    header("Location: ../faculty/f_dash.php"); 
                    exit();
                } elseif (in_array('Admin', $roles)) {
                    header("Location: ../admin/index.php"); 
                    exit();
                } else {
                    header("Location: ../index.php"); 
                    exit();
                }
            }            
            
            
        } else {
            $_SESSION['status'] = "Invalid Password";
            $_SESSION['status_code'] = "error";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['status'] = "Invalid Email Address";
        $_SESSION['status_code'] = "error";
        header("Location: ../index.php");
        exit();
    }

    $stmt->close();
} else {
    $_SESSION['status'] = "Invalid request method.";
    $_SESSION['status_code'] = "error";
    header("Location: ../index.php");
    exit();
}

$con->close();
?>
