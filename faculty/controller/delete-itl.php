<?php
include("../config/config.php");
session_start();

if (isset($_GET['itl_extracted_data_id'])) {
    $itlExtractedDataId = (int)$_GET['itl_extracted_data_id'];

    if (!isset($_SESSION['auth_user']) || !isset($_SESSION['auth_user']['userId'])) {
        die("Unauthorized access.");
    }

    $loggedInUserId = $_SESSION['auth_user']['userId'];

    $sqlDeleteData = "DELETE FROM itl_extracted_data WHERE id = ? AND userId = ?";
    $stmt = $con->prepare($sqlDeleteData);
    $stmt->bind_param('ii', $itlExtractedDataId, $loggedInUserId);
    $stmt->execute();
    
    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    if ($affectedRows > 0) {
        header("Location: ../f_itl.php?msg=success");
        exit();
    } else {
        header("Location: ../f_itl.php?msg=error");
        exit();
    }
}
?>