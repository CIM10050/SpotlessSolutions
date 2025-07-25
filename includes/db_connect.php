<?php
// includes/db_connect.php

$host = 'localhost';
$dbname = 'spotless_laundry';
$user = 'root';
$pass = ''; // Default for XAMPP

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
