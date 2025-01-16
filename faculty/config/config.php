<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "ucheque";

$con = mysqli_connect("$host", "$username", "$password", "$database");

if(!$con)
{
    // header("Location: .../error/error.php"); //NEED TO CREATE DATA FILE
    die();
}

?>

