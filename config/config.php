<?php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'bookandb_guest');
define('DB_PASS', 'Iamowner');
define('DB_NAME', 'bookandb_web');

// Establish a database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>