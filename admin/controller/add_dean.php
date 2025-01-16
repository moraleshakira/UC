<?php
include '../config/config.php';   // Ensure this file connects to the database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deanName = mysqli_real_escape_string($con, $_POST['dean_name']);

    $sql = "INSERT INTO dean (dean_name) VALUES ('$deanName')";

    if (mysqli_query($con, $sql)) {
        $deanId = mysqli_insert_id($con);  // Get the ID of the newly inserted dean
        echo json_encode([
            'status' => 'success',
            'dean_id' => $deanId,
            'dean_name' => $deanName
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add Dean.']);
    }

    mysqli_close($con);
}
?>
