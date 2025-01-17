<?php
    include '../config/config.php';

    header('Content-Type: application/json');

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['dean_id']) || empty($_POST['dean_id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Dean ID is required'
            ]);
            exit;
        }

        if (!is_numeric($_POST['dean_id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid Dean ID'
            ]);
            exit;
        }

        $dean_id = intval($_POST['dean_id']);

        try {
            $con->begin_transaction();

            $check_sql = "SELECT dean_id FROM dean WHERE dean_id = ?";
            $check_stmt = $con->prepare($check_sql);
            $check_stmt->bind_param('i', $dean_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception('Dean not found');
            }

            $delete_sql = "DELETE FROM dean WHERE dean_id = ?";
            $delete_stmt = $con->prepare($delete_sql);
            $delete_stmt->bind_param('i', $dean_id);

            if (!$delete_stmt->execute()) {
                throw new Exception('Failed to delete dean: ' . $delete_stmt->error);
            }

            $con->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Dean deleted successfully'
            ]);

        } catch (Exception $e) {
            $con->rollback();
            
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        if (isset($check_stmt)) $check_stmt->close();
        if (isset($delete_stmt)) $delete_stmt->close();
        
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    $con->close();
?>