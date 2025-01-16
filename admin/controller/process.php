<?php
// Include database connection
include '../config/config.php'; // Ensure $con is defined here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Add Record
    if ($action === 'add') {
        $name = $_POST['name'];
        $table = $_POST['table'];
        $column = $_POST['column'];
        $sql = "INSERT INTO $table ($column) VALUES ('$name')";
        if ($con->query($sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Record added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $con->error]);
        }
    }

    // Edit Record
    if ($action === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $table = $_POST['table'];
        $column = $_POST['column'];
        $id_column = $_POST['id_column'];
        $sql = "UPDATE $table SET $column='$name' WHERE $id_column=$id";
        if ($con->query($sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Record updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $con->error]);
        }
    }

    // Delete Record
    if ($action === 'delete') {
        $id = $_POST['id'];
        $table = $_POST['table'];
        $id_column = $_POST['id_column'];
        $sql = "DELETE FROM $table WHERE $id_column=$id";
        if ($con->query($sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $con->error]);
        }
    }
}
?>
