<?php
session_start();

$user_roles = isset($_SESSION['roles']) ? $_SESSION['roles'] : [];

if (empty($user_roles)) {
    header("Location: ../index.php");
    exit();
}

if (count($user_roles) === 1) {
    $single_role = $user_roles[0];
    switch ($single_role) {
        case 'Admin':
            $_SESSION['status'] = "Welcome " . $_SESSION['auth_user']['firstName'] . ' ' . $_SESSION['auth_user']['lastName'];
            $_SESSION['status_code'] = "success";
            header("Location: ./admin/index.php");
            break;
        case 'Hr Personnel':
            header("Location: ./hr/h_dash.php");
            break;
        case 'HR Staff':
            header("Location: ./staff/s_dash.php");
            break;
        case 'Faculty':
            header("Location: ./faculty/f_dash.php");
            break;
        default:
            $_SESSION['status'] = "Role not recognized.";
            $_SESSION['status_code'] = "error";
            header("Location: ../index.php");
    }
    exit();
}
?>
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
    <link rel="stylesheet" href="./assets/logas.css">

</head>
<body>
    <div class="wrapper">
        <img src="./assets/images/logoall-grey.png" alt="Logo">
        <header>Log As</header>
        <form action="./controller/role_redirect.php" method="POST" autocomplete="off">
            <div class="input-field">
                <?php if (in_array('Admin', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="Admin">Admin</button>
                <?php endif; ?>

                <?php if (in_array('HR Staff', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="HR Staff">Staff</button>
                <?php endif; ?>

                <?php if (in_array('Faculty', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="Faculty">Faculty</button>
                <?php endif; ?>

                <?php if (in_array('Hr Personnel', $user_roles)): ?>
                    <button name="selected_role" type="submit" class="submit" value="Hr Personnel">Hr</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</body>
</html>
