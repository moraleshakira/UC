<?php
    include '../config/config.php';

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    error_log("Received POST request: " . print_r($_POST, true));

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!$con) {
            error_log("Database connection failed: " . mysqli_connect_error());
            echo json_encode([
                'status' => 'error',
                'message' => 'Database connection failed'
            ]);
            exit;
        }

        if (!isset($_POST['dean_name']) || empty($_POST['dean_name'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dean name is required'
            ]);
            exit;
        }

        $deanName = mysqli_real_escape_string($con, $_POST['dean_name']);
        
        $sql = "INSERT INTO dean (dean_name) VALUES ('$deanName')";
        error_log("Executing SQL: " . $sql);
        
        if (mysqli_query($con, $sql)) {
            $deanId = mysqli_insert_id($con);
            error_log("Successfully inserted dean with ID: " . $deanId);
            echo json_encode([
                'status' => 'success',
                'dean_id' => $deanId,
                'dean_name' => $deanName
            ]);
        } else {
            $error = mysqli_error($con);
            error_log("Database error: " . $error);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to add Dean: ' . $error
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    mysqli_close($con);
?>