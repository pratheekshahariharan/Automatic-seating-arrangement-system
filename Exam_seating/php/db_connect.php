<?php
// Turn on error reporting for debugging during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- IMPORTANT ---
// Update these database credentials with your own!
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'sandeep05');
define('DB_NAME', 'exam_seating_db');

// Attempt to connect to the MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Start a session on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
