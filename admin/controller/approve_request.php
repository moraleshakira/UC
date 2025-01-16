<?php
session_start();
require '../../vendor/autoload.php';
require '../../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
    $dateApproved = date("Y-m-d H:i:s"); // Get the current date and time

    // Update the status and set the approval date
    $query = "UPDATE request SET status = 'Approved', dateApproved = ? WHERE requestId = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("si", $dateApproved, $requestId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Include the approval date in the session message (if needed)
        $_SESSION['message'] = "Request has been approved successfully!";
        $_SESSION['message_type'] = "success";

        // Return a JSON response for AJAX
        echo json_encode(['success' => true, 'dateApproved' => $dateApproved]);
    } else {
        $_SESSION['message'] = "Error occurred while updating the request.";
        $_SESSION['message_type'] = "danger";

        // Return an error response
        echo json_encode(['success' => false, 'error' => $con->error]);
    }

    $stmt->close();
    $con->close();
}
?>
