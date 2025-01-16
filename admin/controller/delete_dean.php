<?php

include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dean_id = $_POST['dean_id'];

    $sql = "DELETE FROM dean WHERE dean_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $dean_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete dean']);
    }
}

?>


