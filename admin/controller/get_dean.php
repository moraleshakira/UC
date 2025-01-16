<?php
include '../config/config.php'; // Ensure this file connects to the database

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $deanId = mysqli_real_escape_string($con, $_GET['dean_id']);

    $sql = "SELECT dean_id, dean_name FROM dean WHERE dean_id = '$deanId'";

    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        echo json_encode($data);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Dean not found.']);
    }

    mysqli_close($con);
}
?>
