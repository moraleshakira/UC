<?php
include '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['chairman_name']) || empty($_POST['chairman_name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Chairman name is required'
        ]);
        exit;
    }

    $chairmanName = mysqli_real_escape_string($con, $_POST['chairman_name']);
    
    $sql = "INSERT INTO chairmans (chairman_name) VALUES ('$chairmanName')";
    
    if (mysqli_query($con, $sql)) {
        $chairmanId = mysqli_insert_id($con);
        echo json_encode([
            'status' => 'success',
            'chairman_id' => $chairmanId,
            'chairman_name' => $chairmanName
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add Chairman: ' . mysqli_error($con)
        ]);
    }
}

mysqli_close($con);
?>