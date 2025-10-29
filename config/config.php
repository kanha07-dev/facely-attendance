<?php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'db_password');
define('DB_NAME', 'db_name');

// No hardcoded streams; managed dynamically by admins

// Establish a database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>