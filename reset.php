<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="96x96" href="./assets/images/logo-dark.png">
    <title>Reset Password</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    
    <!-- CSS -->
    <link rel="stylesheet" href="./assets/login.css">

</head>
<body>
    <div class="login-container">
        <img src="./assets/images/logoall-grey.png" alt="Logo">
        <header>Reset Password</header>
        <form action="reset_password.php" method="POST">
            <!-- <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>"> -->
            <div class="input-field">
                <input type="password" id="password" name="password" required>
                <label for="password">New Password</label>
            </div>
            <button type="submit" class="submit-button">Reset Password</button>
        </form>
        <div class="signin">
            <span>Back to <a href="index.php">Login</a></span>
        </div>
    </div>
</body>
</html>
