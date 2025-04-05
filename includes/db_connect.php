<?php
define('DB_SERVER', 'localhost'); // Or your DB host
define('DB_USERNAME', 'root');    // Your DB username
define('DB_PASSWORD', '');        // Your DB password
define('DB_NAME', 'food_charity_db'); // Your DB name

// Attempt to connect to MySQL database using PDO
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Use a generic message for the public user
    error_log("Database connection failed: " . $e->getMessage()); // Log detailed error
    die("ERROR: Could not connect to the database. Please try again later.");
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>