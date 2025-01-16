<?php

include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chairman_id = $_POST['chairman_id'];
    $chairman_name = $_POST['chairman_name'];

    $sql = "UPDATE chairmanas SET chairman_name = ? WHERE chairman_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('si', $chairman_name, $chairman_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update chairman']);
    }
}

?>


