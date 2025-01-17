<?php
include '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['dean_id']) || !isset($_POST['dean_name']) || 
        empty($_POST['dean_id']) || empty($_POST['dean_name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Dean ID and name are required'
        ]);
        exit;
    }

    $deanId = mysqli_real_escape_string($con, $_POST['dean_id']);
    $deanName = mysqli_real_escape_string($con, $_POST['dean_name']);
    
    $sql = "UPDATE dean SET dean_name = '$deanName' WHERE dean_id = '$deanId'";
    
    if (mysqli_query($con, $sql)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Dean updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update Dean: ' . mysqli_error($con)
        ]);
    }
}

mysqli_close($con);
?>
