<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="96x96" href="./assets/images/logo-dark.png">
    <title>Forgot Password</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    
    <!-- CSS -->
    <link rel="stylesheet" href="./assets/login.css">

</head>
<body>
    <div class="login-container">
        <img src="./assets/images/logoall-grey.png" alt="Logo">
        <header>Forgot Password</header>
        <p>Please enter your active e-mail address.</p><br>
        <form action="./controller/forgot_password.php" method="POST">
            <div class="input-field">
                <input type="email" id="email" name="email" required autocomplete="off">
                <label for="email">Enter your email</label>
            </div>
            <button type="submit" class="submit-button">Send Reset Link</button>
        </form>
        <div class="signin">
            <span>Back to <a href="index.php">Login</a></span>
        </div>
    </div>
</body>
</html>
