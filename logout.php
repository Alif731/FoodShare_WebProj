<link rel="stylesheet" href="css/index.css">

<?php
session_start(); // Access the session
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to homepage or login page
header("Location: index.php");
exit;
?>