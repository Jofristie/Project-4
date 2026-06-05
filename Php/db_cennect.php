<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "projectwebsite";

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$conn) {
    die("Verbinding mislukt: " . mysqli_connect_error());
}

echo "Verbonden!";
?>
