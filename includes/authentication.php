<?php
session_start();

/**
 * Handles authentication-related alerts and adds a timeout for display.
 */
if (isset($_SESSION['status']) && isset($_SESSION['status_code'])) {
    // Display the message based on session status
    $statusMessage = $_SESSION['status'];
    $statusCode = $_SESSION['status_code'];

    // Choose the alert style based on the status code
    $alertClass = "info"; // Default
    if ($statusCode === "error") {
        $alertClass = "danger";
    } elseif ($statusCode === "warning") {
        $alertClass = "warning";
    } elseif ($statusCode === "success") {
        $alertClass = "success";
    }

    // Display the alert
    echo "<div id='auth-alert' class='alert alert-{$alertClass} text-center' role='alert'>
            {$statusMessage}
          </div>";

    // Clear the session variables after use
    unset($_SESSION['status']);
    unset($_SESSION['status_code']);
}
?>
<!-- JavaScript to auto-hide the alert after 5 seconds -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const alert = document.getElementById('auth-alert');
        if (alert) {
            setTimeout(() => {
                alert.style.transition = "opacity 0.5s";
                alert.style.opacity = 0;
                setTimeout(() => alert.remove(), 300); 
            }, 3000); // 2 seconds
        }
    });
</script>
