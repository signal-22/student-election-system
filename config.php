<?php
// Database configuration
$host = "localhost";      // Usually localhost
$user = "root";           // Default XAMPP MySQL user
$password = "";           // Default XAMPP MySQL password is empty
$database = "student_election"; // Replace with your database name

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
