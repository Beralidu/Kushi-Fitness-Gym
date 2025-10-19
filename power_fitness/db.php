<?php
$host = "localhost"; // MySQL host
$user = "root";      // MySQL username
$password = "";      // MySQL password
$dbname = "power_fitness";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
