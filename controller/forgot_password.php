<?php
require '../config/config.php'; // Database connection
require '../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'emailAddress', FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Generate a token for the password reset
        $token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Insert the reset token into the password_resets table
        $query = $db->prepare("SELECT * FROM users WHERE emailAddress = :email");
        $query->bindParam(':email', $email);
        $query->execute();

        if ($query->rowCount() > 0) {
            $insert = $db->prepare("INSERT INTO password_resets (emailAddress, token, expires_at) VALUES (:email, :token, :expires)");
            $insert->bindParam(':email', $email);
            $insert->bindParam(':token', $token);
            $insert->bindParam(':expires', $expires);
            $insert->execute();
        }

        // Create the reset link
        $resetLink = "http://ucheque.ct.ws/reset.php?token=" . $token;

        // Send the email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.yourmailserver.com'; // Your SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@example.com'; // Your email address
            $mail->Password   = 'your-email-password'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@yourdomain.com', 'Your Website');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click the following link to reset your password: <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            echo "If the email address is registered, a password reset link has been sent to your email address.";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Invalid email address.";
    }
}
?>
