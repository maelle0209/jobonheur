<?php
$host = 'localhost';
$dbname = 'jobonheur';
$username = 'root'; // Par défaut dans MAMP
$password = 'root'; // Par défaut dans MAMP

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
