<?php
    include '../config/config.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['chairman_id']) || empty($_POST['chairman_id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Chairman ID is required'
            ]);
            exit;
        }

        $chairmanId = mysqli_real_escape_string($con, $_POST['chairman_id']);
        
        $sql = "DELETE FROM chairmans WHERE chairman_id = '$chairmanId'";
        
        if (mysqli_query($con, $sql)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Chairman deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to delete Chairman: ' . mysqli_error($con)
            ]);
        }
    }

    mysqli_close($con);
?>