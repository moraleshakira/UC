<?php

include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hr_id = $_POST['hr_id'];
    $hr_name = $_POST['hr_name'];

    $sql = "UPDATE hr_personnel SET hr_name = ? WHERE hr_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('si', $hr_name, $hr_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update hr']);
    }
}

?>


