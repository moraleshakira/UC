<?php
include '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['hr_name']) || empty($_POST['hr_name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'hr name is required'
        ]);
        exit;
    }

    $hrName = mysqli_real_escape_string($con, $_POST['hr_name']);
    
    $sql = "INSERT INTO hr_personnel (hr_name) VALUES ('$hrName')";
    
    if (mysqli_query($con, $sql)) {
        $hrId = mysqli_insert_id($con);
        echo json_encode([
            'status' => 'success',
            'hr_id' => $hrId,
            'hr_name' => $hrName
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add hr: ' . mysqli_error($con)
        ]);
    }
}

mysqli_close($con);
?>