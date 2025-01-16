<?php
 include("../config/config.php");
 session_start();

 if (isset($_POST['addUser']))
 {
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $phoneNumber = $_POST['phoneNumber'];
    $emailAddress = $_POST['emailAddress'];
    $birthday = $_POST['birthday'];
    $userName = $_POST['userName'];
    $role = $_POST['role'];

     // Function to generate a random password
     function generateRandomPassword($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        $charactersLength = strlen($characters);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $password;
    }
    
    // Generate a random password
    $password = generateRandomPassword();

    // Use prepared statements to prevent SQL injection
    $query = "INSERT INTO `users` (`firstName`, `middleName`, `lastName`, `username`, `password`, `emailAddress`, `role`) VALUES ('$firstName', '$middleName', '$lastName', '$userName', '$password', '$emailAddress', '$role')";
    
    $query_run = mysqli_query($con, $query);

    if ($query_run) {

        // $mail = new PHPMailer(true);

        // $mail->isSMTP();
        // $mail->Host = 'smtp.gmail.com';
        // $mail->SMTPAuth = true;
        // $mail->Username = 'tms.onlinesystem@gmail.com';
        // $mail->Password = 'nvihuxxrogwzxrai';
        // $mail->SMTPSecure = 'ssl';
        // $mail->Port = 465;

        // $mail->setFrom('tms.onlinesystem@gmail.com');

        // $mail->addAddress($email);

        // $mail->isHTML(true);

        // $mail->Subject = "$firstname $lastname";
        // $mail->Body = "You account subscription has been rejected.";
        // $mail->send();


        $_SESSION['status'] = "User has been added successfully!";
        $_SESSION['status_code'] = "success";
        header('Location: ../users.php');
        exit(0);
    } else {
        echo "Error: " . mysqli_error($con);
    }
    mysqli_close($con);
}



?>