<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'biblioteca';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>
