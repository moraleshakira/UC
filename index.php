<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="96x96" href="./assets/images/logo-dark.png">
    <title>Ucheque LogIn</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    
    <!-- CSS -->
    <link rel="stylesheet" href="./assets/login.css">

</head>
<body>
    <div class="login-container">
        <img src="./assets/images/logoall-grey.png" alt="Logo">
        <header>Log In</header>

        <?php include './includes/authentication.php'; ?>

        <form action="./controller/login_process.php" method="POST">
            <div class="input-field">
                <input type="text" id="email" name="emailAddress" required autocomplete="off">
                <label for="email">Email</label>
            </div>
            <div class="input-field">
                <input type="password" id="password" name="password" required>
                <label for="password">Password</label>
            </div>
            <button type="submit" class="submit-button" name="login">Login</button>
        </form>
        <div class="signin">
            <span>Forgot Password? <a href="forgot.php">Click here to reset</a></span>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</html>


