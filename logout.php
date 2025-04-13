<?php
session_start(); // Access the session

// Optional: Log the logout action
// error_log("User logged out: ID " . ($_SESSION['user_id'] ?? 'Unknown'));

session_unset(); // Remove all session variables
session_destroy(); // Destroy the session data on the server
session_write_close(); // Ensure session changes are saved before redirect

// Clear session cookie (optional but good practice)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to homepage
header("Location: index.php?status=loggedout"); // Add status for potential message on index
exit; // Ensure no further code execution
?>