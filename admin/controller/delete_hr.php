<?php
include '../config/config.php';

header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['hr_id']) || empty($_POST['hr_id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'HR Personnel ID is required'
            ]);
            exit;
        }

        $hrId = mysqli_real_escape_string($con, $_POST['hr_id']);
        
        $sql = "DELETE FROM hr_personnel WHERE hr_id = '$hrId'";
        
        if (mysqli_query($con, $sql)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'HR Personnel deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete HR Personnel: ' . mysqli_error($con)
            ]);
        }
    }

    mysqli_close($con);
?>