<?php
session_start();
require '../../vendor/autoload.php';
require '../../config/config.php';

if (!isset($_SESSION['auth_user'])) {
    $_SESSION['status'] = "Please log in to submit requests";
    $_SESSION['status_code'] = "error";
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['auth_user']['userId'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['requesType']) || empty($_POST['requesType']) ||
        !isset($_POST['startMonth']) || empty($_POST['startMonth']) ||
        !isset($_POST['startYear']) || empty($_POST['startYear'])) {
        $_SESSION['status'] = "Please fill in all required fields";
        $_SESSION['status_code'] = "error";
        header("Location: ../f_request.php");
        exit;
    }

    try {
        $requestType = $con->real_escape_string($_POST['requesType']);
        $startMonth = $con->real_escape_string($_POST['startMonth']);
        $startYear = (int)$_POST['startYear'];
        
        $endMonth = !empty($_POST['endMonth']) ? $con->real_escape_string($_POST['endMonth']) : null;
        $endYear = !empty($_POST['endYear']) ? (int)$_POST['endYear'] : null;

        $monthOrder = [
            "January" => 1, "February" => 2, "March" => 3, "April" => 4,
            "May" => 5, "June" => 6, "July" => 7, "August" => 8,
            "September" => 9, "October" => 10, "November" => 11, "December" => 12
        ];

        if ($endMonth && $endYear) {
            if ($endYear < $startYear || 
                ($endYear === $startYear && $monthOrder[$endMonth] < $monthOrder[$startMonth])) {
                throw new Exception("End date cannot be before start date");
            }
        }

        $checkQuery = "SELECT * FROM request 
                      WHERE userId = ? 
                      AND requestType = ? 
                      AND startMonth = ? 
                      AND startYear = ?
                      AND status != 'Rejected'";
        
        $stmt = $con->prepare($checkQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }

        $stmt->bind_param("issi", $userId, $requestType, $startMonth, $startYear);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("A request already exists");
        }

        $insertQuery = "INSERT INTO request (userId, requestType, startMonth, startYear, endMonth, endYear, status) 
                       VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $con->prepare($insertQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }

        $status = 'Pending';
        $stmt->bind_param("ississ", 
            $userId, 
            $requestType, 
            $startMonth, 
            $startYear, 
            $endMonth, 
            $endYear
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to submit request: " . $stmt->error);
        }

        $_SESSION['status'] = "Request submitted successfully!";
        $_SESSION['status_code'] = "success";

    } catch (Exception $e) {
        $_SESSION['status'] = $e->getMessage();
        $_SESSION['status_code'] = "error";
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        header("Location: ../f_request.php");
        exit;
    }
}
?>