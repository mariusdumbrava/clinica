<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "clinica";

$conn = mysqli_connect($host, $user, $pass, $db_name);

if (!$conn) {
    die("Conexiune eșuată: " . mysqli_connect_error());
}

session_start();
?>