<?php
session_start();

unset($_SESSION['auth']);
unset($_SESSION['userstatus']);
unset($_SESSION['roles']);
unset($_SESSION['auth_user']);


session_destroy();

header("Location: ../../index.php");
exit(0);
?>
