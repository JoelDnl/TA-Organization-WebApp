<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally set the charset to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// ... any other configuration or error handling you might want to add ...
?>
